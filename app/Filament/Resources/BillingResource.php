<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingResource\Pages;
use App\Filament\Resources\BillingResource\RelationManagers;
use App\Models\Billing;
use App\Models\TestList;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillingResource extends Resource
{
    protected static ?string $model = Billing::class;
    protected static ?string $navigationGroup = 'Reception';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->columnSpanFull()
                    ->createOptionForm([
                        Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('phone')->tel(),
                            Forms\Components\TextInput::make('email')->email()->unique(),
                            Forms\Components\DatePicker::make('birthdate')
                                ->date(),
                            Forms\Components\ToggleButtons::make('gender')
                                ->inline()
                                ->grouped()
                                ->options([
                                    'Male' => 'Male',
                                    'Female' => 'Female',
                                ]),
                            Forms\Components\Select::make('company_id')
                                ->relationship('company', 'name')
                                ->disabled()
                                ->dehydrated(true)
                                ->default(fn()=>Filament::getTenant()->id)
                                ->required(),
                            Forms\Components\Textarea::make('address')
                                ->columnSpanFull(),
                        ])->columns(2)
                    ])
                    ->createOptionModalHeading('Create New Patient')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'Laboratory' => 'Laboratory',
                        'Scanning' => 'Scanning',
                    ])->default('Laboratory'),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->disabled()
                    ->dehydrated(true)
                    ->default(fn()=>Filament::getTenant()->id)
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->relationship('lineItems')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Select::make('test_list_id')
                            ->required()
                            ->reactive()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->relationship('testLists', 'name')
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get){
                                $selected = $get('test_list_id');
                                if($selected){
                                    $testList = TestList::find($selected);
                                    if($testList){
                                        $set('amount', $testList->price);
                                    }else{
                                        $set('amount', null);
                                    }
                                }else{
                                    $set('amount', null);
                                }

                                $items = $get('../../items');
                                $total = collect($items)->sum('amount');
                                $set('../../total', $total);
                            }),
                        Forms\Components\TextInput::make('amount')
                            ->reactive() // Make amount reactive so changes trigger updates
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                // Get all items in the repeater to recalculate the total
                                $items = $get('../../items'); // Note the '../../' to go up to the form level
                                $total = collect($items)->sum('amount');
                                $set('../../total', $total); // Set the total field
                            })
                            ->required(),
                    ])->columns(2),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->dehydrated()
                    ->default(fn()=> auth()->user()->id)
                    ->required(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->default(0)
                    ->readonly()
                    ->live()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Request By')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->summarize(Sum::make())
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Payment')
                        ->icon('heroicon-m-credit-card')
                        ->url(fn($record) => 'billings/'.$record->id.'/payments'),
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->modalWidth(MaxWidth::FitContent),
                    Tables\Actions\EditAction::make()
                        ->slideOver()
                        ->modalWidth(MaxWidth::FitContent),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBillings::route('/'),
            'payment' => Pages\ManagePayment::route('/{record}/payments'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

}
