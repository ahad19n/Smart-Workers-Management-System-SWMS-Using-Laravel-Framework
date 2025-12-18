<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class WorkerController extends Controller
{
    public function index() {
        $workers = Worker::with('user')->get();
        return view('workers',compact('workers'));
    }

    public function store(Request $r) {
        $rules = [
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6',
            'employee_code'=>'required|string|max:100|unique:workers,employee_code',
            'position'=>'required|string|max:100',
            'join_date'=>'nullable|date',
            'skills'=>'nullable|string',
        ];
        $r->validate($rules);

        DB::transaction(function() use ($r){
            $user = User::create([
                'name'=>$r->name,
                'email'=>$r->email,
                'password'=>Hash::make($r->password),
                'role'=>'worker',
            ]);

            Worker::create([
                'user_id'=>$user->id,
                'employee_code'=>$r->employee_code,
                'position'=>$r->position,
                'join_date'=>$r->join_date ?? now()->toDateString(),
                'skills'=>$r->skills ?? null,
                'status'=>'active',
            ]);
        });

        return redirect()->route('workers.index')->with('success','Worker added');
    }
}
