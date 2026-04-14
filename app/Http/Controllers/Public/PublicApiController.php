<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * PublicApiController — the ONLY authorized gateway for public pages to access data.
 * All public pages must call these endpoints. Direct DB access from public pages is forbidden.
 */
class PublicApiController extends Controller
{
    public function __construct(private PublicDataService $service) {}

    /** GET /api/v1/public/homepage */
    public function homepage(): JsonResponse
    {
        return response()->json($this->service->getHomepageData());
    }

    /** GET /api/v1/public/notices */
    public function notices(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);
        return response()->json($this->service->getNotices($perPage));
    }

    /** GET /api/v1/public/departments */
    public function departments(): JsonResponse
    {
        return response()->json($this->service->getDepartments());
    }

    /** GET /api/v1/public/departments/{slug} */
    public function departmentShow(string $slug): JsonResponse
    {
        return response()->json($this->service->getDepartmentBySlug($slug));
    }

    /** GET /api/v1/public/alumni */
    public function alumni(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 8), 20);
        return response()->json($this->service->getFeaturedAlumni($limit));
    }

    /** GET /api/v1/public/downloads */
    public function downloads(): JsonResponse
    {
        return response()->json($this->service->getDownloads());
    }

    /** GET /api/v1/public/pages/{slug} */
    public function page(string $slug): JsonResponse
    {
        return response()->json($this->service->getPage($slug));
    }
}
