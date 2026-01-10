<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;

class AzureRoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function getPermissions(Role $role)
    {
        return response()->json([]);
    }
}