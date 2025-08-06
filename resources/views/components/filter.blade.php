@props([
    'name',
    'label',
    'options' => [],
    'value' => request($name),
    'placeholder' => 'ทั้งหมด',
])

<div class="flex flex-col relative group w-48">
    <label for="{{ $name }}" class="text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    
    <div class="relative">
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            onchange="this.form.submit()" 
            class="appearance-none border border-gray-300 rounded-md px-4 py-2 pr-10 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 w-full transition"
        >
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $key => $option)
                <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>

        <!-- Chevron Icon -->
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg 
                class="h-4 w-4 text-gray-500 transition-transform duration-300 transform group-focus-within:rotate-180"
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</div>
