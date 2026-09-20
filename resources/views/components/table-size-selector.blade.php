<div x-data="{
    currentSize: localStorage.getItem('soulmate_table_size') || 'md',
    changeSize(size) {
        this.currentSize = size;
        localStorage.setItem('soulmate_table_size', size);
        window.dispatchEvent(new CustomEvent('table-size-changed', { detail: size }));
    }
}" x-init="
    window.addEventListener('table-size-changed', (e) => { currentSize = e.detail; });
" class="inline-flex items-center p-1 bg-gray-100/90 rounded-xl border border-gray-200 shadow-2xs shrink-0">
    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider px-1.5 hidden sm:inline-block">Ukuran:</span>
    <button type="button"
            @click="changeSize('sm')"
            :class="currentSize === 'sm' ? 'bg-white text-[#5E17EB] shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 font-bold'"
            class="px-2.5 py-1 text-xs rounded-lg transition-all cursor-pointer"
            title="Ukuran Font S (Kecil)">
        S
    </button>
    <button type="button"
            @click="changeSize('md')"
            :class="currentSize === 'md' ? 'bg-white text-[#5E17EB] shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 font-bold'"
            class="px-2.5 py-1 text-xs rounded-lg transition-all cursor-pointer"
            title="Ukuran Font M (Normal)">
        M
    </button>
    <button type="button"
            @click="changeSize('lg')"
            :class="currentSize === 'lg' ? 'bg-white text-[#5E17EB] shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 font-bold'"
            class="px-2.5 py-1 text-xs rounded-lg transition-all cursor-pointer"
            title="Ukuran Font L (Besar)">
        L
    </button>
</div>
