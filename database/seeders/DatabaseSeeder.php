<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ChargeTypeSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        // Outside production, every seeded account keeps the fixed 'password'
        // login — convenient for local dev and CI. In production a fixed,
        // publicly-known password on a real login is a live backdoor, so each
        // account there gets its own random password, printed once so
        // whoever ran the seeder can hand it off and it's never stored in
        // the repo or in this process beyond this run.
        $accounts = [
            ['email' => 'gm@cargolink.lk', 'name' => 'General Manager', 'role' => 'gm'],
            ['email' => 'co-gm@cargolink.lk', 'name' => 'Co-General Manager', 'role' => 'co-gm'],
            ['email' => 'staff@cargolink.lk', 'name' => 'Staff User', 'role' => 'staff'],
        ];

        foreach ($accounts as $account) {
            $password = app()->environment('production') ? Str::password(20) : 'password';

            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => bcrypt($password)]
            );
            // syncRoles (not assignRole) so re-running this seeder against a
            // DB seeded before the 'co-gm' role existed replaces the old
            // 'gm' role instead of leaving the account with both.
            $user->syncRoles([$account['role']]);

            if ($user->wasRecentlyCreated && app()->environment('production')) {
                $this->command?->warn("Seeded {$account['email']} with password: {$password}");
            }
        }
    }
}
