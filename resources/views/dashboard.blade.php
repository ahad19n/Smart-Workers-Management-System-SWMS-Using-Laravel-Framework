@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="dashboard-bg">
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0">Dashboard</h1>
      <div>
        <button class="btn btn-outline-secondary me-2" id="refreshBtn"><i class="bi bi-arrow-repeat"></i> Refresh</button>
        <div class="btn-group">
          <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#quickAddModal"><i class="bi bi-plus-lg"></i> Quick Add</button>
        </div>
      </div>
    </div>
    

<div class="row g-4 mb-4">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card h-100 border-0 shadow-sm">
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted">Total Workers</small>
            <h4 id="kpiWorkers" class="fw-bold">{{ $kpis['totalWorkers'] }}</h4>
          </div>
          <span class="kpi-badge bg-primary-subtle text-primary">
            <i class="bi bi-people"></i>
          </span>
        </div>
        <small class="text-success mt-2">
          <i class="bi bi-arrow-up-right"></i> 3.4% since last week
        </small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card h-100 border-0 shadow-sm">
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted">Present Today</small>
            <h4 id="kpiPresent" class="fw-bold">{{ $kpis['presentToday'] }}</h4>
          </div>
          <span class="kpi-badge bg-success-subtle text-success">
            <i class="bi bi-check2-square"></i>
          </span>
        </div>
        <small class="text-muted mt-2">Out of scheduled shift</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card h-100 border-0 shadow-sm">
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted">Pending Leave</small>
            <h4 id="kpiLeave" class="fw-bold">{{ $kpis['pendingLeave'] }}</h4>
          </div>
          <span class="kpi-badge bg-warning-subtle text-warning">
            <i class="bi bi-calendar-x"></i>
          </span>
        </div>
        <small class="text-warning mt-2">2 require manager action</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card kpi-card h-100 border-0 shadow-sm">
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <small class="text-muted">Overtime Hours (Mo.)</small>
            <h4 id="kpiOT" class="fw-bold">{{ $kpis['overtimeHours'] }}</h4>
          </div>
          <span class="kpi-badge bg-info-subtle text-info">
            <i class="bi bi-clock-history"></i>
          </span>
        </div>
        <small class="text-muted mt-2">Auto approvals pending</small>
      </div>
    </div>
  </div>
</div>


<div class="row g-4 mb-4">
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Attendance Trend</h5>
          <select id="attendanceRange" class="form-select form-select-sm w-auto">
            <option value="30" selected>Last 30 days</option>
            <option value="7">Last 7 days</option>
            <option value="90">Last 90 days</option>
          </select>
        </div>
        <canvas id="attendanceChart" height="180"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex flex-column justify-content-center">
        <h5 class="mb-3 text-center">Monthly Payroll</h5>
        <canvas id="payrollDonut" height="160"></canvas>
        <div class="text-center mt-3">
          <strong>Total Payroll</strong>
          <h4 class="mb-0">PKR {{ number_format($totalPayroll ?? 0) }}</h4>
          <small class="text-muted">Includes overtime & deductions</small>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="row g-4">
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-3">Shifts</h5>
          <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-outline-info">Configure Shifts</a>
        </div>
        @forelse($shifts as $s)
          <div class="shift-card mb-2 p-3 rounded">
            <strong>{{ $s->name }}</strong>
            <div class="text-muted">{{ $s->start }} - {{ $s->end }}</div>
          </div>
        @empty
          <div class="text-muted">No shifts configured
            <br>
            
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Recent Payroll</h5>
          <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-info">Configure payroll</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Worker</th>
                <th>Month</th>
                <th class="text-end">Net Salary</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPayrolls as $p)
                <tr>
                  <td>{{ $p->worker_id }}</td>
                  <td>{{ $p->month }}</td>
                  <td class="text-end">PKR {{ number_format($p->net_salary) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-muted text-center">No payroll records</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // attendance data from server side for Chart.js
  const attendanceData = @json($attendance ?? []);
</script>
@endsection

<!-- Quick Add Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-labelledby="quickAddModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="quickAddModalLabel">Quick Add</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-pills mb-3" id="quickAddTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="worker-tab" data-bs-toggle="pill" data-bs-target="#worker" type="button" role="tab">Worker</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="attendance-tab" data-bs-toggle="pill" data-bs-target="#attendance" type="button" role="tab">Attendance</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="payroll-tab" data-bs-toggle="pill" data-bs-target="#payroll" type="button" role="tab">Payroll</button>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="worker" role="tabpanel">
            <form method="POST" action="{{ route('workers.store') }}">
              @csrf
              <div class="row g-2">
                <div class="col-12 col-md-6">
                  <label class="form-label">Name</label>
                  <input name="name" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Email</label>
                  <input name="email" type="email" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Password</label>
                  <input name="password" type="password" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Employee Code</label>
                  <input name="employee_code" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Position</label>
                  <input name="position" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Join Date</label>
                  <input name="join_date" type="date" class="form-control">
                </div>
                <div class="col-12">
                  <label class="form-label">Skills (optional)</label>
                  <input name="skills" class="form-control">
                </div>
              </div>
              <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Worker</button>
              </div>
            </form>
          </div>

          <div class="tab-pane fade" id="attendance" role="tabpanel">
            <p>Quick attendance actions are available on the Attendance page.</p>
            <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-primary">Open Attendance</a>
          </div>

          <div class="tab-pane fade" id="payroll" role="tabpanel">
            <p>Run or configure payroll on the Payroll page.</p>
            <a href="{{ route('payroll.index') }}" class="btn btn-sm btn-outline-primary">Open Payroll</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
