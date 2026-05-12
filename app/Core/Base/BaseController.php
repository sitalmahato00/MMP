<?php

namespace App\Core\Base;

use App\Core\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

/**
 * BaseController
 *
 * All ERP module controllers extend this class.
 * Provides unified JSON response helpers (ApiResponse trait)
 * and Laravel's built-in authorization.
 *
 * Usage:
 *   class StudentController extends BaseController { ... }
 */
abstract class BaseController extends Controller
{
    use AuthorizesRequests, ApiResponse;
}
