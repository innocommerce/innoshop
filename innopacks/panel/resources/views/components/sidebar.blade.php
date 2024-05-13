<div class="accordion accordion-flush">
  @foreach($menuLinks as $index => $menuLink)
    <div class="accordion-item">
      @if(!$menuLink['has_children'])
        <a class="accordion-button {{ $menuLink['active'] ? '' : 'collapsed' }}" href="{{ $menuLink['url'] }}">
          <span class="icon"><i class="bi {{ $menuLink['icon'] ?? 'bi-house' }}"></i></span> {{ $menuLink['title'] }}
        </a>
      @else
        <h2 class="accordion-header">
          <button class="accordion-button {{ $menuLink['active'] ? '' : (system_setting('expand') ? '' : 'collapsed') }}" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#flush-collapseOne-{{ $index }}"
                  aria-expanded="{{ $menuLink['active'] ? 'true' : 'false' }}"
                  aria-controls="flush-collapseOne-{{ $index }}">
            <span class="icon"><i class="bi {{ $menuLink['icon'] ?? 'bi-house' }}"></i></span> {{ $menuLink['title'] }}
          </button>
        </h2>
        <div id="flush-collapseOne-{{ $index }}"
             class="accordion-collapse collapse {{ $menuLink['active'] ? 'show' : (system_setting('expand') ? 'show' : '') }}"
             data-bs-parent="#sidebar-parent">
          <div class="accordion-body p-0">
            <ul class="nav flex-column">
              @foreach($menuLink['children'] as $child)
                <li class="nav-item">
                  <a href="{{ $child['url'] }}"
                     class="nav-link {{ $child['active'] ? 'active' : '' }}">{{ $child['title'] }}</a>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </div>
  @endforeach
</div>