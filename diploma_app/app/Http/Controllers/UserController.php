<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\DestroyUserRequest;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use LogicException;
use Throwable;

class UserController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = UserRepository::all();

        return UserResource::collection($users)->response();
    }

    /**
     * @param ShowUserRequest $request
     * @return JsonResponse
     */
    public function show(ShowUserRequest $request): JsonResponse
    {
        $user = UserRepository::get($request->id);

        return new UserResource($user)->response();
    }

    /**
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            UserService::create($request);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.created')
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.created')
                ]
            ]);
        }
    }

    /**
     * @param EditUserRequest $request
     * @return JsonResponse
     */
    public function edit(EditUserRequest $request): JsonResponse
    {
        try {
            UserService::edit($request);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.updated'),
                ]
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.updated'),
                ]
            ]);
        }
    }

    /**
     * @param DestroyUserRequest $request
     * @return JsonResponse
     */
    public function destroy(DestroyUserRequest $request): JsonResponse
    {
        try {
            UserService::delete($request->id);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.deleted'),
                ]
            ]);
        } catch (LogicException $exception) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.deleted'),
                ]
            ]);
        }
    }
}
