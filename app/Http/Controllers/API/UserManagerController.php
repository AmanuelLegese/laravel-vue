<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserManagerController extends Controller
{
    //
    public function __invoke()
    {
        return User::get();
    }
}
