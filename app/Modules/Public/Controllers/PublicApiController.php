<?php

namespace App\Modules\Public\Controllers;


/**
 * PublicApiController — the ONLY authorized gateway for public pages to access data.
 * All public pages must call these endpoints. Direct DB access from public pages is forbidden.
 */
use App\Http\Controllers\Controller;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\CMS\Models\Page;
use App\Modules\Exam\Models\Exam;
use App\Modules\Staff\Models\Staff;
use App\Services\PublicDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $type = $request->input('type');
        if (!in_array($type, ['general', 'exam'], true)) {
            $type = null;
        }

        return response()->json($this->service->getNotices($perPage, $type));
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

    /** GET /api/v1/public/facilities */
    public function facilities(): JsonResponse
    {
        return response()->json($this->service->getFacilities());
    }

    /** GET /api/v1/public/staff */
    public function staff(): JsonResponse
    {
        return response()->json($this->service->getStaff());
    }

    /** GET /api/v1/public/site-settings */
    public function siteSettings(): JsonResponse
    {
        return response()->json($this->service->getSiteSettings());
    }

    /** GET /api/v1/public/leadership */
    public function leadership(): JsonResponse
    {
        return response()->json($this->service->getLeadership());
    }

    /** GET /api/v1/public/gallery */
    public function gallery(Request $request): JsonResponse
    {
        return response()->json($this->service->getGalleryMedia());
    }

    /** GET /api/v1/public/people */
    public function people(Request $request): JsonResponse
    {
        $department = $request->input('department');
        return response()->json($this->service->getPeopleByDepartment($department));
    }

    /** GET /api/v1/public/news-events */
    public function newsEvents(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 50);
        return response()->json($this->service->getNewsEvents($perPage));
    }

    /** GET /api/v1/public/news-events/{slug} */
    public function newsEventShow(string $slug): JsonResponse
    {
        return response()->json($this->service->getPublishedItemBySlug($slug));
    }

    /** GET /api/v1/public/notices/{slug} */
    public function noticeShow(string $slug): JsonResponse
    {
        $notice = $this->service->getPublishedItemBySlug($slug);
        $related = $this->service->getRelatedItemsByType($notice->type, $notice->id);
        return response()->json(['notice' => $notice, 'related' => $related]);
    }

    /** GET /api/v1/public/question-bank */
    public function questionBank(Request $request): JsonResponse
    {
        return response()->json($this->service->getQuestionBankDownloads());
    }

    /** GET /api/v1/public/alumni-directory */
    public function alumniDirectory(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 50);
        $result = $this->service->getAlumniDirectory(
            $request->input('department_id') ? (int) $request->input('department_id') : null,
            $request->input('search'),
            $request->input('year'),
            $perPage,
        );
        $years = $this->service->getAlumniGraduationYears();
        $departments = $this->service->getDepartments();
        return response()->json([
            'alumni' => $result,
            'graduation_years' => $years,
            'departments' => $departments,
        ]);
    }

    /** GET /api/v1/public/alumni/{id} */
    public function alumniProfile(int $id): JsonResponse
    {
        return response()->json($this->service->getAlumniProfile($id));
    }

    /** GET /api/v1/public/departments/{deptSlug}/programs/{programSlug} */
    public function programShow(string $deptSlug, string $programSlug): JsonResponse
    {
        return response()->json($this->service->getProgramBySlug($deptSlug, $programSlug));
    }

    /** GET /api/v1/public/result */
    public function resultForm(): JsonResponse
    {
        return response()->json($this->service->getCtevtResultForm());
    }
}
