<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Permissions from all Models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $models = $this->getModels();
        $permissions = ["index", "store", "show", "update", "destroy"];

        foreach ($models as $model) {
            foreach ($permissions as $permission) {
                $permName = strtolower($model . '-' . $permission);
                // create permission if not exists
                if (!\Spatie\Permission\Models\Permission::where('name', $permName)->exists()) {
                    \Spatie\Permission\Models\Permission::create(['name' => $permName, 'guard_name' => 'api']);
                    $this->info("Created permission: " . $permName);
                } else {
                    $this->info("Permission already exists: " . $permName);
                }
            }
        }
    }

    /**
     * get all models from a given directory
     */

    protected function getModels()
    {
        return collect(glob(app_path('Models/*.php')))
            ->map(function ($path) {
                return basename($path, '.php');
            });
    }

}
