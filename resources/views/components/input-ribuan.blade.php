@props([
    'class' => '',
    'inputClass' => '',
    'disabled' => false,
])

<div x-data="{
    raw: @entangle($attributes->wire('model')),
    display: '',
    formatRibuan(val) {
        if (val === null || val === undefined || val === '' || isNaN(val)) return '0';
        let clean = val.toString().replace(/[^0-9]/g, '');
        if (!clean) return '0';
        let num = parseInt(clean, 10);
        return isNaN(num) ? '0' : num.toLocaleString('id-ID');
    },
    update(e) {
        let clean = e.target.value.replace(/[^0-9]/g, '');
        let num = clean ? parseInt(clean, 10) : 0;
        this.raw = num;
        this.display = clean ? num.toLocaleString('id-ID') : '';
        e.target.value = this.display;
    },
    init() {
        this.display = this.formatRibuan(this.raw);
        this.$watch('raw', (newVal) => {
            this.display = this.formatRibuan(newVal);
        });
    }
}" class="relative w-full {{ $class }}">
    <input type="text"
           inputmode="numeric"
           :value="display"
           @input="update($event)"
           @blur="if (!display) { display = '0'; $el.value = '0'; raw = 0; }"
           @if($disabled) disabled @endif
           {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'w-full px-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-gray-50 border border-gray-200 rounded-lg focus:border-[#5E17EB] focus:bg-white transition-colors ' . $inputClass]) }}>
</div>
