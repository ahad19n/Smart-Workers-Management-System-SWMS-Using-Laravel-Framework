function initDashboard(){
  // destroy previous charts if present
  if (window._attendanceChart && typeof window._attendanceChart.destroy === 'function') {
    try{ window._attendanceChart.destroy(); }catch(e){}
    window._attendanceChart = null;
  }

  const ctxA = document.getElementById('attendanceChart');
  if (ctxA) {
    const attendanceChart = new Chart(ctxA, {
      type: 'line',
      data: {
        labels: Array.from({length: (attendanceData||[]).length}, (_,i)=>`Day ${i+1}`),
        datasets: [{
          label: 'Present',
          data: attendanceData && attendanceData.length ? attendanceData.map(d=>d.count) : Array.from({length:30}, ()=>Math.floor(800 + Math.random()*200)),
          fill: true,
          tension: 0.3,
          backgroundColor: 'rgba(13,110,253,0.08)',
          borderColor: 'rgba(13,110,253,0.9)',
          pointRadius: 2
        }]
      },
      options: {responsive:true, plugins:{legend:{display:false}}, scales: { y: { beginAtZero:false }}}
    });
    window._attendanceChart = attendanceChart;
  }

  const ctxP = document.getElementById('payrollDonut');
  if (ctxP) {
    const payrollDonut = new Chart(ctxP, {
      type: 'doughnut',
      data: { labels: ['Salaries','Overtime','Deductions'], datasets: [{ data: [72,18,10], hoverOffset:4 }] },
      options:{plugins:{legend:{position:'bottom'}}}
    });
    window._payrollDonut = payrollDonut;
  }

  document.getElementById('refreshBtn')?.addEventListener('click', (e)=>{
    const btn = e.currentTarget;
    // show spinner state
    try{ btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...'; }catch(e){}

    // Force a full navigation to the current path with a cache-busting query param so the server provides fresh data
    const path = window.location.pathname;
    const query = window.location.search ? window.location.search + '&' : '?';
    const url = path + query + '_=' + Date.now();
    window.location.href = url;
  });

  document.getElementById('attendanceRange')?.addEventListener('change', (e)=>{
    const days = Number(e.target.value);
    if (window._attendanceChart) {
      window._attendanceChart.data.labels = Array.from({length:days}, (_,i)=>`Day ${i+1}`);
      window._attendanceChart.data.datasets[0].data = Array.from({length:days}, ()=>Math.floor(800 + Math.random()*200));
      window._attendanceChart.update();
    }
  });
}

document.addEventListener('DOMContentLoaded', initDashboard);
window.addEventListener('pjax:loaded', ()=>{ setTimeout(initDashboard, 40); });
