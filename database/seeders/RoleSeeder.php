<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'gm']);
        // Same full access as 'gm' everywhere in the app (every role:gm gate
        // checks 'gm|co-gm') — a distinct role only so Co-GM shows as its own
        // label instead of a second identical "GM" entry.
        Role::firstOrCreate(['name' => 'co-gm']);
        Role::firstOrCreate(['name' => 'staff']);
    }
}
