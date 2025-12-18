<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Worker;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    public function index()
    {
        $workers = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('workers')) {
            $workers = Worker::with('user')->get();
        }

        $payrolls = [];
        $total = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('payroll')) {
            $payrolls = Payroll::with('worker.user')->orderByDesc('created_at')->get();
            $total = Payroll::sum('net_salary');
        }

        return view('payroll', compact('workers','payrolls','total'));
    }

    public function generate(Request $request)
    {
        // gather payrolls for PDF
        $payrolls = [];
        $total = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('payroll')) {
            $payrolls = Payroll::with('worker.user')->orderByDesc('created_at')->get();
            $total = Payroll::sum('net_salary');
        }

        // If Barryvdh DomPDF is installed, render PDF
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) || class_exists(\Dompdf\Dompdf::class)) {
            try {
                if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll_pdf', compact('payrolls','total'));
                    return $pdf->download('salary_slips_'.date('Ymd').'.pdf');
                }
            } catch (\Exception $e) {
                // fallback to CSV below
            }
        }

        // Fallback: stream CSV of payrolls
        $filename = 'salary_slips_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $callback = function() use ($payrolls) {
            $handle = fopen('php://output','w');
            fputcsv($handle, ['Worker','Month','Basic Salary','Overtime Hours','Deductions','Net Salary']);
            foreach ($payrolls as $p) {
                fputcsv($handle, [ optional(optional($p->worker)->user)->name ?? ('#'.$p->worker_id), $p->month, $p->basic_salary, $p->overtime_hours, $p->deductions, $p->net_salary ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'worker_id' => 'required|exists:workers,id',
            'month' => 'required|string|max:50',
            'basic_salary' => 'required|integer|min:0',
            'overtime_hours' => 'nullable|integer|min:0',
            'deductions' => 'nullable|integer|min:0',
            'net_salary' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Payroll::create([
            'worker_id' => $r->worker_id,
            'month' => $r->month,
            'basic_salary' => $r->basic_salary,
            'overtime_hours' => $r->overtime_hours ?? 0,
            'deductions' => $r->deductions ?? 0,
            'net_salary' => $r->net_salary,
        ]);

        return redirect()->route('payroll.index')->with('success','Payroll record added');
    }

    public function update(Request $r, $id)
    {
        $validator = Validator::make($r->all(), [
            'month' => 'required|string|max:50',
            'basic_salary' => 'required|integer|min:0',
            'overtime_hours' => 'nullable|integer|min:0',
            'deductions' => 'nullable|integer|min:0',
            'net_salary' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $pay = Payroll::findOrFail($id);
        $pay->update([
            'month' => $r->month,
            'basic_salary' => $r->basic_salary,
            'overtime_hours' => $r->overtime_hours ?? 0,
            'deductions' => $r->deductions ?? 0,
            'net_salary' => $r->net_salary,
        ]);

        return redirect()->route('payroll.index')->with('success','Payroll updated');
    }

    public function destroy($id)
    {
        $p = Payroll::findOrFail($id);
        $p->delete();
        return redirect()->route('payroll.index')->with('success','Payroll deleted');
    }
}
