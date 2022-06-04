@extends('nova::auth.layout')

@section('content')

    @include('nova::auth.partials.header')

    <form
        class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
        method="POST"
        action="{{ route('nova.register') }}"
    >
        @csrf

        @component('nova::auth.partials.heading')
            {{ __('form.registration') }}
        @endcomponent

        @if ($errors->any())
            <p class="text-center font-semibold text-danger my-3">
                @if ($errors->has('name'))
                    {{ $errors->first('name') }}
                @elseif ($errors->has('surname'))
                    {{ $errors->first('surname') }}
                @elseif ($errors->has('email'))
                    {{ $errors->first('email') }}
                @elseif ($errors->has('phone'))
                    {{ $errors->first('phone') }}
                @elseif ($errors->has('password'))
                    {{ $errors->first('password') }}
                @elseif ($errors->has('password_confirmation'))
                    {{ $errors->first('password_confirmation') }}
                @endif
            </p>
        @endif

        <div class="mb-6">
            <label class="block font-bold mb-2" for="name">{{ __('form.name') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('name') is-invalid @enderror" id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="surname">{{ __('form.surname') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('surname') is-invalid @enderror" id="surname" type="text" name="surname" value="{{ old('surname') }}" required autocomplete="surname" autofocus>
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="email">{{ __('form.email') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="phone">{{ __('form.phone') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('phone') is-invalid @enderror" id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="password">{{ __('form.password') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('password') is-invalid @enderror" id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="mb-6">
            <label class="block font-bold mb-2" for="password-confirm">{{ __('form.password_confirmation') }}</label>
            <input class="form-control form-input form-input-bordered w-full @error('password') is-invalid @enderror" id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <div class="flex mb-6">
            <div class="ml-auto">
                <a class="text-primary dim font-bold no-underline" href="{{ route('nova.login') }}">
                    {{ __('Already have an account?') }}
                </a>
            </div>
        </div>

        <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
            {{ __('form.register') }}
        </button>
    </form>
@endsection
