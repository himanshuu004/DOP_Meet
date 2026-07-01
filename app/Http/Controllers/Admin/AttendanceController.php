<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attendance::query()->with('user')->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($designation = $request->string('designation')->trim()->toString()) {
            $query->where('designation', $designation);
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $attendances = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Attendance::count(),
            'by_designation' => Attendance::query()
                ->selectRaw('designation, count(*) as total')
                ->groupBy('designation')
                ->pluck('total', 'designation'),
        ];

        return view('attendance.index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'designations' => config('attendance.designations'),
            'filters' => $request->only(['search', 'designation', 'from', 'to']),
        ]);
    }

    public function export(AttendanceExporter $exporter): StreamedResponse
    {
        return $exporter->downloadAll();
    }
}
