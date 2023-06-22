@extends('auth.layout')

@section('auth-title')
    {{ __('form.registration') }}
@endsection

@section('auth-fields')
    @include(
        'auth.components.field',
        [
            'label' => __('form.name'),
            'attribute' => 'name',
            'type' => 'text',
            'required' => true,
            'autofocus' => true,
        ]
    )

    @include(
       'auth.components.field',
       [
           'label' => __('form.surname'),
           'attribute' => 'surname',
           'type' => 'text',
           'required' => true,
       ]
   )

    @include(
       'auth.components.field',
       [
           'label' => __('form.email'),
           'attribute' => 'email',
           'type' => 'email',
           'required' => true,
       ]
   )

    @include(
       'auth.components.field',
       [
           'label' => __('form.phone'),
           'attribute' => 'phone',
           'type' => 'tel',
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

    @include(
       'auth.components.field',
       [
           'label' => __('form.password_confirmation'),
           'attribute' => 'password_confirmation',
           'type' => 'password',
           'required' => true,
       ]
   )

    <div class="flex mb-6 justify-end">
        <div class="ml-auto">
            <a class="text-gray-500 font-bold no-underline" href="/nova/login">
                {{ __('form.already_have_account') }}
            </a>
        </div>
    </div>
@endsection

@section('auth-button')
    <span>{{ __('form.register') }}</span>
@endsection
