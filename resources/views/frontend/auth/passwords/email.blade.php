@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                
                    {!! Form::open(['route' => 'password.email', 'method' => 'post']) !!}
                        <div class="account__form">

                            <div class="input__box">
                                {!! Form::label('email', 'Email *') !!}
                                {!! Form::text('email', old('email')) !!}
                                @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Send Password Reset Link') }}
                                    </button>
                                </div>
                            </div>
                
                            <a class="forget_pass" href="{{ route('frontend.show_login_form') }}">Login?</a>

                        </div>
                    {!! Form::close() !!}

                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
