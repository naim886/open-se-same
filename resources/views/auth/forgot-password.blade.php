@extends('layouts.app')
@section('content')

    @if(session()->has('status'))
        <div class="alert alert-success">
            {{session()->get('status')}}
        </div>
    @endif

    {{html()->form('POST', route('password.email'))->open()}}
    <div class="mb-2 position-relative">
        {{html()->label('Email / Phone', 'email')}}
        <x-required/>
        {{html()->text('email')->placeholder('Enter your email / phone')->class('form-control')}}
        @error('email')
        <p class="validation-message">{{$message}}</p>
        @enderror
    </div>
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="d-grid">
                <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Send Password Reset Link</button>
            </div>
        </div>
    </div>
    {{html()->form()->close()}}
    <div class="mt-2 text-center">
        <p>Remembered password? <a href="{{route('login')}}">Login</a></p>
    </div>
@endsection

@push('scripts')
    <script>
        if ('{{session()->has('status')}}') {
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "{{session()->get('status')}}",
                showConfirmButton: false,
                timer: 3000,
                toast: true
            });
        }
    </script>
@endpush

