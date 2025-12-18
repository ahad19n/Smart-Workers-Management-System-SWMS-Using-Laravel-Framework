@extends('layouts.app')

@section('title','Shifts')

@section('content')
<div class="container p-4">
  <h2 class="h4 mb-3">Shifts</h2>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="mb-4">
        <form method="POST" action="{{ route('shifts.store') }}" class="row g-2 align-items-end">
          @csrf
          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" placeholder="Morning" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Start</label>
            <input name="start_time" type="time" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">End</label>
            <input name="end_time" type="time" class="form-control" required>
          </div>
          <div class="col-md-2 text-end">
            <button class="btn btn-primary">Add Shift</button>
          </div>
        </form>
      </div>

      <div class="row">
        @foreach($shifts as $s)
        <div class="col-md-4 mb-3">
          <div class="shift p-3 border rounded">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <strong>{{ $s['name'] }}</strong>
                <div class="text-muted">{{ $s['start'] }} - {{ $s['end'] }}</div>
              </div>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editShift{{ $s['id'] }}">Edit</button>
                <form method="POST" action="{{ route('shifts.destroy', $s['id']) }}" onsubmit="return confirm('Delete shift?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </div>
            </div>

            <div class="collapse mt-3" id="editShift{{ $s['id'] }}">
              <form method="POST" action="{{ route('shifts.update', $s['id']) }}">
                @csrf
                @method('PUT')
                <div class="row g-2">
                  <div class="col-12">
                    <input name="name" class="form-control" value="{{ $s['name'] }}" required>
                  </div>
                  <div class="col-6">
                    <input name="start_time" type="time" class="form-control" value="{{ $s['start'] }}" required>
                  </div>
                  <div class="col-6">
                    <input name="end_time" type="time" class="form-control" value="{{ $s['end'] }}" required>
                  </div>
                  <div class="col-12 text-end">
                    <button class="btn btn-sm btn-primary">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
