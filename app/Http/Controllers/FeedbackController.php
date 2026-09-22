<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'ux_feedback',
            'entity_type' => $validated['page'],
            'old_values' => ['rating' => $validated['rating']],
            'new_values' => ['comment' => $validated['comment'] ?? ''],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
