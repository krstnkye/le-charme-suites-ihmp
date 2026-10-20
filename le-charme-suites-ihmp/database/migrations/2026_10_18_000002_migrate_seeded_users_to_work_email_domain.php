<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL_CHANGES = [
        'admin@example.com' => 'admin@lecharmehotel.com',
        'receptionist@lecharme.test' => 'receptionist@lecharmehotel.com',
        'cashier@lecharme.test' => 'billing@lecharmehotel.com',
        'housekeeping@lecharme.test' => 'housekeeping@lecharmehotel.com',
        'owner@lecharme.test' => 'owner@lecharmehotel.com',
    ];

    public function up(): void
    {
        $this->moveEmails(self::EMAIL_CHANGES);

        DB::table('users')
            ->where('email', 'not like', '%@lecharmehotel.com')
            ->orderBy('id')
            ->get(['id', 'name', 'email'])
            ->each(function (object $user): void {
                $base = Str::lower(Str::before($user->email, '@'));
                $base = preg_replace('/[^a-z0-9._-]+/', '.', $base) ?: Str::slug($user->name, '.');
                $base = trim($base, '.-_') ?: 'employee'.$user->id;
                $email = $base.'@lecharmehotel.com';
                $suffix = 2;

                while (DB::table('users')->where('email', $email)->where('id', '!=', $user->id)->exists()) {
                    $email = $base.$suffix.'@lecharmehotel.com';
                    $suffix++;
                }

                DB::table('users')->where('id', $user->id)->update(['email' => $email]);
            });
    }

    public function down(): void
    {
        $this->moveEmails(array_flip(self::EMAIL_CHANGES));
    }

    private function moveEmails(array $changes): void
    {
        foreach ($changes as $from => $to) {
            if (DB::table('users')->where('email', $to)->exists()) {
                continue;
            }

            DB::table('users')->where('email', $from)->update(['email' => $to]);
        }
    }
};
