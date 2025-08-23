@props(['label' => 'Label', 'value' => 'Value'])

<div class="mb-4 w-full">
    @php
        $inputId = 'copy-input-' . \Str::random(8);
    @endphp
    <label class="block mb-1 text-sm font-medium text-gray-700">
        {{ $label }}
    </label>

    <div class="relative">
        <input type="text" id="{{ $inputId }}" value="{{ $value }}"
            class="w-full px-4 py-2 pr-10 border rounded bg-gray-100 text-gray-700 cursor-not-allowed" disabled
            readonly />

        <button type="button" onclick="copyToClipboard('{{ $value }}')"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700" title="Copy">
            📋
        </button>
    </div>
</div>


@pushOnce('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ __('Copied to clipboard') }}",
                    showConfirmButton: false,
                    timer: 1500
                });
            }).catch(() => {
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "{{ __('Failed to copy') }}",
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    </script>
@endPushOnce
