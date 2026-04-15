<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicDataService;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $staff = $this->service->getStaff();
        return view('public.home', array_merge($data, compact('leadership', 'siteSettings', 'staff')));
    }

    public function notices()
    {
        $notices = $this->service->getNotices(15);
        return view('public.notices', compact('notices'));
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

    public function downloads()
    {
        $downloads = $this->service->getDownloads();
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

    public function about()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.about', compact('siteSettings'));
    }

    public function contact()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.contact', compact('siteSettings'));
    }
}
