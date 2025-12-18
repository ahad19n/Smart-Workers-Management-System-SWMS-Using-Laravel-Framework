<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function index()
    {
        // Fetch shifts from DB and pass to view as array with name/start/end keys for compatibility
        $shifts = Shift::orderBy('id')->get()->map(function($s){
            return [
                'id' => $s->id,
                'name' => $s->name,
                'start' => substr($s->start_time,0,5),
                'end' => substr($s->end_time,0,5),
            ];
        })->toArray();
        return view('shifts', compact('shifts'));
    }

    public function store(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'name' => 'required|string|max:150',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Shift::create([
            'name' => $r->name,
            'start_time' => $r->start_time,
            'end_time' => $r->end_time,
        ]);

        return redirect()->route('shifts.index')->with('success','Shift created');
    }

    public function update(Request $r, $id)
    {
        $validator = Validator::make($r->all(), [
            'name' => 'required|string|max:150',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $shift = Shift::findOrFail($id);
        $shift->update([
            'name' => $r->name,
            'start_time' => $r->start_time,
            'end_time' => $r->end_time,
        ]);

        return redirect()->route('shifts.index')->with('success','Shift updated');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        return redirect()->route('shifts.index')->with('success','Shift deleted');
    }
}
