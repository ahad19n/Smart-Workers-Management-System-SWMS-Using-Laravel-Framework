<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Leave;
use Illuminate\Support\Facades\Log;

Route::get('/workers', function (Request $request) {
    if (Schema::hasTable('workers')) {
        return Worker::with('user')->get()->map(function($w){
            return [
                'id'=>$w->id,
                'name'=> optional($w->user)->name ?? $w->employee_code,
                'position'=>$w->position,
                'join'=> optional($w->join_date)?$w->join_date->toDateString():null,
                'skills'=>$w->skills,
            ];
        });
    }
    // mock data
    return [
        ['id'=>1,'name'=>'Ali Khan','position'=>'Operator','join'=>'2024-01-12','skills'=>'Welding,PLC'],
        ['id'=>2,'name'=>'Sara Ahmed','position'=>'Supervisor','join'=>'2023-08-01','skills'=>'Leadership,Reporting']
    ];
});

Route::post('/workers', function (Request $request) {
    $rules = [
        'name'=>'required|string|max:255',
        'email'=>'required|email|max:255',
        'password'=>'required|string|min:6',
        'employee_code'=>'required|string|max:100',
        'position'=>'required|string|max:100',
        'join_date'=>'nullable|date',
        'skills'=>'nullable|string',
    ];
    $request->validate($rules);

    // If workers table doesn't exist or users table missing, return helpful error
    if (!Schema::hasTable('workers') || !Schema::hasTable('users')) {
        return response()->json(['error'=>'Required tables not present'], 500);
    }

    // Create user + worker in transaction
    return DB::transaction(function() use ($request) {
        // ensure email and employee_code uniqueness
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error'=>'User with this email already exists'], 422);
        }
        if (Worker::where('employee_code', $request->employee_code)->exists()) {
            return response()->json(['error'=>'Employee code already exists'], 422);
        }

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>'worker',
        ]);

        $worker = Worker::create([
            'user_id'=>$user->id,
            'employee_code'=>$request->employee_code,
            'position'=>$request->position,
            'join_date'=>$request->join_date ?? now()->toDateString(),
            'skills'=>$request->skills ?? null,
            'status'=>'active',
        ]);

        return response()->json(['ok'=>true,'worker'=>[
            'id'=>$worker->id,
            'employee_code'=>$worker->employee_code,
            'name'=>$user->name,
            'email'=>$user->email,
        ]], 201);
    });
});

Route::post('/attendance/clock', function (Request $request) {
    $request->validate(['id'=>'required','type'=>'required']);
    $type = $request->input('type');
    $time = now()->format('H:i:s');
    if (Schema::hasTable('attendance')) {
        try {
            if ($type === 'in') {
                Attendance::create(['worker_id'=>$request->id,'date'=>now()->toDateString(),'clock_in'=>$time]);
            } else {
                Attendance::where('worker_id',$request->id)->whereDate('date',now())->update(['clock_out'=>$time]);
            }
        } catch (\Throwable $e) {
            // fallback to mock
        }
    }
    return ['id'=>$request->id,'time'=>$time,'type'=>$type];
});

Route::get('/attendance/today', function () {
    if (Schema::hasTable('attendance')) {
        $rows = Attendance::whereDate('date',now())->with('worker.user')->get();
        return $rows->map(function($r){
            return [
                'id'=>optional($r->worker)->employee_code ?? $r->worker_id,
                'name'=>optional(optional($r->worker)->user)->name ?? null,
                'shift'=>optional($r->worker)->position ?? null,
                'in'=>$r->clock_in,
                'out'=>$r->clock_out,
                'status'=>$r->clock_in? 'Present':'Absent',
            ];
        });
    }
    return [
        ['id'=>'W-001','name'=>'Ali Khan','shift'=>'Morning','in'=>'08:06','out'=>null,'status'=>'Present'],
        ['id'=>'W-002','name'=>'Sara Ahmed','shift'=>'Day','in'=>null,'out'=>null,'status'=>'Absent']
    ];
});

Route::post('/workers/{id}/deactivate', function (Request $request, $id) {
    if (!Schema::hasTable('workers')) {
        return response()->json(['error'=>'Workers table not found'], 404);
    }
    $w = Worker::find($id);
    if (!$w) return response()->json(['error'=>'Worker not found'],404);
    $w->status = 'inactive';
    $w->save();
    return response()->json(['ok'=>true]);
});

// Leave request endpoints
Route::post('/leaves', function(Request $request){
    $rules = [
        'worker_id'=>'required|integer',
        'start_date'=>'required|date',
        'end_date'=>'required|date|after_or_equal:start_date',
        'reason'=>'nullable|string',
    ];
    $request->validate($rules);
    if (!Schema::hasTable('leaves') || !Schema::hasTable('workers')) {
        return response()->json(['error'=>'Required tables not present'],500);
    }
    $w = Worker::find($request->worker_id);
    if (!$w) return response()->json(['error'=>'Worker not found'],404);
    $leave = Leave::create([
        'worker_id'=>$w->id,
        'start_date'=>$request->start_date,
        'end_date'=>$request->end_date,
        'reason'=>$request->reason ?? null,
        'status'=>'pending',
    ]);
    return response()->json(['ok'=>true,'leave'=>$leave],201);
});

Route::get('/leaves', function(Request $request){
    if (!Schema::hasTable('leaves')) return [];
    return Leave::with('worker.user')->orderBy('status')->orderByDesc('created_at')->get()->map(function($l){
        return [
            'id'=>$l->id,
            'worker_id'=>$l->worker_id,
            'employee_code'=>optional($l->worker)->employee_code,
            'name'=>optional(optional($l->worker)->user)->name,
            'start'=>$l->start_date?->toDateString(),
            'end'=>$l->end_date?->toDateString(),
            'reason'=>$l->reason,
            'status'=>$l->status,
        ];
    });
});

Route::post('/leaves/{id}/approve', function(Request $request, $id){
    if (!Schema::hasTable('leaves')) return response()->json(['error'=>'Leaves table missing'],500);
    $l = Leave::find($id); if(!$l) return response()->json(['error'=>'Not found'],404);
    $l->status = 'approved'; $l->save(); return response()->json(['ok'=>true,'leave'=>$l]);
});

Route::post('/leaves/{id}/reject', function(Request $request, $id){
    if (!Schema::hasTable('leaves')) return response()->json(['error'=>'Leaves table missing'],500);
    $l = Leave::find($id); if(!$l) return response()->json(['error'=>'Not found'],404);
    $l->status = 'rejected'; $l->save(); return response()->json(['ok'=>true,'leave'=>$l]);
});

// Mobile sync: accept array of attendance records and upsert
Route::post('/attendance/sync', function(Request $request){
    $data = $request->input('records');
    if (!is_array($data)) return response()->json(['error'=>'Invalid payload'],400);
    $created = 0; $updated = 0;
    foreach($data as $rec){
        $wid = $rec['worker_id'] ?? null; $date = $rec['date'] ?? now()->toDateString();
        if (!$wid) continue;
        $att = Attendance::where('worker_id',$wid)->whereDate('date',$date)->first();
        if ($att){
            $att->fill([ 'clock_in'=>$rec['clock_in'] ?? $att->clock_in, 'clock_out'=>$rec['clock_out'] ?? $att->clock_out ]);
            $att->save(); $updated++;
        } else {
            Attendance::create(['worker_id'=>$wid,'date'=>$date,'clock_in'=>$rec['clock_in'] ?? null,'clock_out'=>$rec['clock_out'] ?? null]); $created++;
        }
    }
    return response()->json(['ok'=>true,'created'=>$created,'updated'=>$updated]);
});
