@extends('layouts.app')

@section('title','Interactive Page')

@section('content')
<div class="card border-0 shadow-sm p-3">
    <h4>Interactive Feedback</h4>
    <form method="POST" action="{{ url('/page') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Your Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary">Send</button>
        </div>
    </form>
    @if(session('page_response'))
        <div class="alert alert-info mt-3">{!! session('page_response') !!}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
