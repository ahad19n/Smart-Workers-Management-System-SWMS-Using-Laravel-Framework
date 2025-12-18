@extends('layouts.app')

@section('title','Payroll')

@section('content')
<div class="container p-4">
  <h2 class="h4 mb-3">Payroll</h2>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h5>Payroll Summary</h5>
      <div><strong>Total Payroll:</strong> PKR {{ number_format($total ?? 0) }}</div>
      <div class="mt-3">
        <form method="POST" action="{{ route('payroll.generate') }}">
          @csrf
          <button class="btn btn-sm btn-primary">Generate Salary Slips (PDF/CSV)</button>
        </form>
      </div>
      <hr class="my-4">

      <h5>Create Payroll Record</h5>
      <form method="POST" action="{{ route('payroll.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Worker</label>
          <select name="worker_id" class="form-select" required>
            <option value="">Select worker</option>
            @foreach($workers as $w)
              <option value="{{ $w->id }}">{{ optional($w->user)->name ?? ('#'.$w->id) }} ({{ $w->employee_code }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Month</label>
          <input name="month" class="form-control" placeholder="Dec 2025" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Basic</label>
          <input name="basic_salary" type="number" class="form-control" value="0" required>
        </div>
        <div class="col-md-1">
          <label class="form-label">OT</label>
          <input name="overtime_hours" type="number" class="form-control" value="0">
        </div>
        <div class="col-md-1">
          <label class="form-label">Deductions</label>
          <input name="deductions" type="number" class="form-control" value="0">
        </div>
        <div class="col-md-2">
          <label class="form-label">Net Salary</label>
          <input name="net_salary" type="number" class="form-control" value="0" required>
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary">Save Payroll</button>
        </div>
      </form>

      <hr class="my-4">

      <h5 class="mb-3">Payroll Records</h5>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Worker</th>
              <th>Month</th>
              <th class="text-end">Net Salary</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($payrolls as $p)
              <tr>
                <td>{{ optional(optional($p->worker)->user)->name ?? ('#'.$p->worker_id) }}</td>
                <td>{{ $p->month }}</td>
                <td class="text-end">PKR {{ number_format($p->net_salary) }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editPayroll{{ $p->id }}">Edit</button>
                  <form method="POST" action="{{ route('payroll.destroy', $p->id) }}" style="display:inline" onsubmit="return confirm('Delete payroll entry?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              <tr class="collapse" id="editPayroll{{ $p->id }}">
                <td colspan="4">
                  <form method="POST" action="{{ route('payroll.update', $p->id) }}" class="row g-2 align-items-end">
                    @csrf
                    @method('PUT')
                    <div class="col-md-3">
                      <input name="month" class="form-control" value="{{ $p->month }}" required>
                    </div>
                    <div class="col-md-2">
                      <input name="basic_salary" type="number" class="form-control" value="{{ $p->basic_salary }}" required>
                    </div>
                    <div class="col-md-1">
                      <input name="overtime_hours" type="number" class="form-control" value="{{ $p->overtime_hours }}">
                    </div>
                    <div class="col-md-1">
                      <input name="deductions" type="number" class="form-control" value="{{ $p->deductions }}">
                    </div>
                    <div class="col-md-3">
                      <input name="net_salary" type="number" class="form-control" value="{{ $p->net_salary }}" required>
                    </div>
                    <div class="col-md-2 text-end">
                      <button class="btn btn-sm btn-primary">Save</button>
                    </div>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-muted text-center">No payroll records</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
