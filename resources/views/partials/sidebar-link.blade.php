<li class="sidebar-item {{ $item['active'] ? 'active' : '' }}">
    <a class="sidebar-link" href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif>
        <i data-lucide="{{ $item['icon'] }}"></i>
        <span class="mn-link-text">{{ $item['label'] }}</span>
        @if (!empty($item['badge']))<span class="mn-badge">{{ $item['badge'] }}</span>@endif
    </a>
</li>
