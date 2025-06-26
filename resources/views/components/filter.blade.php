@props([
    'label',
    'options' => [],
    'name' => null,
])

<div class="relative">
    <select
        name="{{ $name }}"
        class="appearance-none border rounded-lg px-4 py-2 pr-8"
    >
        <option value="">{{ $label }}</option>
        @foreach ($options as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    </select>
    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
        &#9662;
    </div>
</div>
