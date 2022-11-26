<div class="px-5 py-3">
    @include('eden::fields.label')
    <div x-data="{
                model: @entangle('fields.' . $key).defer
            }"
        x-init="
             $nextTick(function () {
                let picker = Pickr.create({
                    el: '#{{ $uid }}',
                    theme: 'nano',
                    default: '{{ $value }}',
                    disabled: @json((isset($meta['readonly']) || isset($meta['disabled']))),
                    swatches: [
                        'rgba(244, 67, 54, 1)',
                        'rgba(233, 30, 99, 1)',
                        'rgba(156, 39, 176, 1)',
                        'rgba(103, 58, 183, 1)',
                        'rgba(63, 81, 181, 1)',
                        'rgba(33, 150, 243, 1)',
                        'rgba(3, 169, 244, 1)',
                        'rgba(0, 188, 212, 1)',
                        'rgba(0, 150, 136, 1)',
                        'rgba(76, 175, 80, 1)',
                        'rgba(139, 195, 74, 1)',
                        'rgba(205, 220, 57, 1)',
                        'rgba(255, 235, 59, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    components: {
                        preview: true,
                        opacity: false,
                        hue: true,
                        // Input / output Options
                        interaction: {
                            hex: true,
                            rgba: true,
                            hsla: false,
                            hsva: false,
                            cmyk: false,
                            input: true,
                            clear: false,
                            save: false
                        }
                    }
                }).on('change', (color, source, instance) => {
                    picker.applyColor(true);
                    model = picker.getSelectedColor().toHEXA().toString();
                })
             })
            "
        wire:ignore>
        <label for="{{ $uid }}" class="inline-flex gap-3 items-center border border-slate-300 focus-within:border-indigo-300 focus-within:ring focus-within:ring-indigo-200 focus-within:ring-opacity-50 rounded-md shadow-sm overflow-hidden">
            <span class="empty:hidden ml-2">{!! edenIcon($prefix) !!}</span>
            <input id="{{ $uid }}" wire:model.defer="fields.{{ $key }}" {!! $attributes !!}>
            <span class="empty:hidden mr-2">{!! edenIcon($suffix) !!}</span>
        </label>
    </div>
    @include('eden::fields.error')
    @include('eden::fields.help')
</div>
