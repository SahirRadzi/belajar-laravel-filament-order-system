<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BasePage;

class Login extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'sahir.radzi@gmail.com',
            'password' => '12345678',
            'remember' => true,
        ]);
    }
}
