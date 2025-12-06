<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GrantPermissionRequest;
use App\Http\Requests\MassGrantPermissionRequest;
use App\Http\Resources\GrantPermissionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagePermission extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        $request->user()->load('permissions');
        return GrantPermissionResource::make($request->user());
    }

    /**
     * Get permissions for the authenticated user.
     */
    public function getPermission(Request $request): array
    {
        // Example logic to get permissions for the authenticated user
        $permissions = \Spatie\Permission\Models\Permission::pluck("name","id");
        return $permissions->toArray();
    }

    /**
     * Grant a permission to the authenticated user.
     */
    public function grantPermission(GrantPermissionRequest $request): GrantPermissionResource
    {
        /**
         * Example logic to grant a permission to the authenticated user
         */
        // $request->user_id,$request->permission,$request->all
        $permissions = \Spatie\Permission\Models\Permission::whereIn("id",$request->permission)->pluck("name","id");
        $user = User::find($request->user_id);
        $user->givePermissionTo($permissions->toArray());
        $user->load('permissions');
        return GrantPermissionResource::make($user);
    }

    /**
     * Revoke a permission from the authenticated user.
     */
    public function revokePermission(GrantPermissionRequest $request): GrantPermissionResource
    {
        /**
         * Example logic to grant a permission to the authenticated user
         */
        $permissions = \Spatie\Permission\Models\Permission::whereIn("id",$request->permission)->pluck("name","id");
        $user = User::find($request->user_id);
        $user->revokePermissionTo($permissions->toArray());
        $user->load('permissions');
        return GrantPermissionResource::make($user);
    }

    /**
     * Mass grant permissions to multiple users.
     */
    public function massgrantPermission(MassGrantPermissionRequest $request): GrantPermissionResource
    {
        //
        $users = User::whereIn('id',$request->user)->get();
        foreach($users as $user){
            $permissions = \Spatie\Permission\Models\Permission::whereIn("id",$request->permission)
                                ->pluck("name")
                                ->toArray();
            $user->givePermissionTo($permissions);
        }
            $users->load('permissions');
        return GrantPermissionResource::make($users);
    }

    /**
     * Mass revoke permissions from multiple users.
     */
    public function massrevokePermission(MassGrantPermissionRequest $request): GrantPermissionResource
    {
        //
        $users = User::whereIn('id',$request->user)->get();
        foreach($users as $user){
            $permissions = \Spatie\Permission\Models\Permission::whereIn("id",$request->permission)
                                ->pluck("name")
                                ->toArray();
            $user->revokePermissionTo($permissions);
        }
        $users->load('permissions');
        return GrantPermissionResource::make($users);
    }
}
