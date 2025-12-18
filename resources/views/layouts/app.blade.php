<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>SWMS — @yield('title')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg custom-navbar shadow-sm align-items-center">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center text-white" href="{{ route('dashboard') }}">
        <i class="bi bi-people-fill me-2" style="font-size:1.2rem"></i>
        <span style="font-weight:700;letter-spacing:0.6px;">SWMS</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link @if(request()->routeIs('dashboard')) active @endif text-light" href="{{ route('dashboard') }}">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link @if(request()->routeIs('workers.*')) active @endif text-light" href="{{ route('workers.index') }}">Workers</a></li>
          <li class="nav-item"><a class="nav-link @if(request()->routeIs('attendance.*')) active @endif text-light" href="{{ route('attendance.index') }}">Attendance</a></li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <a href="#" class="btn btn-contact btn-sm">Contact</a>
          <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </nav>

  @if(request()->routeIs('dashboard'))
    <main class="p-0">
  @else
    <main class="container-fluid p-4">
  @endif
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @yield('content')
    <footer class="text-center text-muted mt-4">
      <small>© 2025 SWMS — Smart Workers Management System</small>
    </footer>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>window.Laravel = {csrfToken: '{{ csrf_token() }}'};</script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
