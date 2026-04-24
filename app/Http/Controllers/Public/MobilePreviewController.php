<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MobilePreviewController extends Controller
{
    public function __invoke(): View
    {
        return view('public.app-preview');
    }
}
