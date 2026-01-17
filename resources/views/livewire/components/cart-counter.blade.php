<a href="{{ route('cart') }}" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 transition-colors" wire:navigate>
    <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
    @if($this->count > 0)
        <span class="absolute top-1 right-1 bg-[#13ec13] text-[10px] font-bold px-1.5 py-0.5 rounded-full text-black min-w-[18px] text-center">{{ $this->count }}</span>
    @endif
</a>
