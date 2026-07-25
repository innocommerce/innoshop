<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $shopName }} &mdash; {{ __('mcp::welcome.title') }}</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; display: flex; min-height: 100vh; background: #f5f6fa; color: #1e1e2e; }
    a { color: #4f6ef7; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .sidebar { width: 260px; background: #121826; color: #c9cdd4; display: flex; flex-direction: column; flex-shrink: 0; position: fixed; top: 0; left: 0; bottom: 0; z-index: 10; }
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.06); }
    .sidebar-brand img { width: 36px; height: 36px; object-fit: contain; flex-shrink: 0; }
    .sidebar-brand h2 { font-size: 1rem; font-weight: 600; color: #fff; white-space: nowrap; }
    .sidebar-nav { flex: 1; padding: 12px 0; }
    .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #9da4b0; font-size: .875rem; transition: all .15s; border-left: 3px solid transparent; }
    .sidebar-nav a:hover, .sidebar-nav a.active { color: #fff; background: rgba(255,255,255,.05); border-left-color: #4f6ef7; text-decoration: none; }
    .sidebar-nav a .icon { width: 18px; font-size: .9rem; text-align: center; flex-shrink: 0; }
    .sidebar-nav a .count { margin-left: auto; font-size: .75rem; color: #6b7280; }
    .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.06); font-size: .75rem; color: #6b7280; }
    .sidebar-footer .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: .7rem; font-weight: 600; background: rgba(79,110,247,.15); color: #4f6ef7; margin-right: 6px; }
    .lang-switch { display: flex; gap: 6px; margin-top: 12px; }
    .lang-switch a { padding: 3px 10px; border-radius: 4px; font-size: .75rem; color: #9da4b0; border: 1px solid rgba(255,255,255,.1); }
    .lang-switch a.active, .lang-switch a:hover { color: #fff; border-color: #4f6ef7; background: rgba(79,110,247,.15); text-decoration: none; }

    .main { margin-left: 260px; flex: 1; padding: 40px 48px; max-width: 900px; }
    .main section { margin-bottom: 40px; }
    .main h1 { font-size: 1.7rem; font-weight: 700; margin-bottom: 6px; }
    .main .lead { color: #6b7280; font-size: .95rem; margin-bottom: 32px; }
    .main h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .main h2::before { content: ''; width: 4px; height: 18px; background: #4f6ef7; border-radius: 2px; display: inline-block; }

    .card { background: #fff; border-radius: 10px; padding: 22px 26px; margin-bottom: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.04); border: 1px solid #eaecf0; }
    .card h3 { font-size: .95rem; font-weight: 600; margin-bottom: 10px; }

    pre { background: #1a1d2e; color: #c9d1d9; padding: 14px 18px; border-radius: 8px; overflow-x: auto; font-size: .82rem; line-height: 1.55; }
    code { font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, monospace; }
    :not(pre) > code { background: #eeeef2; padding: 1px 5px; border-radius: 4px; font-size: .85rem; color: #4f6ef7; }

    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: .75rem; font-weight: 600; background: #dcfce7; color: #15803d; margin-left: 8px; }

    table { width: 100%; font-size: .85rem; border-collapse: collapse; }
    thead th { text-align: left; padding: 8px 10px; border-bottom: 2px solid #eaecf0; color: #6b7280; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; }
    tbody td { padding: 9px 10px; border-bottom: 1px solid #f3f4f6; }
    tbody tr:hover { background: #f9fafb; }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main { margin-left: 0; padding: 24px 20px; }
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
      <a href="#overview"><span class="icon">&#9679;</span> {{ __('mcp::welcome.nav_overview') }}</a>
      <a href="#connect"><span class="icon">&#9889;</span> {{ __('mcp::welcome.nav_connect') }}</a>
      <a href="#auth"><span class="icon">&#128274;</span> {{ __('mcp::welcome.nav_auth') }}</a>
      <a href="#tools"><span class="icon">&#128736;</span> {{ __('mcp::welcome.nav_tools') }} <span class="count">{{ count($tools) }}</span></a>
    </nav>
    <div class="sidebar-footer">
      <span class="badge">{{ __('mcp::welcome.protocol_label') }}</span> {{ __('mcp::welcome.protocol_desc') }}<br>
      <span style="margin-top:4px;display:inline-block;">{!! __('mcp::welcome.transport') !!}</span>
      <div class="lang-switch">
        @php $current = app()->getLocale(); @endphp
        <a href="?lang=en" @class(['active' => $current === 'en'])>EN</a>
        <a href="?lang=zh-cn" @class(['active' => $current === 'zh-cn'])>中文</a>
      </div>
    </div>
  </aside>

  <main class="main">
    <h1>{{ __('mcp::welcome.title') }} <span class="status-badge">{{ __('mcp::welcome.active') }}</span></h1>
    <p class="lead">{!! __('mcp::welcome.subtitle', ['name' => '<strong>'.$shopName.'</strong>']) !!}</p>

    <section id="overview">
      <h2>{{ __('mcp::welcome.overview_title') }}</h2>
      <div class="card">
        <p style="color:#6b7280;font-size:.9rem;">{!! __('mcp::welcome.overview_desc') !!}</p>
        <div style="margin-top:16px;display:flex;align-items:center;gap:10px;">
          <span style="font-weight:600;font-size:.85rem;">{{ __('mcp::welcome.endpoint_label') }}</span>
          <code style="font-size:.85rem;">POST {{ $mcpUrl }}</code>
        </div>
      </div>
    </section>

    <section id="connect">
      <h2>{{ __('mcp::welcome.connect_title') }}</h2>

      <div class="card">
        <h3>{{ __('mcp::welcome.cursor_title') }}</h3>
        <p style="font-size:.85rem;color:#6b7280;margin-bottom:10px;">{!! __('mcp::welcome.cursor_desc') !!}</p>
        <pre><code>{
  "mcpServers": {
    "innoshop": {
      "url": "{{ $mcpUrl }}",
      "headers": {
        "Authorization": "Bearer &lt;your-admin-token&gt;"
      }
    }
  }
}</code></pre>
      </div>

      <div class="card">
        <h3>{{ __('mcp::welcome.claude_code_title') }}</h3>
        <p style="font-size:.85rem;color:#6b7280;margin-bottom:10px;">{{ __('mcp::welcome.claude_code_desc') }}</p>
        <pre><code>claude mcp add --transport http innoshop {{ $mcpUrl }} \
  --header "Authorization: Bearer &lt;your-admin-token&gt;"</code></pre>
      </div>
    </section>

    <section id="auth">
      <h2>{{ __('mcp::welcome.auth_title') }}</h2>
      <div class="card">
        <p style="font-size:.85rem;color:#6b7280;margin-bottom:10px;">{{ __('mcp::welcome.auth_desc') }}</p>
        <pre><code>POST {{ $loginUrl }}
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "your-password"
}</code></pre>
      </div>
    </section>

    <section id="tools">
      <h2>{{ __('mcp::welcome.tools_title') }}</h2>
      <div class="card" style="padding:0;overflow:hidden;">
        <table>
          <thead>
            <tr>
              <th>{{ __('mcp::welcome.th_tool') }}</th>
              <th>{{ __('mcp::welcome.th_permission') }}</th>
              <th>{{ __('mcp::welcome.th_description') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($tools as $tool)
            <tr>
              <td><code>{{ $tool->name() }}</code></td>
              <td style="color:#6b7280;">{{ $tool->requiredPermission() }}</td>
              <td style="color:#6b7280;">{{ $tool->description() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
