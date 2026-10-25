<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $comptes = [
            [
                'name' => 'Yas',
                'email' => 'yas@gmail.com',
                'role' => 'operateur',
                'operateur' => 'Yas',
            ],
            [
                'name' => 'Airtel',
                'email' => 'airtel@gmail.com',
                'role' => 'operateur',
                'operateur' => 'Airtel',
            ],
            [
                'name' => 'Orange',
                'email' => 'orange@gmail.com',
                'role' => 'operateur',
                'operateur' => 'Orange',
            ],
            [
                'name' => 'ARTEC',
                'email' => 'artec@gmail.com',
                'role' => 'artec',
                'operateur' => null,
            ],
        ];

        foreach ($comptes as $compte) {
            DB::table('users')->updateOrInsert(
                ['email' => $compte['email']],
                [
                    'name' => $compte['name'],
                    'role' => $compte['role'],
                    'password' => Hash::make('123456789'),
                    'email_verified_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            if ($compte['operateur'] !== null) {
                $user = DB::table('users')
                    ->where('email', $compte['email'])
                    ->first();

                DB::table('operateurs')->updateOrInsert(
                    ['nom' => $compte['operateur']],
                    [
                        'user_id' => $user->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $emails = [
            'yas@gmail.com',
            'airtel@gmail.com',
            'orange@gmail.com',
            'artec@gmail.com',
        ];

        $users = DB::table('users')
            ->whereIn('email', $emails)
            ->get(['id', 'email']);

        foreach ($users as $user) {
            if (in_array($user->email, [
                'yas@gmail.com',
                'airtel@gmail.com',
                'orange@gmail.com',
            ], true)) {
                DB::table('operateurs')
                    ->where('user_id', $user->id)
                    ->delete();
            }
        }

        DB::table('users')
            ->whereIn('email', $emails)
            ->delete();
    }
};