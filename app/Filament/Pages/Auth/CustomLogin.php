<?php

namespace App\Filament\Pages\Auth\CustomLogin;


use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseAuth
{
    protected function getForms(): array
    {
        return[
            'form'=>$this->form(
                $this->makeForm()
                ->schema([
                    $this->getLoginFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getRememberFormComponent(),
                ])
                ->statePath('data'),
            )
        ];
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('mobile_number')
        ->required()
        ->maxLength(255);

        // $this->getEmailFormComponent(),
        // $this->getPasswordFormComponent(),
        // $this->getPasswordConfirmationFormComponent(),


    }
    public function getCredentialsFromFormData(array $data): array
    {
        // $login_type  = filter_var($data['login'] , )
        return [
            'mobile_number'=>$data['mobile_number'],
            'password'=>$data['password'],

        ];
    }
    public function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.mobile_number'=>__('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}