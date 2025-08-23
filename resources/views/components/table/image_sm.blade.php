<div>
    <a href="{{ $photo ?? ($model->photo ?? $column->attribute) }}" target="_blank"> <img
            src="{{ $photo ?? ($model->photo ?? $column->attribute) }}"
            class="{{ $position ?? 'object-cover' }} w-12 h-12 overflow-hidden bg-white rounded" />
    </a>
</div>
