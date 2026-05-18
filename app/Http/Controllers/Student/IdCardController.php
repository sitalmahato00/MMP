<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class IdCardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student?->loadMissing('program.department', 'academicSession', 'user');

        if (! $student) {
            abort(403, 'Student profile not found');
        }

        $settings = SiteSetting::whereIn('key', [
            'college_name', 'college_affiliation', 'site_logo',
            'contact_address', 'contact_phone', 'contact_email', 'principal_name',
        ])->pluck('value', 'key')->toArray();

        $collegeName = $settings['college_name']       ?? 'Manmohan Memorial Polytechnic';
        $affiliation = $settings['college_affiliation'] ?? 'CTEVT';
        $address     = $settings['contact_address']    ?? '';
        $phone       = $settings['contact_phone']      ?? '';
        $email       = $settings['contact_email']      ?? '';
        $principal   = $settings['principal_name']     ?? 'Principal';
        $logoUrl     = ($settings['site_logo'] ?? null)
                        ? Storage::disk('public')->url($settings['site_logo'])
                        : null;

        $photoUrl    = $student->user->avatar_url ?? null;
        $validUpto   = bsDate(now()->addYear()->format('Y') . '-06-30') ?? '';
        $issueDate   = bsDate(now()->format('Y-m-d')) ?? '';
        $dob         = $student->user->dob ? bsDate($student->user->dob) : null;

        return view('student.id-card.index', compact(
            'student',
            'collegeName', 'affiliation', 'address', 'phone', 'email', 'principal',
            'logoUrl', 'photoUrl', 'validUpto', 'issueDate', 'dob'
        ));
    }
}
