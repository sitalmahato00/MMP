<?php

namespace App\Modules\Public\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MobilePreviewController extends Controller
{
    public function __invoke(): View
    {
        return view('public.app-preview');
    }
}
