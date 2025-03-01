<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = RoleRepository::all();

        return RoleResource::collection($roles)->response();
    }
}
