<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/page', function () {
    return view('mypage');
});
Route::post('/page', function (\Illuminate\Http\Request $r) {
    $r->validate([ 'name'=>'required|string|max:50', 'message'=>'required|string|max:500' ]);
    $resp = "Hello <strong>".e($r->name)."</strong>! You said: <em>".e($r->message)."</em>";
    return redirect()->back()->with('page_response', $resp);
});

Route::get('/register',[AuthController::class,'RegisterForm'])->name('register');
Route::post('/register',[AuthController::class,'register'])->name('register.post');
Route::get('/login',[AuthController::class,'loginForm'])->name('login');
Route::post('/login',[AuthController::class,'login'])->name('login.post');


Route::get('/dashboard',[HomeController::class,'index']);
Route::get('/dashboard',[HomeController::class,'index'])->name('dashboard');
Route::get('/logout',[AuthController::class,'logout']);
Route::post('/logout',[AuthController::class,'logout'])->name('logout');

Route::get('/workers',[WorkerController::class,'index'])->name('workers.index');
Route::post('/workers',[WorkerController::class,'store'])->name('workers.store');

Route::get('/attendance',[AttendanceController::class,'index'])->name('attendance.index');
Route::post('/attendance/in/{id}',[AttendanceController::class,'clockIn'])->name('attendance.in');
Route::post('/attendance/out/{id}',[AttendanceController::class,'clockOut'])->name('attendance.out');

Route::get('/payroll',[PayrollController::class,'index'])->name('payroll.index');
Route::post('/payroll/generate',[PayrollController::class,'generate'])->name('payroll.generate');
Route::post('/payroll',[PayrollController::class,'store'])->name('payroll.store');
Route::put('/payroll/{id}',[PayrollController::class,'update'])->name('payroll.update');
Route::delete('/payroll/{id}',[PayrollController::class,'destroy'])->name('payroll.destroy');

Route::get('/attendance/export', function(){
    if (!\Illuminate\Support\Facades\Schema::hasTable('attendance')) {
        return redirect()->back()->with('error','No attendance table');
    }
    $rows = \App\Models\Attendance::with('worker.user')->orderBy('date')->get();
    $filename = 'attendance_export_'.date('Ymd_His').'.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
    ];
    $callback = function() use ($rows) {
        $handle = fopen('php://output','w');
        fputcsv($handle, ['Worker ID','Employee Code','Name','Date','Clock In','Clock Out']);
        foreach($rows as $r){
            fputcsv($handle, [ $r->worker_id, optional($r->worker)->employee_code, optional(optional($r->worker)->user)->name, $r->date, $r->clock_in, $r->clock_out ]);
        }
        fclose($handle);
    };
    return response()->stream($callback, 200, $headers);
});

Route::get('/shifts',[ShiftController::class,'index'])->name('shifts.index');
Route::post('/shifts',[ShiftController::class,'store'])->name('shifts.store');
Route::put('/shifts/{id}',[ShiftController::class,'update'])->name('shifts.update');
Route::delete('/shifts/{id}',[ShiftController::class,'destroy'])->name('shifts.destroy');
