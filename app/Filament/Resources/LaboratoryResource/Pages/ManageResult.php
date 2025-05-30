<?php

namespace App\Filament\Resources\LaboratoryResource\Pages;

use App\Filament\Resources\LaboratoryResource;
use App\Models\Laboratory;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageResult extends ManageRelatedRecords
{
    protected static string $resource = LaboratoryResource::class;

    protected static string $relationship = 'lineItems';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public Laboratory $laboratory;
    public function mount($record = null): void
    {
        parent::mount($record);

        if ($record && is_numeric($record)) {
            $this->laboratory = Laboratory::findOrFail($record);
        }
    }
    public function getTitle(): string|Htmlable
    {
        return 'Laboratory results for '.$this->laboratory->patient->name;
    }
    public static function getNavigationLabel(): string
    {
        return 'Line Items';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\KeyValue::make('results')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record): string => "Result for {$record->testList->name}")
            ->columns([
                Tables\Columns\TextColumn::make('testList.name'),
                Tables\Columns\TextColumn::make('testList.range')
                    ->label('Range'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::FitContent),
                Tables\Actions\EditAction::make()
                    ->label('Enter result')
                    ->slideOver()
                    ->modalWidth(MaxWidth::FitContent),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
