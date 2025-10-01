<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Filter by user
        if ($request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter by event
        if ($request->event) {
            $query->where('description', 'like', '%' . $request->event . '%');
        }

        // Filter by date
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(20);

        return Inertia::render('ActivityLogs/Index', [
            'activities' => $activities,
            'filters' => $request->only(['causer_id', 'event', 'date_from', 'date_to'])
        ]);
    }

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);

        return Inertia::render('ActivityLogs/Show', [
            'activity' => $activity
        ]);
    }
}