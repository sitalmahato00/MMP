<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
/**
 * HomeController — renders public Blade views.
 * NEVER queries the database directly.
 * ALL data flows through: HomeController → PublicDataService → DB
 */
class HomeController extends Controller
{
    public function __construct(private PublicDataService $service) {}

    public function index()
    {
        $data = $this->service->getHomepageData();
        $leadership = $this->service->getLeadership();
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');

        // Home welcome box should use its own managed setting.
        $welcomeMessage = trim((string) optional($siteSettings->get('welcome_message'))->value);
        if ($welcomeMessage !== '') {
            $siteSettings->put('what_is_mmp', $siteSettings->get('welcome_message'));
        }

        // Principal section should prioritize Web Control's principal message.
        $principalName = trim((string) optional($siteSettings->get('principal_name'))->value);
        $principalMessage = trim((string) optional($siteSettings->get('principals_message'))->value);
        $principalPhoto = trim((string) optional($siteSettings->get('principal_photo'))->value);

        if (isset($leadership['principals'])) {
            $principals = collect($leadership['principals']);
            $currentPrincipal = $principals->firstWhere('is_current', true);

            if (!$currentPrincipal) {
                $currentPrincipal = (object) [
                    'name' => 'Principal',
                    'designation' => 'Principal, MMP',
                    'avatar' => null,
                    'message' => null,
                    'is_current' => true,
                ];
                $principals = collect([$currentPrincipal])->merge($principals);
            }

            if ($principalName !== '') {
                $currentPrincipal->name = ltrim($principalName, "- \t");
            }
            if ($principalMessage !== '') {
                $currentPrincipal->message = $principalMessage;
            }
            if ($principalPhoto !== '') {
                $currentPrincipal->avatar = $principalPhoto;
            }

            $leadership['principals'] = $principals;
        }

        $staff = $this->service->getStaff();
        $newsEvents = $this->service->getLatestNewsEvents(5);
        $ctevtGeneralNotices = $this->service->getCtevtGeneralNotices(5);
        $ctevtResultNotices = $this->service->getCtevtResultNotices(5);
        $recentDownloads = $this->service->getRecentDownloads(4);
        $stats = $this->service->getHomepageStats();

        return view('public.home', array_merge($data, compact('leadership', 'siteSettings', 'staff', 'newsEvents', 'ctevtGeneralNotices', 'ctevtResultNotices', 'recentDownloads', 'stats')));
    }

    public function notices(Request $request)
    {
        $activeType = in_array($request->string('type')->toString(), ['general', 'exam', 'news', 'event', 'ctevt-general', 'ctevt-result'], true)
            ? $request->string('type')->toString()
            : 'general';

        $notices = in_array($activeType, ['general', 'exam', 'news', 'event'], true)
            ? $this->service->getNotices(15, $activeType)
            : $this->service->getNotices(15, 'general');
        $ctevtGeneralNotices = $this->service->getCtevtGeneralNotices(10);
        $ctevtResultNotices = $this->service->getCtevtResultNotices(10);

        return view('public.notices', compact('notices', 'activeType', 'ctevtGeneralNotices', 'ctevtResultNotices'));
    }

    public function departments()
    {
        $departments = $this->service->getDepartments();
        return view('public.departments', compact('departments'));
    }

    public function departmentShow(string $slug)
    {
        $department = $this->service->getDepartmentBySlug($slug);
        return view('public.department-show', compact('department'));
    }

    public function downloads(Request $request)
    {
        $category = trim($request->string('category')->toString());
        $downloads = $this->service->getDownloads($category !== '' ? $category : null);
        return view('public.downloads', compact('downloads'));
    }

    public function page(string $slug)
    {
        $page = $this->service->getPage($slug);
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');

        return view('public.content-page', compact('page', 'siteSettings'));
    }

    public function leadership()
    {
        $leadership = $this->service->getLeadership();
        return view('public.leadership', $leadership);
    }

    public function facilities()
    {
        $facilities = $this->service->getFacilities();
        return view('public.facilities', compact('facilities'));
    }

    public function newsEvents(Request $request)
    {
        $notices = $this->service->getNewsEvents(12);
        return view('public.news-events', compact('notices'));
    }

    public function gallery()
    {
        $media = $this->service->getGalleryMedia();
        return view('public.gallery', compact('media'));
    }

    public function result()
    {
        $resultForm = $this->service->getCtevtResultForm();

        return view('public.result', compact('resultForm'));
    }

    public function questionBank()
    {
        $downloads = $this->service->getQuestionBankDownloads();
        return view('public.question-bank', compact('downloads'));
    }

    public function staff()
    {
        $staff = $this->service->getStaff();
        $departments = $this->service->getDepartments();
        return view('public.staff', compact('staff', 'departments'));
    }

    public function contact()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.contact', compact('siteSettings'));
    }

    public function alumniDirectory()
    {
        $alumni = $this->service->getFeaturedAlumni(20);
        $departments = $this->service->getDepartments();
        return view('public.alumni', compact('alumni', 'departments'));
    }

    public function alumniProfile(int $id)
    {
        $alumnus = \App\Models\Alumni::with(['user', 'department', 'program'])->findOrFail($id);
        return view('public.alumni-profile', compact('alumnus'));
    }

    public function about()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.about', compact('siteSettings'));
    }

}