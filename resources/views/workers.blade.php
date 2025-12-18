@extends('layouts.app')

@section('title','Workers')

@section('content')
<div class="container p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4">Workers & Profiles</h2>
    <div>
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="bi bi-person-plus"></i> Add Worker</button>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="mb-3 d-flex gap-2">
        <input class="form-control w-50" placeholder="Search by name, ID, skill..." id="searchWorkers">
        <select class="form-select w-auto" id="filterRole">
          <option value="">All Positions</option>
          <option>Operator</option>
          <option>Supervisor</option>
          <option>Maintenance</option>
        </select>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr><th>ID</th><th>Name</th><th>Position</th><th>Join Date</th><th>Skills</th><th>Actions</th></tr>
          </thead>
          <tbody id="workersTbody">
            @foreach($workers as $w)
            <tr>
              <td>{{ $w->id }}</td>
              <td>{{ optional($w->user)->name ?? $w->employee_code }}</td>
              <td>{{ $w->position }}</td>
              <td>{{ optional($w->join_date)?->format('Y-m-d') }}</td>
              <td>{{ \Illuminate\Support\Str::limit($w->skills,60) }}</td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary me-1 viewBtn" data-id="{{ $w->id }}">View</button>
                <button type="button" class="btn btn-sm btn-outline-danger deactivateBtn" data-id="{{ $w->id }}">Deactivate</button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<!-- Register Worker Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Worker</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="regForm" method="POST" action="{{ route('workers.store') }}">
      @csrf
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input name="name" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Employee Code</label>
            <input name="employee_code" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Position</label>
            <input name="position" class="form-control" required />
          </div>
          <div class="col-md-6">
            <label class="form-label">Join Date</label>
            <input name="join_date" type="date" class="form-control" />
          </div>
          <div class="col-12">
            <label class="form-label">Skills</label>
            <textarea name="skills" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Worker</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
// inline search fallback (kept for progressive enhancement)
document.getElementById('searchWorkers')?.addEventListener('input', (e)=>{
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#workersTbody tr').forEach(row=>{
    row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
@endsection
