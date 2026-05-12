<?php

namespace App\Modules\Notification\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Communication;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $messages = Communication::with(['sender', 'receiver'])
            ->when($request->search, function ($query) use ($request) {
                $term = trim((string) $request->search);

                $query->where(function ($query) use ($term) {
                    $query->where('subject', 'like', "%{$term}%")
                        ->orWhere('message', 'like', "%{$term}%")
                        ->orWhereHas('sender', fn ($query) => $query->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('receiver', fn ($query) => $query->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Communication::count(),
            'unread' => Communication::unread()->count(),
            'sent_by_me' => auth()->id() ? Communication::where('sender_id', auth()->id())->count() : 0,
            'received_by_me' => auth()->id() ? Communication::where('receiver_id', auth()->id())->count() : 0,
        ];

        return view('admin.messages.index', compact('messages', 'stats'));
    }
}