@extends('auth.layout')

@section('auth-title')
    {{ __('form.welcome_back') }}
@endsection

@section('auth-fields')
    @include(
        'auth.components.field',
        [
            'label' => __('form.email'),
            'attribute' => 'email',
            'type' => 'email',
            'required' => true,
            'autofocus' => true,
        ]
    )

    @include(
        'auth.components.field',
        [
            'label' => __('form.password'),
            'attribute' => 'password',
            'type' => 'password',
            'required' => true,
        ]
    )

    <div class="flex mb-6 justify-between">
        <label class="flex items-center select-none">
            <input type="checkbox" class="checkbox mr-2" style="margin-right: 0.5rem;"
                   name="remember" value="1">
            {{ __('form.remember_me') }}
        </label>

        <div class="ml-auto">
            <a class="text-gray-500 font-bold no-underline" href="/nova/register">
                {{ __('form.no_account_yet') }}
            </a>
        </div>
    </div>
@endsection

@section('auth-button')
    <span>{{ __('form.log_in') }}</span>
@endsection
