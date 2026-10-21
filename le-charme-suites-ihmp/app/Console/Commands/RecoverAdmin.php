<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('ihmp:recover-admin {email? : Work email of the administrator to create or reset} {--name= : Full name for a new administrator}')]
#[Description('Create an administrator, or reset an administrator password, when nobody can sign in to the admin dashboard')]
class RecoverAdmin extends Command
{
    /**
     * Recovery runs on the server itself, so only someone with access to the machine can use it.
     * The password is typed at a hidden prompt and is never stored in code or shown on screen.
     */
    public function handle(): int
    {
        $email = Str::lower(trim((string) ($this->argument('email') ?: $this->ask('Administrator work email'))));
        $user = User::where('email', $email)->first();
        $name = $user?->name ?? ($this->option('name') ?: $this->ask('Full name', 'Hotel Administrator'));
        $password = (string) $this->secret('New password (at least 8 characters)');
        $confirmation = (string) $this->secret('Confirm the new password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'email' => ['required', 'email', new WorkEmail],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user ??= new User(['email' => $email]);
        $wasAdmin = $user->exists && $user->role === 'admin';

        $user->forceFill([
            'name' => $name,
            'role' => 'admin',
            'approved_at' => $user->approved_at ?? now(),
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();

        $this->info(match (true) {
            ! $user->wasRecentlyCreated && $wasAdmin => "Password reset for administrator {$email}.",
            ! $user->wasRecentlyCreated => "{$email} is now an administrator with the new password.",
            default => "Administrator {$email} created.",
        });
        $this->line('Sign in at /login with the Admin department selected.');

        return self::SUCCESS;
    }
}
