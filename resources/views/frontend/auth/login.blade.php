@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Login</div>

                <div class="card-body">
                {!! Form::open(['route' => 'frontend.login', 'method' => 'post']) !!}
                    <div class="account__form">

                        <div class="input__box">
                            {!! Form::label('username', 'Username *') !!}
                            {!! Form::text('username', old('username')) !!}
                            @error('username')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="input__box">
                            {!! Form::label('password', 'Password *') !!}
                            {!! Form::password('password') !!}
                            @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="form__btn">
                            {!! Form::button('Login', ['type' => 'submit']) !!}
                            <label class="label-for-checkbox">
                                <input class="input-checkbox" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <span>Remember me</span>
                            </label> 
                        </div>

                        <a class="forget_pass" href="{{ route('password.request') }}">Lost your password?</a>

                    </div>
                {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
