<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class RegisterCompany extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Company';
    }
    protected ?string $maxWidth = '3xl';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Company Information')
                        ->schema([
                            Forms\Components\FileUpload::make('company_logo')
                                ->label('')
                                ->alignCenter()
                                ->avatar()
                                ->directory('company'),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company_slogan')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company_email')
                                ->email()
                                ->maxLength(255),
                        ]),
                    Forms\Components\Wizard\Step::make('Company Details')
                        ->schema([
                            Forms\Components\TextInput::make('company_phone')
                                ->tel()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company_address')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company_website')
                                ->maxLength(255),

                            Forms\Components\Toggle::make('is_active')
                                ->required(),
                        ])
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        size="sm"
                        wire:submit="register"
                    >
                        Register
                    </x-filament::button>
                    BLADE))),


            ]);
    }
    protected function getFormActions(): array
    {
        return [];
    }
    protected function handleRegistration(array $data): Company
    {
        $team = Company::create($data);

        $team->members()->attach(auth()->user());

        return $team;
    }
}
