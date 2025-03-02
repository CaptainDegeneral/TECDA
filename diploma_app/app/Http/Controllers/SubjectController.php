<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\DestroySubjectRequest;
use App\Http\Requests\Subject\EditSubjectRequest;
use App\Http\Requests\Subject\ShowSubjectRequest;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Repositories\SubjectRepository;
use App\Services\SubjectService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

class SubjectController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $subjects = SubjectRepository::all($request->query('search_value'));

        return SubjectResource::collection($subjects)->response();
    }

    /**
     * @return JsonResponse
     */
    public function getList(): JsonResponse
    {
        $subjects = SubjectRepository::getAllWithoutPagination();

        return SubjectResource::collection($subjects)->response();
    }

    /**
     * @param ShowSubjectRequest $request
     * @return JsonResponse
     */
    public function show(ShowSubjectRequest $request): JsonResponse
    {
        $subject = SubjectRepository::get($request->id);

        return new SubjectResource($subject)->response();
    }

    /**
     * @param StoreSubjectRequest $request
     * @return JsonResponse
     */
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        try {
            SubjectService::create($request->name, $request->code);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.created'),
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.created'),
                ]
            ]);
        }
    }

    /**
     * @param EditSubjectRequest $request
     * @return JsonResponse
     */
    public function edit(EditSubjectRequest $request): JsonResponse
    {
        try {
            SubjectService::update($request->id, $request->name, $request->code);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.updated'),
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.updated'),
                ],
            ]);
        }
    }

    /**
     * @param DestroySubjectRequest $request
     * @return JsonResponse
     */
    public function destroy(DestroySubjectRequest $request): JsonResponse
    {
        try {
            SubjectService::delete($request->id);

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
