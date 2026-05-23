<form method="GET" action="{{ url()->current() }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
    <x-text-input
        name="search"
        type="search"
        class="w-full sm:w-72"
        :value="$search"
        :placeholder="$placeholder"
    />
    <div class="flex gap-2">
        <button class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">
            {{ __('Search') }}
        </button>
        @if ($search !== '')
            <a href="{{ url()->current() }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Clear') }}
            </a>
        @endif
    </div>
</form>
