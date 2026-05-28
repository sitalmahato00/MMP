<?php

namespace App\Modules\Cms\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Cms\Models\Executive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExecutiveApiController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Executive::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('designation', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_current', $request->status === '1');
        }

        $executives = $query->orderBy('order')->get();

        return $this->success($executives);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:principal,president',
            'designation'  => 'nullable|string|max:255',
            'start_date_bs' => 'required|string|max:10',
            'end_date_bs'  => 'nullable|string|max:10',
            'message'      => 'nullable|string',
            'order'        => 'required|integer',
            'avatar'       => 'nullable|image|max:2048',
        ]);

        $data['is_current'] = $request->boolean('is_current');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('executives', 'public');
        }

        $executive = Executive::create($data);

        return $this->created($executive, 'Executive profile saved.');
    }

    public function show(Executive $executive): JsonResponse
    {
        return $this->success($executive);
    }

    public function update(Request $request, Executive $executive): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:principal,president',
            'designation'  => 'nullable|string|max:255',
            'start_date_bs' => 'required|string|max:10',
            'end_date_bs'  => 'nullable|string|max:10',
            'message'      => 'nullable|string',
            'order'        => 'required|integer',
            'avatar'       => 'nullable|image|max:2048',
        ]);

        $data['is_current'] = $request->boolean('is_current');

        if ($request->hasFile('avatar')) {
            if ($executive->avatar) {
                Storage::disk('public')->delete($executive->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('executives', 'public');
        }

        $executive->update($data);

        return $this->success($executive, 'Executive profile updated.');
    }

    public function destroy(Executive $executive): JsonResponse
    {
        if ($executive->avatar) {
            Storage::disk('public')->delete($executive->avatar);
        }
        $executive->delete();

        return $this->success(['message' => 'Executive removed.']);
    }
}
