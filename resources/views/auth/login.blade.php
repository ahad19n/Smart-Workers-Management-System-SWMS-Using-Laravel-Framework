@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh">
    <div class="card p-4" style="max-width:420px; width:100%">
        <h4 class="mb-3 text-center">Sign In</h4>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required>
            </div>

            <div class="d-grid">
                <button class="btn btn-primary">Login</button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('register') }}">Create account</a>
            </div>
        </form>
    </div>
</div>
@endsection
