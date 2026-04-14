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
        return view('public.home', $data);
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
        return view('public.page', compact('page'));
    }
}
