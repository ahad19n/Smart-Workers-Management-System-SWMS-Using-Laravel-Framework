@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh">
    <div class="card p-4" style="max-width:420px; width:100%">
        <h4 class="mb-3 text-center">Create Account</h4>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid">
                <button class="btn btn-success">Register</button>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}">Already have an account?</a>
            </div>
        </form>
    </div>
</div>
@endsection
