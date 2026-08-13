<li x-data="{ open: true }">
    <div class="flex items-center gap-2 py-1.5 px-2 rounded-lg {{ $node['highlight'] ? 'font-bold' : '' }} transition-colors duration-150"
        style="{{ $node['highlight'] ? 'background: rgba(168, 85, 247, 0.18); border: 1px solid rgba(168, 85, 247, 0.6);' : '' }}"
        @click="{{ count($node['children']) > 0 ? 'open = !open' : '' }}">
        @if(count($node['children']) > 0)
            <svg x-show="open" class="w-3.5 h-3.5 shrink-0 cursor-pointer" style="color: var(--color-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg x-show="!open" x-cloak class="w-3.5 h-3.5 shrink-0 cursor-pointer" style="color: var(--color-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @else
            <span class="w-3.5 h-3.5 shrink-0 block rounded-full border" style="border-color: var(--color-border);"></span>
        @endif
        <span class="shrink-0 inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide"
            style="background: var(--color-glass-bg); border: 1px solid var(--color-border); color: var(--color-text-secondary);">
            {{ $node['type'] }}
        </span>
        <span class="text-primary {{ $node['highlight'] ? '!text-purple-400' : '' }}">{{ $node['name'] }}</span>
        @if($node['highlight'])
            <span class="ml-auto shrink-0 text-[10px] font-semibold uppercase tracking-wide" style="color: rgb(168, 85, 247);">terpilih</span>
        @endif
    </div>
    @if(count($node['children']) > 0)
        <ul x-show="open" x-cloak class="ml-5 border-l pl-3 space-y-1" style="border-color: var(--color-border);">
            @foreach($node['children'] as $child)
                @include('livewire.admin.structure-organization._tree-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
