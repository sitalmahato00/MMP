<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::published()->latest()->paginate(15);

        return view('parent.notices', compact('notices'));
    }
}
