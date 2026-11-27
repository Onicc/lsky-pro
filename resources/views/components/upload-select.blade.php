@props(['name', 'label', 'icon', 'options', 'selected'])

<div class="upload-option-group upload-select" x-data="uploadSelect"
     @click.outside="open = false"
     @focusout="if (!$el.contains($event.relatedTarget)) open = false"
     @keydown.escape="if (open) { $event.stopPropagation(); close() }">
    <span class="upload-option-label" id="upload-{{ $name }}-label">{{ $label }}</span>
    <input type="hidden" x-ref="input" data-upload-{{ $name }} value="{{ $selected }}">
    <button type="button" class="upload-select-trigger" x-ref="trigger" aria-haspopup="listbox"
            aria-labelledby="upload-{{ $name }}-label upload-{{ $name }}-value"
            aria-controls="upload-{{ $name }}-options" :aria-expanded="open.toString()"
            @click="open ? close() : show()" @keydown.arrow-down.prevent="show()" @keydown.arrow-up.prevent="show()">
        <i class="fas {{ $icon }} upload-select-icon" aria-hidden="true"></i>
        <span id="upload-{{ $name }}-value" x-ref="label">{{ $options[$selected] }}</span>
        <i class="fas fa-chevron-down upload-select-chevron" aria-hidden="true"></i>
    </button>
    <div id="upload-{{ $name }}-options" x-ref="list" x-show="open" x-cloak
         class="site-dropdown-panel upload-select-panel" role="listbox" aria-labelledby="upload-{{ $name }}-label"
         @keydown="move($event)">
        @foreach($options as $value => $option)
            <button type="button" class="upload-select-item" role="option" tabindex="-1"
                    data-value="{{ $value }}" title="{{ $option }}" :aria-selected="($el.dataset.value === value).toString()"
                    @click="choose($el)">
                <span>{{ $option }}</span>
                <i class="fas fa-check" aria-hidden="true"></i>
            </button>
        @endforeach
    </div>
</div>
