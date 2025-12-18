<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\Payroll;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index() {
        $totalWorkers = Schema::hasTable('workers') ? Worker::count() : 0;
        $presentToday = Schema::hasTable('attendance') ? Attendance::whereDate('date', today())->whereNotNull('clock_in')->count() : 0;
        $pendingLeave = 0; // placeholder, implement leave model if available
        $overtimeHours = Schema::hasTable('attendance') ? Attendance::whereNotNull('clock_out')->count() : 0;

        // Attendance data for last 30 days (simple counts)
        $attendance = [];
        if (Schema::hasTable('attendance')) {
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $attendance[] = [
                    'date' => $date,
                    'count' => Attendance::whereDate('date', $date)->count(),
                ];
            }
        }

        $kpis = [
            'totalWorkers' => $totalWorkers,
            'presentToday' => $presentToday,
            'pendingLeave' => $pendingLeave,
            'overtimeHours' => $overtimeHours,
        ];

        $shifts = [];
        $dummyShifts = [
            (object)['name' => 'Morning', 'start' => '08:00', 'end' => '16:00'],
            (object)['name' => 'Day', 'start' => '09:00', 'end' => '17:00'],
            (object)['name' => 'Night', 'start' => '22:00', 'end' => '06:00'],
        ];

        if (Schema::hasTable('shifts')) {
            $dbShifts = DB::table('shifts')->get();
            if ($dbShifts->isEmpty()) {
                $shifts = $dummyShifts;
            } else {
                $shifts = $dbShifts->map(function ($r) {
                    return (object)[
                        'name' => $r->name ?? 'Shift',
                        'start' => isset($r->start_time) ? substr($r->start_time, 0, 5) : ($r->start ?? null),
                        'end' => isset($r->end_time) ? substr($r->end_time, 0, 5) : ($r->end ?? null),
                    ];
                })->toArray();
            }
        } else {
            $shifts = $dummyShifts;
        }

        $totalPayroll = 0;
        $recentPayrolls = [];

        $dummyPayrolls = [
            (object)['worker_id' => 1, 'month' => 'Nov 2025', 'net_salary' => 45000, 'created_at' => now()],
            (object)['worker_id' => 2, 'month' => 'Nov 2025', 'net_salary' => 38000, 'created_at' => now()->subDay()],
            (object)['worker_id' => 3, 'month' => 'Nov 2025', 'net_salary' => 50000, 'created_at' => now()->subDays(2)],
        ];

        if (Schema::hasTable('payroll')) {
            $count = DB::table('payroll')->count();
            if ($count === 0) {
                $recentPayrolls = $dummyPayrolls;
                $totalPayroll = 0;
                foreach ($dummyPayrolls as $d) {
                    $totalPayroll += $d->net_salary;
                }
            } else {
                $totalPayroll = DB::table('payroll')->sum('net_salary');
                $recentPayrolls = DB::table('payroll')->orderByDesc('created_at')->limit(5)->get();
            }
        } else {
            $recentPayrolls = $dummyPayrolls;
            $totalPayroll = 0;
            foreach ($dummyPayrolls as $d) {
                $totalPayroll += $d->net_salary;
            }
        }

        return view('dashboard', compact('kpis','attendance','shifts','totalPayroll','recentPayrolls'));
    }
}
