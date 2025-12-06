<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignPermissionsToRoleRequest;
use App\Http\Requests\AssignUserToRoleRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\DeleteRoleRequest;
use App\Http\Resources\CreateRoleResource;
use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManageRole extends Controller
{
    //
    public function __invoke()
    {
        //
        $role = Role::with('permissions')->paginate(10);
        return CreateRoleResource::collection($role);
    }

    /**
     * Create new roles
     */
    public function createRole(CreateRoleRequest $request) : CreateRoleResource
    {
       foreach ($request->roles as $roleName) {
                Role::create(['name' => $roleName]);
            }
        $role = Role::paginate(10);
        $role->load('permissions');
        return CreateRoleResource::make($role);

    }

    /**
     * Delete a role
     */
    public function deleteRole(DeleteRoleRequest $request) : Response
    {
        $role = Role::whereIn('id',$request->roles)->delete();
        return response()->noContent();
    }

    /**
     * assign permissions to role
     */
    public function assignPermissionsToRole(AssignPermissionsToRoleRequest $request) : CreateRoleResource
    {
        //
        $roleModel = Role::whereIn('id',$request->roles)->get();
        $permissions = Permission::whereIn('id',$request->permissions)->pluck('name')->toArray();
        foreach ($roleModel as $role) {
            //
            $role->givePermissionTo($permissions);
        }
        $role = Role::with('permissions')->paginate(10);
        return CreateRoleResource::make($role);
    }


    /**
     * revoke permissions from role
     */
    public function revokePermissionsFromRole(AssignPermissionsToRoleRequest $request) : CreateRoleResource
    {
        //
        $roleModel = Role::whereIn('id',$request->roles)->get();
        $permissions = Permission::whereIn('id',$request->permissions)->pluck('name')->toArray();
        foreach ($roleModel as $role) {
            $role->revokePermissionTo($permissions);
        }
        $role = Role::with('permissions')->paginate(10);
        return CreateRoleResource::make($role);
    }

    /**
     * assign user to role
     */
    public function assignUserToRole(AssignUserToRoleRequest $request): UsersResource
    {
        //
        $roleModel = Role::whereIn('id',$request->roles)->pluck('name')->toArray();
            //
            foreach ($request->users as $userId) {
                $user = User::find($userId);
                $user->assignRole($roleModel);
            }
        $user = User::with('roles')->paginate(10);
        return UsersResource::make($user);

    }
    /**
     * revoke user from role
     */
    public function revokeUserFromRole(AssignUserToRoleRequest $request): UsersResource
    {
        //
        $roleModel = Role::whereIn('id',$request->roles)->pluck('name')->toArray();
            //
            foreach ($request->users as $userId) {
                $user = User::find($userId);
                $user->removeRole($roleModel);
            }
        $user = User::with('roles')->paginate(10);
        return UsersResource::make($user);

    }
}
