<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Worker;

class AttendanceController extends Controller
{
    public function index()
    {
        $workers = Worker::all();
        $attendances = Attendance::whereDate('date', today())->get();
        return view('attendance', compact('workers','attendances'));
    }
    public function clockIn($workerId) {
        Attendance::create([
            'worker_id'=>$workerId,
            'date'=>now()->toDateString(),
            'clock_in'=>now()->format('H:i:s')
        ]);
        return back();
    }

    public function clockOut($workerId) {
        Attendance::where('worker_id',$workerId)
            ->whereDate('date',now())
            ->update(['clock_out'=>now()->format('H:i:s')]);
        return back();
    }
}

