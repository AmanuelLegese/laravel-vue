<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRole extends Command
{
    protected $signature = 'pharmacy:role {email} {role=admin : admin or pharmacist}';

    protected $description = 'Create the admin/pharmacist roles if missing and give a user one of them';

    public function handle(): int
    {
        $role = $this->argument('role');

        if (! in_array($role, ['admin', 'pharmacist'], true)) {
            $this->error('Role must be admin or pharmacist.');
            return self::FAILURE;
        }

        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user with that email.');
            return self::FAILURE;
        }

        foreach (['admin', 'pharmacist'] as $name) {
            Role::findOrCreate($name);
        }

        $user->assignRole(Role::findByName($role));
        $this->info("{$user->email} is now {$role}.");

        return self::SUCCESS;
    }
}
