@php
$categories = [
    'product' => ['label' => __('mcp::welcome.cat_product'), 'prefix' => ['product_', 'sku_']],
    'order' => ['label' => __('mcp::welcome.cat_order'), 'prefix' => ['order_']],
    'customer' => ['label' => __('mcp::welcome.cat_customer'), 'prefix' => ['customer_']],
    'catalog' => ['label' => __('mcp::welcome.cat_catalog'), 'prefix' => ['category_', 'brand_']],
    'content' => ['label' => __('mcp::welcome.cat_content'), 'prefix' => ['article_', 'catalog_', 'page_', 'tag_']],
    'shipping' => ['label' => __('mcp::welcome.cat_shipping'), 'prefix' => ['shipment_']],
    'analytics' => ['label' => __('mcp::welcome.cat_analytics'), 'prefix' => ['dashboard', 'sales_stats', 'stock_report']],
    'config' => ['label' => __('mcp::welcome.cat_config'), 'prefix' => ['locale_', 'currency_', 'country_', 'region_', 'tax_', 'attribute_', 'option_', 'review_', 'file_']],
];

$catTools = [];
$seen = [];
foreach ($tools as $tool) {
    $name = $tool->name();
    foreach ($categories as $key => $cat) {
        foreach ($cat['prefix'] as $prefix) {
            if (str_starts_with($name, $prefix)) { $catTools[$key][] = $tool; $seen[] = $name; continue 2; }
        }
    }
}
// Any uncategorized tools
$catTools['other'] = [];
foreach ($tools as $tool) {
    if (! in_array($tool->name(), $seen)) { $catTools['other'][] = $tool; }
}
$categories['other'] = ['label' => __('mcp::welcome.cat_other'), 'prefix' => []];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $shopName }} &mdash; MCP Server</title>
  <style>
    @font-face {
      font-family: "bootstrap-icons";
      src: url("{{ asset('vendor/bootstrap-icons/bootstrap-icons.woff2') }}") format("woff2"),
           url("{{ asset('vendor/bootstrap-icons/bootstrap-icons.woff') }}") format("woff");
      font-display: swap;
    }
    .bi { display: inline-block; font-family: "bootstrap-icons"; font-style: normal; font-weight: normal; line-height: 1; vertical-align: -.125em; }
    .bi-speedometer2::before { content: "\f580"; }
    .bi-diagram-3::before { content: "\f2ee"; }
    .bi-lightning-charge::before { content: "\f46d"; }
    .bi-shield-lock::before { content: "\f538"; }
    .bi-tools::before { content: "\f5db"; }
    .bi-clipboard::before { content: "\f290"; }
    .bi-exclamation-triangle-fill::before { content: "\f33a"; }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; display: flex; min-height: 100vh; background: #f5f6fa; color: #1e1e2e; }
    a { color: #4f6ef7; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .sidebar { width: 260px; background: #121826; color: #c9cdd4; display: flex; flex-direction: column; flex-shrink: 0; position: fixed; top: 0; left: 0; bottom: 0; z-index: 10; overflow-y: auto; }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.06); }
    .sidebar-brand img { width: 36px; height: 36px; object-fit: contain; flex-shrink: 0; }
    .sidebar-brand h2 { font-size: 1rem; font-weight: 600; color: #fff; white-space: nowrap; }
    .sidebar-nav { flex: 1; padding: 12px 0; }
    .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #9da4b0; font-size: .875rem; transition: all .15s; border-left: 3px solid transparent; }
    .sidebar-nav a:hover, .sidebar-nav a.active { color: #fff; background: rgba(255,255,255,.05); border-left-color: #4f6ef7; text-decoration: none; }
    .sidebar-nav a .count { margin-left: auto; font-size: .75rem; color: #6b7280; }
    .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.06); font-size: .75rem; color: #6b7280; }
    .sidebar-footer .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: .7rem; font-weight: 600; background: rgba(79,110,247,.15); color: #4f6ef7; margin-right: 6px; }
    .locale-switcher { position: fixed; top: 16px; right: 24px; z-index: 20; }
    .locale-current { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #eaecf0; border-radius: 8px; padding: 6px 12px; font-size: .85rem; color: #1e1e2e; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .locale-current img, .locale-menu img { width: 20px; height: 15px; object-fit: cover; border: 1px solid #eaecf0; border-radius: 2px; }
    .locale-menu { display: none; position: absolute; right: 0; top: calc(100% + 6px); list-style: none; background: #fff; border: 1px solid #eaecf0; border-radius: 8px; box-shadow: 0 6px 18px rgba(0,0,0,.08); min-width: 160px; padding: 6px; }
    .locale-switcher:focus-within .locale-menu { display: block; }
    .locale-menu a { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 6px; font-size: .85rem; color: #1e1e2e; }
    .locale-menu a:hover { background: #f3f4f6; text-decoration: none; }

    .main { margin-left: 260px; flex: 1; padding: 40px 48px; max-width: 960px; }
    .main section { margin-bottom: 40px; }
    .main h1 { font-size: 1.7rem; font-weight: 700; margin-bottom: 6px; }
    .main .lead { color: #6b7280; font-size: .95rem; margin-bottom: 32px; }
    .main h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .main h2::before { content: ''; width: 4px; height: 18px; background: #4f6ef7; border-radius: 2px; display: inline-block; }

    .card { background: #fff; border-radius: 10px; padding: 22px 26px; margin-bottom: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.04); border: 1px solid #eaecf0; }
    .card h3 { font-size: .95rem; font-weight: 600; margin-bottom: 10px; }
    .card p { color: #6b7280; font-size: .9rem; margin-bottom: 10px; line-height: 1.55; }

    pre { background: #1a1d2e; color: #c9d1d9; padding: 14px 18px; border-radius: 8px; overflow-x: auto; font-size: .82rem; line-height: 1.55; }
    code { font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, monospace; }
    :not(pre) > code { background: #eeeef2; padding: 1px 5px; border-radius: 4px; font-size: .85rem; color: #4f6ef7; }

    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: .75rem; font-weight: 600; background: #dcfce7; color: #15803d; margin-left: 8px; }
    .warn-badge { background: #fef3c7; color: #92400e; }

    table { width: 100%; font-size: .85rem; border-collapse: collapse; }
    thead th { text-align: left; padding: 8px 10px; border-bottom: 2px solid #eaecf0; color: #6b7280; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
    tbody td { padding: 9px 10px; border-bottom: 1px solid #f3f4f6; }
    tbody tr:hover { background: #f9fafb; }

    .arch-diagram { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 12px; }
    .arch-box { flex: 1; min-width: 180px; padding: 16px; border-radius: 8px; font-size: .85rem; background: #f9fafb; border: 1px solid #eaecf0; }
    .arch-box strong { display: block; margin-bottom: 6px; font-size: .9rem; }
    .arch-box .arrow { color: #4f6ef7; font-size: 1.2rem; margin: 0 4px; }

    .copy-btn { background: #fff; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; cursor: pointer; font-size: .85rem; opacity: .6; transition: opacity .15s; }
    .copy-btn:hover { opacity: 1; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 24px 20px; }
      .arch-box { min-width: 100%; }
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="{{ $shopLogo }}" alt="{{ $shopName }}" onerror="this.style.display='none'">
      <h2>{{ $shopName }}</h2>
    </div>
    <nav class="sidebar-nav">
      <a href="#overview"><i class="bi bi-speedometer2"></i> {{ __('mcp::welcome.nav_overview') }}</a>
      <a href="#architecture"><i class="bi bi-diagram-3"></i> {{ __('mcp::welcome.nav_architecture') }}</a>
      <a href="#connect"><i class="bi bi-lightning-charge"></i> {{ __('mcp::welcome.nav_connect') }}</a>
      <a href="#auth"><i class="bi bi-shield-lock"></i> {{ __('mcp::welcome.nav_auth') }}</a>
      <a href="#tools"><i class="bi bi-tools"></i> {{ __('mcp::welcome.nav_tools') }} <span class="count">{{ count($tools) }}</span></a>
    </nav>
    <div class="sidebar-footer">
      <span class="badge">MCP</span> Streamable HTTP<br>
      <span style="margin-top:4px;display:inline-block;">{{ count($tools) }} {{ __('mcp::welcome.tools_available') }}</span>
    </div>
  </aside>

  <div class="locale-switcher">
    <button class="locale-current" type="button">
      @if (! empty($currentLocale['image']))<img src="{{ image_origin($currentLocale['image']) }}" alt="">@endif
      <span>{{ $currentLocale['name'] ?? app()->getLocale() }}</span> &#9662;
    </button>
    <ul class="locale-menu">
      @foreach ($locales as $locale)
        <li>
          <a href="{{ route('mcp.locale.switch', $locale['code']) }}">
            @if (! empty($locale['image']))<img src="{{ image_origin($locale['image']) }}" alt="">@endif
            {{ $locale['name'] }}
          </a>
        </li>
      @endforeach
    </ul>
  </div>

  <main class="main">
    <h1>{{ $shopName }} MCP Server <span class="status-badge">{{ __('mcp::welcome.active') }}</span></h1>
    <p class="lead">{!! __('mcp::welcome.subtitle', ['name' => '<strong>'.$shopName.'</strong>']) !!}</p>

    <section id="overview">
      <h2>{{ __('mcp::welcome.overview_title') }}</h2>
      <div class="card">
        <p>{!! __('mcp::welcome.overview_desc') !!}</p>
        <div style="margin-top:12px;display:flex;align-items:center;gap:10px;">
          <span style="font-weight:600;font-size:.85rem;">{{ __('mcp::welcome.endpoint_label') }}</span>
          <code style="font-size:.85rem;">POST {{ $mcpUrl }}</code>
        </div>
        <div style="margin-top:10px;">
          <span class="warn-badge">{{ $writeEnabled ? __('mcp::welcome.write_mode_on') : __('mcp::welcome.write_mode_off') }}</span>
        </div>
      </div>
    </section>

    <section id="architecture">
      <h2>{{ __('mcp::welcome.nav_architecture') }}</h2>
      <div class="card">
        <p>{{ __('mcp::welcome.arch_desc') }}</p>
        <div class="arch-diagram">
          <div class="arch-box">
            <strong>{{ __('mcp::welcome.arch_ai') }}</strong>
            <span style="color:#6b7280;">{{ count($tools) }} {!! __('mcp::welcome.arch_tools') !!}</span><br>
            <code style="font-size:.75rem;">ToolInterface</code><br>
            <code style="font-size:.75rem;">ToolRegistry</code><br>
            <code style="font-size:.75rem;">PanelChatAgent</code>
          </div>
          <div class="arch-box" style="text-align:center;display:flex;align-items:center;justify-content:center;">
            <span class="arrow">&rarr;</span> {{ __('mcp::welcome.arch_bridge') }} <span class="arrow">&rarr;</span>
          </div>
          <div class="arch-box">
            <strong>{{ __('mcp::welcome.arch_mcp') }}</strong>
            <span style="color:#6b7280;">MCP {!! __('mcp::welcome.arch_protocol') !!}</span><br>
            <code style="font-size:.75rem;">RegistryToolAdapter</code><br>
            <code style="font-size:.75rem;">InnoShopMcpServer</code><br>
            <code style="font-size:.75rem;">Sanctum + Origin</code>
          </div>
        </div>
        <p style="margin-top:14px;">
          <span class="warn-badge" style="margin-right:6px;">{{ __('mcp::welcome.arch_note_badge') }}</span>
          {!! __('mcp::welcome.arch_note') !!}
        </p>
      </div>
    </section>

    <section id="connect">
      <h2>{{ __('mcp::welcome.connect_title') }}</h2>

      <div class="card" id="card-claude">
        <h3>Claude Code</h3>
        <div style="position:relative;">
          <pre><code id="cmd-claude">claude mcp add --transport http innoshop {{ $mcpUrl }} \
  --header "Authorization: Bearer <span class="token-val">YOUR_TOKEN</span>"</code></pre>
          <button class="copy-btn" data-target="cmd-claude" title="Copy" style="position:absolute;top:8px;right:8px;"><i class="bi bi-clipboard"></i></button>
        </div>
      </div>

      <div class="card" id="card-json">
        <h3>Cursor / Cline / 其他 MCP 客户端</h3>
        <div style="position:relative;">
          <pre><code id="cmd-json">{
  "mcpServers": {
    "innoshop": {
      "url": "{{ $mcpUrl }}",
      "headers": {
        "Authorization": "Bearer <span class="token-val">YOUR_TOKEN</span>"
      }
    }
  }
}</code></pre>
          <button class="copy-btn" data-target="cmd-json" title="Copy" style="position:absolute;top:8px;right:8px;"><i class="bi bi-clipboard"></i></button>
        </div>
      </div>

      <div class="card" id="card-notoken" style="display:none;background:#fef3c7;border-color:#f59e0b;">
        <p style="margin:0;font-size:.9rem;"><i class="bi bi-exclamation-triangle-fill" style="color:#92400e;margin-right:6px;"></i>{{ __('mcp::welcome.no_token') }}</p>
      </div>
    </section>

    <section id="auth">
      <h2>{{ __('mcp::welcome.auth_title') }}</h2>
      <div class="card">
        <p>{{ __('mcp::welcome.auth_desc') }}</p>
        <pre><code>POST {{ $loginUrl }}
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "your-password"
}</code></pre>
        <p style="margin-top:10px;">{{ __('mcp::welcome.auth_token_hint') }} <strong>{{ __('mcp::welcome.system_settings') }}</strong> {{ __('mcp::welcome.auth_mcp_card') }}</p>
      </div>
    </section>

    <section id="tools">
      <h2>{{ __('mcp::welcome.tools_title') }} <span class="status-badge">{{ count($tools) }}</span></h2>
      @unless ($writeEnabled)
      <p style="margin:0 0 14px;font-size:.85rem;color:#92400e;"><i class="bi bi-exclamation-triangle-fill" style="margin-right:6px;"></i>{{ __('mcp::welcome.tools_write_hint') }}</p>
      @endunless
      @foreach ($categories as $key => $cat)
      @if (!empty($catTools[$key]))
      <div class="card" style="padding:0;overflow:hidden;margin-bottom:12px;">
        <h3 style="padding:14px 18px;margin:0;background:#f9fafb;font-size:.85rem;border-bottom:1px solid #eaecf0;">{{ $cat['label'] }} <span style="color:#9ca3af;font-weight:400;">({{ count($catTools[$key]) }})</span></h3>
        <table>
          <tbody>
            @foreach ($catTools[$key] as $tool)
            <tr>
              <td style="width:200px;"><code>{{ $tool->name() }}</code> @if ($tool->isWrite())<span class="warn-badge">{{ __('mcp::welcome.tool_write_badge') }}</span>@endif</td>
              <td style="color:#6b7280;">{{ $tool->localizedDescription() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
      @endforeach

      <div class="card" style="margin-top:16px;">
        <p style="font-size:.85rem;color:#6b7280;">{{ __('mcp::welcome.tools_plugin_hint') }}</p>
        <pre><code>add_hook_filter('ai.tools', function ($registry) {
    $registry->register(new MyCustomTool);
    return $registry;
});</code></pre>
      </div>
    </section>
  </main>
  <script>
    (function () {
      const meta = document.querySelector('meta[name="api-token"]');
      const token = meta ? meta.content.trim() : '';

      if (token) {
        document.querySelectorAll('.token-val').forEach(el => el.textContent = token);
      } else {
        document.getElementById('card-notoken').style.display = 'block';
      }

      document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const target = document.getElementById(this.dataset.target);
          if (! target) return;
          // Get raw text (not innerHTML) for copying
          const text = target.textContent || target.innerText;
          navigator.clipboard.writeText(text).then(() => {
            const orig = this.innerHTML;
            this.innerHTML = '&#10003;';
            this.style.color = '#15803d';
            setTimeout(() => { this.innerHTML = orig; this.style.color = ''; }, 1500);
          }).catch(() => {
            // Fallback: select the text
            const range = document.createRange();
            range.selectNodeContents(target);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
          });
        });
      });
    })();
  </script>
</body>
</html>
