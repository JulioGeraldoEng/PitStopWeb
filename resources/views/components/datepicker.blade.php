@props(['id', 'label', 'icon' => 'fa-calendar-alt', 'placeholder' => 'dd/mm/aaaa'])

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    <label for="{{ $id }}" class="form-label">
        <i class="fas {{ $icon }}"></i> {{ $label }}
    </label>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
        <input type="text" 
               class="form-control datepicker-field" 
               id="{{ $id }}" 
               name="{{ $id }}"
               placeholder="{{ $placeholder }}" 
               maxlength="10"
               value="{{ old($id) }}">
    </div>
</div>