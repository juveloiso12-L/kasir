<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:grant-filament-admin {email : The email address of an existing user}')]
#[Description('Grant administrator access to an existing Filament user')]
class GrantFilamentAdmin extends Command
{
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->is_admin = true;
        $user->save();

        $this->info('Filament administrator access granted.');

        return self::SUCCESS;
    }
}
