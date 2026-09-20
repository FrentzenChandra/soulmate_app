@props([
    'class' => '',
    'inputClass' => '',
    'disabled' => false,
])

<div x-data="{
    raw: @entangle($attributes->wire('model')),
    display: '',
    formatRupiah(val) {
        if (val === null || val === undefined || val === '' || isNaN(val)) return '';
        let num = parseInt(val.toString().replace(/[^0-9]/g, ''), 10);
        return isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
    },
    update(e) {
        let clean = e.target.value.replace(/[^0-9]/g, '');
        let num = clean ? parseInt(clean, 10) : 0;
        this.raw = num;
        this.display = num ? num.toLocaleString('id-ID') : '';
        e.target.value = this.display;
    },
    init() {
        this.display = this.formatRupiah(this.raw);
        this.$watch('raw', (newVal) => {
            this.display = this.formatRupiah(newVal);
        });
    }
}" class="relative {{ $class }}">
    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-gray-400 select-none pointer-events-none">Rp</span>
    <input type="text"
           inputmode="numeric"
           :value="display"
           @input="update($event)"
           @if($disabled) disabled @endif
           {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'w-full pl-10 pr-3.5 py-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#5E17EB] focus:border-[#5E17EB] bg-white ' . $inputClass]) }}>
</div>
