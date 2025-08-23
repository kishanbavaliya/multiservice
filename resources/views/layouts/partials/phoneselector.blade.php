<div id="utilsScriptUrl" data-value="{{ asset('js/iti/utils.js') }}"></div>
@push('scripts')
    <script src="{{ asset('js/iti/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('js/phone-selector.js') }}"></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/intlTelInput.css') }}">
    <style>
        .intl-tel-input {
            width: 100% !important;
        }

        .iti {
            width: 100% !important;
        }

        .iti.iti--allow-dropdown {
            width: 100%
        }
    </style>
@endpush
