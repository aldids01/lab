<?php

namespace App\Filament\Resources\BillingResource\Pages;

use App\Filament\Resources\BillingResource;
use App\Models\Billing;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class ManagePayment extends ManageRelatedRecords
{
    protected static string $resource = BillingResource::class;
    public Billing $billing;

    protected static string $relationship = 'payments';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public function mount($record = null): void
    {
        parent::mount($record);

        if ($record && is_numeric($record)) {
            $this->billing = Billing::findOrFail($record);
        }
    }
    public function getTitle(): string|Htmlable
    {
        return 'Payments for '.$this->billing->patient->name;
    }

    public static function getNavigationLabel(): string
    {
        return 'Payments';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('status')
                    ->required()
                    ->inline()
                    ->columnSpanFull()
                    ->grouped()
                    ->options([
                        'Processing' => 'Processing',
                        'Paid' => 'Paid',
                        'Cancel' => 'Cancel',
                        'Failed' => 'Failed',
                        'Refund' => 'Refund',
                    ])->default('Paid'),
                Forms\Components\ToggleButtons::make('method')
                    ->required()
                    ->inline()
                    ->columnSpanFull()
                    ->grouped()
                    ->options([
                        'Cash' => 'Cash',
                        'Online' => 'Online',
                        'POS' => 'POS',
                        'Bank' => 'Bank',
                        'Cheque' => 'Cheque',
                    ])->default('Cash'),
                Forms\Components\TextInput::make('amount')
                    ->prefix('NGN')
                    ->default(fn()=> $this->billing->total)
                    ->columnSpanFull()
                    ->numeric(),
                Forms\Components\Select::make('patient_id')
                    ->label('Paid By')
                    ->relationship('patient', 'name')
                    ->disabled()
                    ->columnSpanFull()
                    ->dehydrated()
                    ->default(fn()=> $this->billing->patient->id)
                    ->required(),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->disabled()
                    ->columnSpanFull()
                    ->dehydrated(true)
                    ->default(fn()=>Filament::getTenant()->id)
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Received By')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->columnSpanFull()
                    ->dehydrated()
                    ->default(fn()=> auth()->user()->id)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (): string => "{$this->billing->patient->name} Payment")
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Received By'),
                Tables\Columns\TextColumn::make('method')
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->summarize(Sum::make()),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::FitContent)
                    ->modalHeading(fn() => 'Received Payment for ' .$this->billing->type)
                    ->modalDescription(fn() => "Amount due: NGN".Number::format($this->billing->total, 2))
                    ->hidden(fn()=>$this->billing->status === 'Paid'),
            ])
            ->actions([
               Tables\Actions\ActionGroup::make([
                   Tables\Actions\ViewAction::make()
                       ->slideOver()
                       ->modalWidth(MaxWidth::FitContent),
                   Tables\Actions\EditAction::make()
                       ->slideOver()
                       ->modalWidth(MaxWidth::FitContent),
                   Tables\Actions\DeleteAction::make(),
                   Tables\Actions\ForceDeleteAction::make(),
                   Tables\Actions\RestoreAction::make(),
               ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
