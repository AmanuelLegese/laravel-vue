<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class UserManagerController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Sort (whitelist for security)
        $sortable = ['name', 'email', 'created_at'];
        $sort = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $order = $request->order === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $order);

        return $query->paginate(10)->withQueryString();
    }
}
