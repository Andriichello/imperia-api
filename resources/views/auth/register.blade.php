@extends('auth.layout')

@section('auth-title')
    {{ __('Create New Account') }}
@endsection

@section('auth-fields')
    @include(
        'auth.components.field',
        [
            'label' => __('Name'),
            'attribute' => 'name',
            'type' => 'text',
            'required' => true,
            'autofocus' => true,
        ]
    )

    @include(
       'auth.components.field',
       [
           'label' => __('Surname'),
           'attribute' => 'surname',
           'type' => 'text',
           'required' => true,
       ]
   )

    @include(
       'auth.components.field',
       [
           'label' => __('Email Address'),
           'attribute' => 'email',
           'type' => 'email',
           'required' => true,
       ]
   )

    @include(
       'auth.components.field',
       [
           'label' => __('Phone'),
           'attribute' => 'phone',
           'type' => 'tel',
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

    @include(
       'auth.components.field',
       [
           'label' => __('Confirm Password'),
           'attribute' => 'password_confirmation',
           'type' => 'password',
           'required' => true,
       ]
   )

    <div class="flex mb-6 justify-end">
        <div class="ml-auto">
            <a class="text-gray-500 font-bold no-underline" href="/nova/login">
                {{ __('Already have an account?') }}
            </a>
        </div>
    </div>
@endsection

@section('auth-button')
    <span>{{ __('Register') }}</span>
@endsection
