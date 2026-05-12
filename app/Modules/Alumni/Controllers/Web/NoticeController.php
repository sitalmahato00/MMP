<?php

namespace App\Modules\Alumni\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::published()->latest()->paginate(15);
        return view('alumni.notices', compact('notices'));
    }
}
