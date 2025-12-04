<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Super Admin Role to a User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Assigning Super Admin Role to User...');
        $email = $this->ask("What is admins email?");
        $this->info("Searching for user with email: $email");
        $user = \App\Models\User::where('email', $email)->first();
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['guard_name' => 'api']);
        if (!$user) {
            $this->error("User with email $email not found!");
            return Command::FAILURE;
        }

        $user->assignRole($role);
        $this->info("Assigned Super Admin Role to user with email: $email");
        return Command::SUCCESS;
    }
}
