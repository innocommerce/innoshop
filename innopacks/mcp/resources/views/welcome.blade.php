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
    .bi-puzzle::before { content: "\f503"; }
    .bi-broadcast::before { content: "\f1d6"; }
    .bi-stack::before { content: "\f585"; }
    .bi-collection::before { content: "\f2cc"; }
    .bi-cpu::before { content: "\f2d6"; }
    .bi-plug::before { content: "\f4f7"; }
    .bi-gear::before { content: "\f3e5"; }
    .bi-lightbulb-fill::before { content: "\f468"; }
    .bi-code-slash::before { content: "\f2c6"; }
    .bi-cursor-fill::before { content: "\f2e1"; }
    .bi-wind::before { content: "\f61d"; }
    .bi-stars::before { content: "\f589"; }
    .bi-chat-square-dots::before { content: "\f25f"; }
    .bi-chat-text::before { content: "\f267"; }
    .bi-fire::before { content: "\f7f6"; }
    .bi-flower1::before { content: "\f3cd"; }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; display: flex; min-height: 100vh; background: #f5f6fa; color: #1e1e2e; }
    a { color: #4f6ef7; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .sidebar { width: 260px; background: #121826; color: #c9cdd4; display: flex; flex-direction: column; flex-shrink: 0; position: fixed; top: 0; left: 0; bottom: 0; z-index: 10; overflow-y: auto; }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.06); color: #fff; text-decoration: none; }
    .sidebar-brand:hover { text-decoration: none; }
    .sidebar-brand img { width: 36px; height: 36px; object-fit: contain; flex-shrink: 0; }
    .sidebar-brand h2 { font-size: 1rem; font-weight: 600; color: #fff; white-space: nowrap; }
    .sidebar-nav { flex: 1; padding: 12px 0; }
    .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 7px 20px; color: #9da4b0; font-size: .875rem; transition: all .15s; border-left: 3px solid transparent; }
    .sidebar-nav a:hover, .sidebar-nav a.active { color: #fff; background: rgba(255,255,255,.05); border-left-color: #4f6ef7; text-decoration: none; }
    .sidebar-nav .nav-label { display: flex; flex-direction: column; flex: 1; line-height: 1.3; min-width: 0; }
    .sidebar-nav .nav-label small { font-size: .68rem; color: #6b7280; font-weight: 400; margin-top: 1px; }
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
    .card-tag { display: inline-block; margin-left: 8px; padding: 2px 8px; font-size: .7rem; font-weight: 500; color: #4b5563; background: #f3f4f6; border-radius: 10px; vertical-align: middle; }
    .card-hint { color: #6b7280; font-size: .85rem; margin: 0 0 10px; line-height: 1.55; }
    .card-foot { text-align: center; color: #6b7280; font-size: .85rem; margin: 8px 0 0; }
    .agent-grid { display: flex; flex-wrap: wrap; gap: 6px; margin: 6px 0 12px; }
    .agent-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px 3px 8px; background: #f3f4f6; border-radius: 14px; font-size: .78rem; color: #374151; }
    .agent-badge i { color: var(--badge-color, #6b7280); font-size: .95rem; }
    .conn-table { width: 100%; font-size: .85rem; border-collapse: collapse; margin: 0; }
    .conn-table th { text-align: left; padding: 6px 10px; color: #6b7280; font-weight: 600; width: 90px; vertical-align: top; }
    .conn-table td { padding: 6px 10px; }
    .conn-table code { background: #eeeef2; padding: 1px 5px; border-radius: 4px; font-size: .82rem; color: #1e1e2e; }
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

    .arch-flow { display: grid; grid-template-columns: 1fr 140px 1fr; gap: 14px; align-items: stretch; margin: 6px 0 14px; }
    .arch-module { background: #fff; border: 1px solid #eaecf0; border-radius: 12px; padding: 16px 18px; position: relative; }
    .arch-module::before { content: ''; position: absolute; top: 14px; bottom: 14px; left: 0; width: 3px; border-radius: 0 2px 2px 0; }
    .arch-module-ai::before { background: linear-gradient(180deg, #4f6ef7, #8b5cf6); }
    .arch-module-mcp::before { background: linear-gradient(180deg, #10b981, #06b6d4); }
    .arch-module-head { display: flex; align-items: center; gap: 12px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px dashed #e5e7eb; }
    .arch-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0; }
    .arch-module-ai .arch-icon { background: linear-gradient(135deg, #4f6ef7, #8b5cf6); }
    .arch-module-mcp .arch-icon { background: linear-gradient(135deg, #10b981, #06b6d4); }
    .arch-module-head strong { display: block; font-size: .95rem; color: #111; }
    .arch-module-head small { display: block; font-size: .75rem; color: #6b7280; margin-top: 1px; }
    .arch-cap { list-style: none; padding: 0; margin: 0; }
    .arch-cap li { display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: .83rem; color: #4b5563; }
    .arch-cap li i { color: #9ca3af; width: 14px; text-align: center; }
    .arch-cap li code { background: #f3f4f6; padding: 1px 6px; border-radius: 4px; font-size: .76rem; color: #1e1e2e; }
    .arch-connector { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; }
    .arch-connector code { background: #4f6ef7; color: #fff; padding: 3px 10px; border-radius: 6px; font-size: .76rem; font-weight: 600; }
    .arch-connector-arrows { color: #4f6ef7; font-size: 1rem; }
    .arch-connector-cap { font-size: .72rem; color: #6b7280; }
    .arch-desc { color: #6b7280; font-size: .88rem; line-height: 1.6; text-align: center; margin: 0 auto 14px; max-width: 720px; }
    .arch-tip { display: flex; align-items: flex-start; gap: 10px; background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fcd34d; border-radius: 10px; padding: 12px 16px; }
    .arch-tip i { color: #d97706; font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
    .arch-tip span { font-size: .85rem; color: #78350f; line-height: 1.55; }
    .arch-tip strong { color: #92400e; }

    .copy-btn { background: #fff; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; cursor: pointer; font-size: .85rem; opacity: .6; transition: opacity .15s; }
    .copy-btn:hover { opacity: 1; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 24px 20px; }
      .arch-flow { grid-template-columns: 1fr; }
      .arch-connector { padding: 4px 0; }
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <a class="sidebar-brand" href="{{ url('/mcp') }}">
      <img src="{{ $shopLogo }}" alt="{{ $shopName }}" onerror="this.style.display='none'">
      <h2>{{ $shopName }}</h2>
    </a>
    <nav class="sidebar-nav">
      @php $hideNavSub = ($currentLocale['code'] ?? 'en') === 'en'; @endphp
      <a href="#overview"><i class="bi bi-speedometer2"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_overview') }}</span>@unless ($hideNavSub)<small>Overview</small>@endunless</span></a>
      <a href="#architecture"><i class="bi bi-diagram-3"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_architecture') }}</span>@unless ($hideNavSub)<small>Architecture</small>@endunless</span></a>
      <a href="#connect"><i class="bi bi-lightning-charge"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_connect') }}</span>@unless ($hideNavSub)<small>Connect</small>@endunless</span></a>
      <a href="#auth"><i class="bi bi-shield-lock"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_auth') }}</span>@unless ($hideNavSub)<small>Auth</small>@endunless</span></a>
      <a href="#tools"><i class="bi bi-tools"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_tools') }}</span>@unless ($hideNavSub)<small>Tools</small>@endunless</span><span class="count">{{ count($tools) }}</span></a>
      <a href="#plugins"><i class="bi bi-puzzle"></i><span class="nav-label"><span>{{ __('mcp::welcome.nav_plugins') }}</span>@unless ($hideNavSub)<small>Plugins</small>@endunless</span></a>
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

      <div class="arch-flow">
        <div class="arch-module arch-module-ai">
          <div class="arch-module-head">
            <span class="arch-icon"><i class="bi bi-stack"></i></span>
            <div>
              <strong>{{ __('mcp::welcome.arch_ai') }}</strong>
              <small>{{ __('mcp::welcome.arch_ai_role') }}</small>
            </div>
          </div>
          <ul class="arch-cap">
            <li><i class="bi bi-tools"></i> {{ count($tools) }} {!! __('mcp::welcome.arch_tools') !!}</li>
            <li><i class="bi bi-collection"></i> <code>ToolRegistry</code></li>
            <li><i class="bi bi-cpu"></i> <code>PanelChatAgent</code></li>
          </ul>
        </div>

        <div class="arch-connector">
          <code>ToolInterface</code>
          <span class="arch-connector-arrows">⟷</span>
          <span class="arch-connector-cap">{{ __('mcp::welcome.arch_bridge') }}</span>
        </div>

        <div class="arch-module arch-module-mcp">
          <div class="arch-module-head">
            <span class="arch-icon"><i class="bi bi-broadcast"></i></span>
            <div>
              <strong>{{ __('mcp::welcome.arch_mcp') }}</strong>
              <small>{{ __('mcp::welcome.arch_mcp_role') }}</small>
            </div>
          </div>
          <ul class="arch-cap">
            <li><i class="bi bi-plug"></i> MCP {!! __('mcp::welcome.arch_protocol') !!}</li>
            <li><i class="bi bi-shield-lock"></i> Sanctum + Origin</li>
            <li><i class="bi bi-gear"></i> <code>InnoShopMcpServer</code></li>
          </ul>
        </div>
      </div>

      <p class="arch-desc">{{ __('mcp::welcome.arch_desc') }}</p>

      <div class="arch-tip">
        <i class="bi bi-lightbulb-fill"></i>
        <span><strong>{{ __('mcp::welcome.arch_note_badge') }}</strong> {!! __('mcp::welcome.arch_note') !!}</span>
      </div>
    </section>

    <section id="connect">
      <h2>{{ __('mcp::welcome.connect_title') }}</h2>

      <div class="card" id="card-claude">
        <h3>Claude Code <span class="card-tag">{{ __('mcp::welcome.tag_cli') }}</span></h3>
        <div style="position:relative;">
          <pre><code id="cmd-claude">claude mcp add --transport http innoshop {{ $mcpUrl }} \
  --header "Authorization: Bearer <span class="token-val">YOUR_TOKEN</span>"</code></pre>
          <button class="copy-btn" data-target="cmd-claude" title="Copy" style="position:absolute;top:8px;right:8px;"><i class="bi bi-clipboard"></i></button>
        </div>
      </div>

      <div class="card" id="card-standard">
        <h3>Standard MCP Clients <span class="card-tag">{{ __('mcp::welcome.tag_oss') }}</span></h3>
        <p class="card-hint">{!! __('mcp::welcome.hint_oss') !!}</p>
        <div class="agent-grid">
          <span class="agent-badge" style="--badge-color:#cc785c"><i class="bi bi-stars"></i> Claude Desktop</span>
          <span class="agent-badge" style="--badge-color:#111"><i class="bi bi-cursor-fill"></i> Cursor</span>
          <span class="agent-badge" style="--badge-color:#2f80ed"><i class="bi bi-code-slash"></i> Cline</span>
          <span class="agent-badge" style="--badge-color:#0078d4"><i class="bi bi-wind"></i> Windsurf</span>
          <span class="agent-badge" style="--badge-color:#ff6b9d"><i class="bi bi-flower1"></i> Cherry Studio</span>
          <span class="agent-badge" style="--badge-color:#4d6bfe"><i class="bi bi-chat-square-dots"></i> ChatBox</span>
          <span class="agent-badge" style="--badge-color:#ff6b35"><i class="bi bi-fire"></i> 5ire</span>
          <span class="agent-badge" style="--badge-color:#00a884"><i class="bi bi-chat-text"></i> LibreChat</span>
        </div>
        <div style="position:relative;">
          @include('mcp::partials._mcp_json', ['id' => 'cmd-standard'])
          <button class="copy-btn" data-target="cmd-standard" title="Copy" style="position:absolute;top:8px;right:8px;"><i class="bi bi-clipboard"></i></button>
        </div>
        <p class="card-foot">{!! __('mcp::welcome.connect_more') !!}</p>
      </div>

      <div class="card" id="card-workbuddy">
        <h3>WorkBuddy <span class="card-tag">{{ __('mcp::welcome.tag_workbuddy') }}</span></h3>
        <p class="card-hint">{!! __('mcp::welcome.hint_workbuddy') !!}</p>
        <table class="conn-table">
          <tr><th>{{ __('mcp::welcome.field_url') }}</th><td><code>{{ $mcpUrl }}</code></td></tr>
          <tr><th>{{ __('mcp::welcome.field_header') }}</th><td><code>Authorization: Bearer <span class="token-val">YOUR_TOKEN</span></code></td></tr>
        </table>
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
    </section>

    <section id="plugins">
      <h2>{{ __('mcp::welcome.nav_plugins') }}</h2>
      <div class="card">
        <p style="font-size:.88rem;color:#4b5563;line-height:1.6;">{!! __('mcp::welcome.tools_plugin_hint') !!}</p>
        <div style="position:relative;">
          <pre><code id="cmd-plugins">add_hook_filter('ai.tools', function ($registry) {
    $registry->register(new MyCustomTool);
    return $registry;
});</code></pre>
          <button class="copy-btn" data-target="cmd-plugins" title="Copy" style="position:absolute;top:8px;right:8px;"><i class="bi bi-clipboard"></i></button>
        </div>
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
