{{-- Shared sidebar renderer.
     $menu: list of links and categories. A category has 'label', 'icon', 'items'.
     A link has 'label', 'icon', 'url', 'active', and optional 'show', 'badge'. --}}
<ul class="sidebar-nav">
    @foreach ($menu as $entry)
        @if (isset($entry['items']))
            @php
                $items = collect($entry['items'])->filter();
                $open = $items->contains(fn ($i) => $i['active']);
                $badges = $items->pluck('badge')->filter();
                // Numbers are summed; a text badge such as "Baru" is shown as is.
                $summary = $badges->isEmpty() ? null
                    : ($badges->every(fn ($b) => is_int($b)) ? $badges->sum() : $badges->first());
            @endphp
            @if ($items->isNotEmpty())
                <li>
                    <details class="mn-group" @if($open) open @endif>
                        <summary>
                            <i data-lucide="{{ $entry['icon'] }}"></i>
                            <span class="mn-link-text">{{ $entry['label'] }}</span>
                            @if ($summary)<span class="mn-badge">{{ $summary }}</span>@endif
                            <i class="mn-chevron" data-lucide="chevron-down"></i>
                        </summary>
                        <ul>
                            @foreach ($items as $item)
                                @include('partials.sidebar-link', ['item' => $item])
                            @endforeach
                        </ul>
                    </details>
                </li>
            @endif
        @else
            @include('partials.sidebar-link', ['item' => $entry])
        @endif
    @endforeach
</ul>
