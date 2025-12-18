@extends('layouts.app')

@section('title','Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h4">Attendance & Leave</h2>
  <div class="text-end"><small class="text-muted">Device status: <span id="deviceStatus" class="text-success">Connected</span></small></div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm p-3">
      <h6 class="mb-3">Quick Clock</h6>
      <div class="d-grid gap-2">
        <input class="form-control" placeholder="Enter Worker ID" id="clockId">
        <div class="input-group mt-2">
          <input class="form-control" placeholder="Leave start" id="leaveStart" type="date">
          <input class="form-control" placeholder="Leave end" id="leaveEnd" type="date">
        </div>
        <textarea class="form-control mt-2" id="leaveReason" placeholder="Reason for leave (optional)"></textarea>
        <button class="btn btn-outline-warning mt-2" id="requestLeave">Request Leave</button>
        <div class="d-flex gap-2">
          <button class="btn btn-success flex-fill" id="btnIn"><i class="bi bi-arrow-right-circle"></i> Clock In</button>
          <button class="btn btn-danger flex-fill" id="btnOut"><i class="bi bi-arrow-left-circle"></i> Clock Out</button>
        </div>
        <small class="text-muted">Tip: Biometric integration simulated in prototype.</small>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3 p-3">
      <h6 class="mb-2">Pending Leave Requests</h6>
      <ul class="list-group list-group-flush" id="leaveList">
      </ul>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="mb-3">Today's Attendance</h6>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Shift</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
            <tbody id="todayAtt">
                @foreach($attendances as $a)
                <tr>
                  <td>{{ optional($a->worker)->employee_code ?? $a->worker_id }}</td>
                  <td>{{ optional(optional($a->worker)->user)->name ?? '—' }}</td>
                  <td>{{ optional($a->worker)->position ?? '—' }}</td>
                  <td>{{ $a->clock_in ?? '—' }}</td>
                  <td>{{ $a->clock_out ?? '—' }}</td>
                  <td><span class="badge {{ $a->clock_in ? 'bg-success' : 'bg-secondary' }}">{{ $a->clock_in ? 'Present' : 'Absent' }}</span></td>
                </tr>
                @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-outline-primary" id="syncBtn">Sync Devices</button>
          <button class="btn btn-outline-secondary" id="exportAttend">Export</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.Laravel?.csrfToken;
function postAttendance(url, workerId){
  if(!workerId) { alert('Please enter worker id'); return; }
  if(!confirm('Proceed with attendance action for ' + workerId + '?')) return;
  fetch(url.replace('%7Bid%7D', encodeURIComponent(workerId)), {
    method: 'POST',
    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({})
  }).then(r=>{
    if(r.ok) location.reload(); else alert('Failed');
  }).catch(e=>{alert('Error');});
}

document.getElementById('btnIn')?.addEventListener('click', ()=>{
  const id = document.getElementById('clockId').value.trim();
  postAttendance("{{ route('attendance.in', ['id'=>':id']) }}".replace(':id','%7Bid%7D'), id);
});
document.getElementById('btnOut')?.addEventListener('click', ()=>{
  const id = document.getElementById('clockId').value.trim();
  postAttendance("{{ route('attendance.out', ['id'=>':id']) }}".replace(':id','%7Bid%7D'), id);
});

document.getElementById('syncBtn')?.addEventListener('click', ()=>{ alert('Device sync simulated'); });
// leave request
document.getElementById('requestLeave')?.addEventListener('click', async ()=>{
  const worker_id = document.getElementById('clockId').value.trim();
  const start = document.getElementById('leaveStart').value;
  const end = document.getElementById('leaveEnd').value;
  const reason = document.getElementById('leaveReason').value;
  if (!worker_id || !start || !end) return alert('Enter worker id and dates');
  try{
    const res = await fetch('{{ url('api/leaves') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({worker_id,start_date:start,end_date:end,reason})});
    if (!res.ok) { const j = await res.json().catch(()=>null); return alert('Failed: '+(j?.error||res.status)); }
    alert('Leave requested');
    loadLeaves();
  }catch(e){ console.error(e); alert('Error'); }
});

async function loadLeaves(){
  try{
    const res = await fetch('{{ url('api/leaves') }}');
    const data = await res.json();
    const ul = document.getElementById('leaveList'); ul.innerHTML='';
    data.forEach(l=>{
      const li = document.createElement('li'); li.className='list-group-item d-flex justify-content-between';
      li.innerHTML = `<div><strong>${l.employee_code || l.worker_id}</strong> - ${l.reason || ''} <div class="text-muted small">${l.start} → ${l.end}</div></div>`;
      const actions = document.createElement('div');
      if (l.status === 'pending'){
        const a = document.createElement('button'); a.className='btn btn-sm btn-outline-success me-1'; a.innerText='Approve'; a.onclick = async ()=>{ await fetch('{{ url('api/leaves') }}/'+l.id+'/approve',{method:'POST',headers:{'X-CSRF-TOKEN':csrf}}); loadLeaves(); };
        const r = document.createElement('button'); r.className='btn btn-sm btn-outline-danger'; r.innerText='Reject'; r.onclick = async ()=>{ await fetch('{{ url('api/leaves') }}/'+l.id+'/reject',{method:'POST',headers:{'X-CSRF-TOKEN':csrf}}); loadLeaves(); };
        actions.appendChild(a); actions.appendChild(r);
      } else {
        const badge = document.createElement('span'); badge.className = l.status==='approved' ? 'badge bg-success' : 'badge bg-danger'; badge.innerText = l.status; actions.appendChild(badge);
      }
      li.appendChild(actions); ul.appendChild(li);
    });
  }catch(e){ console.error(e); }
}

// export button
document.getElementById('exportAttend')?.addEventListener('click', ()=>{
  window.location = '/attendance/export';
});

// initial load
loadLeaves();
</script>
@endsection
