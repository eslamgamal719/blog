@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Reset Password</div>

                <div class="card-body">
                {!! Form::open(['route' => 'password.update', 'method' => 'post']) !!}

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="account__form">
                   
                    <div class="input__box">
                        {!! Form::label('email', 'Email *') !!}
                        {!! Form::email('email', old('email')) !!}
                        @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                  
                    <div class="input__box">
                        {!! Form::label('password', 'Password *') !!}
                        {!! Form::password('password') !!}
                        @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="input__box">
                        {!! Form::label('password_confirmation', 'Re-Password *') !!}
                        {!! Form::password('password_confirmation') !!}
                        @error('password_confirmation')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>


                    <div class="form__btn">
                        {!! Form::button('Reset Password', ['type' => 'submit']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
