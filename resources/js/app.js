import './bootstrap';

// app.js - enhanced interactivity for SWMS (Blade scaffold)
// Helper: show bootstrap toast
function showToast(message, title='Notice', delay=3000){
  const id = 'toast-'+Date.now();
  const container = document.getElementById('toastContainer') || (function(){
    const div = document.createElement('div'); div.id='toastContainer';
    div.style.position='fixed'; div.style.right='20px'; div.style.top='20px'; div.style.zIndex=1080;
    document.body.appendChild(div); return div;
  })();
  const html = `<div id="${id}" class="toast align-items-center text-bg-primary border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body"><strong>${title}:</strong> ${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div></div>`;
  container.insertAdjacentHTML('beforeend', html);
  const t = new bootstrap.Toast(document.getElementById(id), {delay});
  t.show();
}

// fetch wrapper handling CSRF token
async function apiFetch(url, opts={}){
  opts.headers = opts.headers || {};
  if (!opts.headers['X-Requested-With']) opts.headers['X-Requested-With']='XMLHttpRequest';
  if (!opts.headers['Content-Type'] && !(opts.body instanceof FormData)) opts.headers['Content-Type']='application/json';
  if (!opts.body || opts.body instanceof FormData) {
    // leave body as-is
  } else {
    opts.body = JSON.stringify(opts.body);
  }
  const res = await fetch(url, opts);
  if (!res.ok) throw new Error('Network error '+res.status);
  return res.json();
}

// Debounce helper
function debounce(fn, wait=300){ let t; return (...args)=>{ clearTimeout(t); t = setTimeout(()=>fn(...args), wait); }; }

// Load workers into table dynamically
async function loadWorkers(){
  try{
    const data = await apiFetch('/api/workers');
    const tbody = document.getElementById('workersTbody');
    if(!tbody) return;
    tbody.innerHTML = data.map(w=>`<tr>
      <td>${w.id}</td><td>${w.name}</td><td>${w.position}</td><td>${w.join}</td><td>${w.skills}</td>
      <td>
        <button class="btn btn-sm btn-outline-primary me-1 viewBtn" data-id="${w.id}">View</button>
        <button class="btn btn-sm btn-outline-danger deactivateBtn" data-id="${w.id}">Deactivate</button>
      </td></tr>`).join('');
    // attach view handlers
    document.querySelectorAll('.viewBtn').forEach(b=>b.addEventListener('click', (e)=>{
      const id = e.currentTarget.dataset.id;
      const w = data.find(x=> String(x.id) === String(id));
      if (w) showWorkerModal(w);
    }));
    // attach deactivate handlers
    document.querySelectorAll('.deactivateBtn').forEach(b=>b.addEventListener('click', async (e)=>{
      const id = e.currentTarget.dataset.id;
      if (!confirm('Deactivate worker #' + id + '?')) return;
      try{
        await apiFetch('/api/workers/'+id+'/deactivate',{method:'POST'});
        showToast('Worker deactivated','Success');
        loadWorkers();
      }catch(err){ console.error(err); showToast('Failed to deactivate','Error'); }
    }));
  }catch(err){ console.error(err); showToast('Failed to load workers', 'Error'); }
}

// show worker modal
function showWorkerModal(w){
  const html = `<div class="modal fade" id="workerModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Worker: ${w.name} (${w.id})</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><dl class="row"><dt class="col-sm-3">Position</dt><dd class="col-sm-9">${w.position}</dd>
      <dt class="col-sm-3">Join Date</dt><dd class="col-sm-9">${w.join}</dd>
      <dt class="col-sm-3">Skills</dt><dd class="col-sm-9">${w.skills}</dd></dl></div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>`;
  // remove old
  document.getElementById('workerModal')?.remove();
  document.body.insertAdjacentHTML('beforeend', html);
  const modal = new bootstrap.Modal(document.getElementById('workerModal'));
  modal.show();
}

// Submit register form via AJAX
function initPage(){
  // enhance register worker modal form (if present)
  const regForm = document.getElementById('regForm');
  if (regForm){
    // remove existing handlers to avoid duplicates
    regForm.replaceWith(regForm.cloneNode(true));
    const newForm = document.getElementById('regForm');
    newForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const form = e.currentTarget;
      const fd = new FormData(form);
      const payload = Object.fromEntries(fd.entries());
      try{
        const res = await apiFetch('/api/workers', {method:'POST', body: payload});
        if (res.ok) {
          showToast('Worker registered successfully', 'Success');
          bootstrap.Modal.getInstance(document.getElementById('registerModal'))?.hide();
          loadWorkers();
        }
      }catch(err){ showToast('Failed to register worker', 'Error'); console.error(err); }
    });
  }

  // quick clock buttons on attendance page
  const btnIn = document.getElementById('btnIn');
  const btnOut = document.getElementById('btnOut');
  if (btnIn && btnOut){
    btnIn.replaceWith(btnIn.cloneNode(true));
    btnOut.replaceWith(btnOut.cloneNode(true));
    const nBtnIn = document.getElementById('btnIn');
    const nBtnOut = document.getElementById('btnOut');
    nBtnIn.addEventListener('click', async ()=>{
      const id = document.getElementById('clockId').value.trim();
      if (!id) return showToast('Enter Worker ID', 'Warning');
      try{ const res = await apiFetch('/api/attendance/clock', {method:'POST', body:{id, type:'in'}}); showToast(`Clocked IN ${res.id} at ${res.time}`, 'Attendance'); }catch(e){ showToast('Clock failed','Error'); }
    });
    nBtnOut.addEventListener('click', async ()=>{
      const id = document.getElementById('clockId').value.trim();
      if (!id) return showToast('Enter Worker ID', 'Warning');
      try{ const res = await apiFetch('/api/attendance/clock', {method:'POST', body:{id, type:'out'}}); showToast(`Clocked OUT ${res.id} at ${res.time}`, 'Attendance'); }catch(e){ showToast('Clock failed','Error'); }
    });
  }

  // load workers dynamically on workers page
  if (document.getElementById('workersTbody')) loadWorkers();

  // search input debounce
  const search = document.getElementById('searchWorkers');
  if (search){
    search.removeEventListener && search.removeEventListener('input', ()=>{});
    search.addEventListener('input', debounce(async (e)=>{
      const q = e.target.value.trim().toLowerCase();
      const rows = document.querySelectorAll('#workersTbody tr');
      rows.forEach(r=> r.style.display = r.innerText.toLowerCase().includes(q) ? '' : 'none');
    }, 250));
  }

  // sync devices button on attendance page: simulate fetching latest attendance
  const syncBtn = document.getElementById('syncBtn');
  if (syncBtn){
    syncBtn.replaceWith(syncBtn.cloneNode(true));
    const nSync = document.getElementById('syncBtn');
    nSync.addEventListener('click', async ()=>{
      try{
        const data = await apiFetch('/api/attendance/today');
        const tbody = document.getElementById('todayAtt');
        if(tbody) tbody.innerHTML = data.map(t=>`<tr><td>${t.id}</td><td>${t.name}</td><td>${t.shift}</td><td>${t.in}</td><td>${t.out}</td><td><span class="badge ${t.status==='Present'?'bg-success':'bg-danger'}">${t.status}</span></td></tr>`).join('');
        showToast('Device sync complete', 'Sync');
      }catch(e){ showToast('Sync failed', 'Error'); }
    });
  }

  // attach nav button click handlers to ensure PJAX navigation works reliably
  const navBtns = document.querySelectorAll('.nav-btn');
  if (navBtns.length) {
    navBtns.forEach(btn => {
      const newBtn = btn.cloneNode(true);
      btn.replaceWith(newBtn);
      newBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const href = newBtn.dataset.href;
        if (!href) return;
        if (window.SWMS && typeof window.SWMS.navigateTo === 'function') {
          window.SWMS.navigateTo(href, true);
        } else {
          location.href = href;
        }
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', initPage);
window.addEventListener('pjax:loaded', initPage);

// PJAX-like navigation: fetch page fragment and replace main content with animations
(()=>{
  const contentSelector = 'main.container-fluid.p-4';
  async function fetchPage(url){
    const res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
    if(!res.ok) throw new Error('Fetch failed');
    return res.text();
  }

  function parseHTML(html){
    const doc = new DOMParser().parseFromString(html,'text/html');
    return doc.querySelector(contentSelector) || doc.querySelector('main') || document.createElement('div');
  }

  async function navigateTo(url, addToHistory = true){
    try{
      const old = document.querySelector(contentSelector);
      if(!old) { location.href = url; return; }
      old.classList.add('pjax-fade-exit');
      old.classList.add('pjax-fade-exit-active');
      const html = await fetchPage(url);
      const newContent = parseHTML(html);
      newContent.classList.add('pjax-fade-enter');
      // insert new content after old to measure
      old.parentNode.insertBefore(newContent, old.nextSibling);
      // small delay to allow CSS transitions
      requestAnimationFrame(()=>{
        newContent.classList.add('pjax-fade-enter-active');
        old.addEventListener('transitionend', ()=> old.remove(), {once:true});
      });
      // update document title
      const tmpDoc = new DOMParser().parseFromString(html,'text/html');
      const newTitle = tmpDoc.querySelector('title')?.innerText;
      if(newTitle) document.title = newTitle;
      if(addToHistory) history.pushState({url}, newTitle || '', url);
      // re-run scripts: simple approach, dispatch event for page-ready
      window.dispatchEvent(new CustomEvent('pjax:loaded', {detail:{url}}));
      // update active nav buttons
      document.querySelectorAll('.nav-btn').forEach(b=> b.classList.toggle('active', new URL(b.dataset.href, location).pathname === new URL(url, location).pathname));
    }catch(err){ console.error(err); location.href = url; }
  }

  // attach to nav buttons
  // expose navigateTo globally for direct handlers
  window.SWMS = window.SWMS || {};
  window.SWMS.navigateTo = navigateTo;

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest && e.target.closest('.nav-btn');
    if(!btn) return;
    e.preventDefault();
    const href = btn.dataset.href;
    if(!href) return;
    navigateTo(href, true);
  });

  // handle browser back/forward
  window.addEventListener('popstate', (e)=>{
    const url = (e.state && e.state.url) || location.href;
    navigateTo(url, false);
  });

})();

