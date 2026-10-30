@props([
    'class' => '',
    'inputClass' => '',
    'disabled' => false,
])

<div x-data="{
    raw: @entangle($attributes->wire('model')),
    display: '',
    formatRibuan(val) {
        if (val === null || val === undefined || val === '' || isNaN(val)) return '';
        let clean = val.toString().replace(/[^0-9]/g, '');
        if (!clean) return '';
        let num = parseInt(clean, 10);
        return isNaN(num) || num === 0 ? '' : num.toLocaleString('id-ID');
    },
    computeCursorPos(str, targetDigitsBefore) {
        if (targetDigitsBefore <= 0) return 0;
        let count = 0;
        for (let i = 0; i < str.length; i++) {
            if (/[0-9]/.test(str[i])) {
                count++;
                if (count === targetDigitsBefore) {
                    return i + 1;
                }
            }
        }
        return str.length;
    },
    update(e) {
        const el = e.target;
        const oldVal = el.value;
        const cursorPos = el.selectionEnd ?? oldVal.length;
        const digitsBefore = oldVal.slice(0, cursorPos).replace(/[^0-9]/g, '').length;
        const clean = oldVal.replace(/[^0-9]/g, '');

        if (!clean) {
            this.raw = null;
            this.display = '';
            el.value = '';
            el.setSelectionRange(0, 0);
            return;
        }

        const num = parseInt(clean, 10);
        if (isNaN(num) || num === 0) {
            this.raw = null;
            this.display = '';
            el.value = '';
            el.setSelectionRange(0, 0);
            return;
        }

        this.raw = num;
        this.display = num.toLocaleString('id-ID');
        el.value = this.display;

        const newPos = this.computeCursorPos(this.display, digitsBefore);
        el.setSelectionRange(newPos, newPos);

        this.$nextTick(() => {
            if (document.activeElement === el) {
                el.setSelectionRange(newPos, newPos);
            }
        });
    },
    handleKeydown(e) {
        const el = e.target;
        if (el.selectionStart !== el.selectionEnd) return;

        const pos = el.selectionStart;
        const val = el.value;

        if (e.key === 'Backspace' && pos > 1 && val[pos - 1] === '.') {
            e.preventDefault();
            const newVal = val.slice(0, pos - 2) + val.slice(pos);
            el.value = newVal;
            el.setSelectionRange(pos - 2, pos - 2);
            this.update({ target: el });
        } else if (e.key === 'Delete' && pos < val.length - 1 && val[pos] === '.') {
            e.preventDefault();
            const newVal = val.slice(0, pos) + val.slice(pos + 2);
            el.value = newVal;
            el.setSelectionRange(pos, pos);
            this.update({ target: el });
        }
    },
    init() {
        this.display = this.formatRibuan(this.raw);
        this.$watch('raw', (newVal) => {
            if (document.activeElement !== this.$refs.input) {
                this.display = this.formatRibuan(newVal);
            }
        });
    }
}" class="relative w-full {{ $class }}">
    <input type="text"
           x-ref="input"
           inputmode="numeric"
           :value="display"
           @input="update($event)"
           @keydown="handleKeydown($event)"
           @blur="display = formatRibuan(raw)"
           @if($disabled) disabled @endif
           {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'w-full px-2.5 py-1.5 text-right font-mono font-bold text-xs text-black bg-gray-50 border border-gray-200 rounded-lg focus:border-[#5E17EB] focus:bg-white transition-colors ' . $inputClass]) }}>
</div>
