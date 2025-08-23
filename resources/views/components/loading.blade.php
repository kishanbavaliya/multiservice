{{-- loading --}}
<div wire:loading class="fixed top-0 bottom-0 left-0 z-50 w-full h-full ">
    <div class="fixed top-0 bottom-0 left-0 w-full h-full bg-black opacity-75"></div>
    <div class="fixed top-0 bottom-0 left-0 flex items-center justify-center w-full h-full">
        {{-- <img src="{{ asset('images/loading.svg') }}" class="" /> --}}
        <svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <style>
                @keyframes bounce {

                    0%,
                    100% {
                        transform: translateY(0);
                    }

                    50% {
                        transform: translateY(-10px);
                    }
                }
            </style>

            <g fill="white">
                <circle cx="50" cy="100" r="10" style="animation: bounce 1s infinite ease-in-out;" />
                <circle cx="90" cy="100" r="10" style="animation: bounce 1s infinite ease-in-out 0.2s;" />
                <circle cx="130" cy="100" r="10" style="animation: bounce 1s infinite ease-in-out 0.4s;" />
                <circle cx="170" cy="100" r="10" style="animation: bounce 1s infinite ease-in-out 0.6s;" />
            </g>
        </svg>



    </div>
</div>
