<div class="mb-6">
    <label class="block mb-2" for="{{ $attribute }}">{{ $label }}</label>
    <input class="form-control form-input form-input-bordered w-full {{ $errors->has($attribute) ? 'form-input-border-error' : '' }}"
           id="{{ $attribute }}" type="{{ $type }}" name="{{ $attribute }}" value="{{ old($attribute, $value ?? '') }}"
           {{ ($autofocus ?? false) ? 'autofocus' : '' }}
           {{ ($required ?? false) ? 'required' : '' }}/>
    @if ($errors->has($attribute))
        <p class="help-text mt-2 text-red-500">
            {{ $errors->first($attribute) }}
        </p>
    @endif
</div>
