<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\DestroyReportRequest;
use App\Http\Requests\Report\EditReportRequest;
use App\Http\Requests\Report\ExportReportRequest;
use App\Http\Requests\Report\ShowReportRequest;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Repositories\ReportRepository;
use App\Services\ReportService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userId = $request->query('user_id');
        $searchValue = $request->query('search_value');

        if ($user->role_id === 1) {
            $reports = $userId ? ReportRepository::getByUser($userId, $searchValue) : ReportRepository::all($searchValue);
        } else {
            $reports = ReportRepository::getByUser($user->id, $searchValue);
        }

        return ReportResource::collection($reports)->response();
    }

    /**
     * @param ShowReportRequest $request
     * @return JsonResponse
     */
    public function show(ShowReportRequest $request): JsonResponse
    {
        $report = ReportRepository::get($request->id);
        $accessResponse = $this->checkAccess($report);

        if ($accessResponse) {
            return $accessResponse;
        }

        return new ReportResource($report)->response();
    }

    /**
     * @param StoreReportRequest $request
     * @return JsonResponse
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $user = Auth::user();
        $userId = $request->user_id ?? $user->id;

        $accessResponse = $this->checkAccess($userId);

        if ($accessResponse) {
            return $accessResponse;
        }

        try {
            $report = ReportService::create($request);

            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.created'),
                    'report' => [
                        'id' => $report->id,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.created'),
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param EditReportRequest $request
     * @return JsonResponse
     */
    public function edit(EditReportRequest $request): JsonResponse
    {
        $report = ReportRepository::get($request->id);
        $accessResponse = $this->checkAccess($report);
        if ($accessResponse) {
            return $accessResponse;
        }

        try {
            ReportService::update($request);
            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.updated'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.updated'),
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DestroyReportRequest $request
     * @return JsonResponse
     */
    public function destroy(DestroyReportRequest $request): JsonResponse
    {
        $report = ReportRepository::get($request->id);
        $accessResponse = $this->checkAccess($report);

        if ($accessResponse) {
            return $accessResponse;
        }

        try {
            ReportService::delete($request->id);
            return response()->json([
                'data' => [
                    'success' => true,
                    'message' => __('messages.success.deleted'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.deleted'),
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Экспорт отчета в Word и Excel
     *
     * @param ExportReportRequest $request
     * @return BinaryFileResponse|JsonResponse
     */
    public function export(ExportReportRequest $request): BinaryFileResponse|JsonResponse
    {
        try {
            //return ReportService::export($request->id);
            return ReportService::exportTest();
        } catch (Exception $e) {
            Log::critical($e->getMessage());
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'При попытке экспорта произошла ошибка',
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Проверка прав доступа
     *
     * @param $target
     * @return JsonResponse|null
     */
    private function checkAccess($target): ?JsonResponse
    {
        $user = auth()->user();
        $targetUserId = is_int($target) ? $target : $target->user_id;

        if ($user->role_id !== 1 && $targetUserId !== $user->id) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => __('messages.errors.unauthorized'),
                ]
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        return null;
    }
}
