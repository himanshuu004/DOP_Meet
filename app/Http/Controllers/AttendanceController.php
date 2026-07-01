<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function create(): View
    {
        return view('attendance.create', [
            'designations' => config('attendance.designations'),
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        Attendance::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('attendance.create')
            ->with('success', 'Attendance entry saved successfully.');
    }
}
