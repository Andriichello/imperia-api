@extends('auth.layout')

@section('auth-title')
    {{ __('Welcome Back!') }}
@endsection

@section('auth-fields')
    @include(
        'auth.components.field',
        [
            'label' => __('Email Address'),
            'attribute' => 'email',
            'type' => 'email',
            'required' => true,
            'autofocus' => true,
        ]
    )

    @include(
        'auth.components.field',
        [
            'label' => __('Password'),
            'attribute' => 'password',
            'type' => 'password',
            'required' => true,
        ]
    )

    <div class="flex mb-6 justify-between">
        <label class="flex items-center select-none">
            <input type="checkbox" class="checkbox mr-2" style="margin-right: 0.5rem;"
                   name="remember" value="1">
            Remember me
        </label>

        <div class="ml-auto">
            <a class="text-gray-500 font-bold no-underline" href="/nova/register">
                {{ __('Don\'t have an account?') }}
            </a>
        </div>
    </div>
@endsection

@section('auth-button')
    <span>{{ __('Log In') }}</span>
@endsection
