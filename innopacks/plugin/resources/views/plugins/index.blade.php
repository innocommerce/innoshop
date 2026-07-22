@extends('panel::layouts.app')
@section('body-class', 'page-my-plugins')

@section('title', $type ? __('panel/plugin.'.$type) : __('panel/plugin.all'))

@section('page-title-right')
  <a href="{{ panel_route('plugin-market.index') }}" class="btn btn-primary">{{ __('panel/common.get_more') }}</a>
@endsection

@section('content')
  @if (config('app.debug'))
    @php
      $cacheStatus = \InnoShop\Plugin\Core\PluginManager::getLastCacheStatus();
      $cacheStatusMap = [
          'hit'           => ['label' => 'HIT',                          'class' => 'text-success'],
          'miss_no_data'  => ['label' => 'MISS · no cached data',       'class' => 'text-warning'],
          'miss_stale'    => ['label' => 'MISS · config.json changed',  'class' => 'text-warning'],
          'skipped_debug' => ['label' => 'SKIPPED · APP_DEBUG=true',    'class' => 'text-secondary'],
      ];
      $cacheStatusInfo = $cacheStatusMap[$cacheStatus] ?? null;
    @endphp
    @if ($cacheStatusInfo)
      <div class="alert alert-light border py-2 mb-3 small d-flex align-items-center gap-2">
        <span class="font-monospace text-muted">plugins.config cache:</span>
        <span class="fw-bold {{ $cacheStatusInfo['class'] }}">{{ $cacheStatusInfo['label'] }}</span>
        <span class="text-muted">· key: <code>{{ \InnoShop\Plugin\Core\PluginManager::PLUGINS_CONFIG_CACHE_KEY }}</code></span>
      </div>
    @endif
  @endif
  <div class="card h-min-600">
    <div class="card-body">

      <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link {{ !$type ? 'active' : '' }}" 
             href="{{ panel_route('plugins.index') }}">
            {{ __('panel/plugin.all') }}
            <span class="badge ms-1" style="font-size: 0.7em; font-weight: normal; background-color: #e9ecef; color: #6c757d;">{{ $typeCounts['all'] ?? 0 }}</span>
          </a>
        </li>
        @foreach($types as $pluginType)
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ $type === $pluginType ? 'active' : '' }}" 
               href="{{ panel_route('plugins.index', ['type' => $pluginType]) }}">
              {{ __('panel/plugin.'.$pluginType) }}
              <span class="badge ms-1" style="font-size: 0.7em; font-weight: normal; background-color: #e9ecef; color: #6c757d;">{{ $typeCounts[$pluginType] ?? 0 }}</span>
            </a>
          </li>
        @endforeach
      </ul>

      <div class="row">
        @if (count($plugins))
        @foreach ($plugins as $plugin)
          <div class="col-6 col-md-3 mb-4">
            <div class="plugin-item" data-code="{{ $plugin['code'] }}"
                 data-installed="{{ $plugin['installed'] ? 1 : 0 }}">
              <div class="image-wrap">
                <div class="image"><img src="{{ $plugin['icon'] }}" alt="{{ $plugin['name'] }}" class="img-fluid"></div>
                <div class="title-wrap">
                  <div class="title">{{ $plugin['name'] }}</div>
                  <div class="plugin-meta">
                    <span class="font-monospace">{{ $plugin['code'] }}</span>
                    @if(!empty($plugin['version']))
                      <span class="plugin-meta-dot">·</span>
                      <span>{{ $plugin['version'] }}</span>
                    @endif
                  </div>
                </div>
              </div>

              <div class="plugin-info">
                <div class="description" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $plugin['description'] }}">{{ $plugin['description'] }}</div>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="version">
                    <div class="d-flex align-items-center">
                      <div class="form-switch plugin-enabled-switch cursor-pointer">
                        <input class="form-check-input" type="checkbox"
                               {{ !$plugin['installed'] ? 'disabled' : '' }} role="switch" {{ $plugin['enabled'] ? 'checked' : '' }}>
                      </div>
                    </div>
                  </div>
                  <div class="btns">
                    @if ($plugin['installed'])
                      @if($plugin['menu_url'])
                        <a href="{{ $plugin['menu_url'] }}" target="_blank" class="btn btn-success btn-sm">{{ __('common/base.use') }}</a>
                      @endif
                      @if($plugin['edit_url'])
                        <a href="{{ $plugin['edit_url'] }}" class="btn btn-primary btn-sm">{{ __('panel/common.setting') }}</a>
                      @endif
                      <div class="btn btn-warning btn-sm reset-plugin" data-code="{{ $plugin['code'] }}">{{ __('panel/common.reset') }}</div>
                      <div class="btn btn-danger btn-sm uninstall-plugin">{{ __('panel/common.uninstall') }}</div>
                    @else
                      <div class="btn btn-primary btn-sm install-plugin">{{ __('panel/common.install') }}</div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
        @else
          <x-common-no-data />
        @endif
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    var pluginLabels = {
      use: '{{ __("common/base.use") }}',
      setting: '{{ __("panel/common.setting") }}',
      reset: '{{ __("panel/common.reset") }}',
      uninstall: '{{ __("panel/common.uninstall") }}',
      install: '{{ __("panel/common.install") }}'
    };

    $(function () {
      $(document).on('click', '.install-plugin', function () {
        var $item = $(this).closest('.plugin-item');
        var code = $item.data('code');
        pluginsUpdate($item, code, 'install');
      });

      $(document).on('click', '.uninstall-plugin', function () {
        var $item = $(this).closest('.plugin-item');
        var code = $item.data('code');
        pluginsUpdate($item, code, 'uninstall');
      });

      $(document).on('click', '.reset-plugin', function () {
        var $item = $(this).closest('.plugin-item');
        var code = $item.data('code');
        if (!confirm(pluginLabels.reset + '?')) return;
        var $btn = $(this);
        $btn.prop('disabled', true);
        axios.post('/{{ panel_name() }}/plugins/reset', {code: code}).then(function (res) {
          $btn.prop('disabled', false);
          if (res && res.success) {
            layer.msg(res.message, { icon: 1 });
          } else {
            inno.alert((res && res.message) || '{{ __("common/base.error") }}');
          }
        }).catch(function (error) {
          $btn.prop('disabled', false);
          var data = error.response && error.response.data;
          layer.msg((data && data.message) || '{{ __("common/base.error") }}', { icon: 2 });
        });
      });
    });

    $(document).on('change', '.plugin-enabled-switch input', function () {
      var $item = $(this).closest('.plugin-item');
      var code = $item.data('code');
      var enabled = $(this).prop('checked') ? 1 : 0;
      var $switch = $(this);
      axios.post('/{{ panel_name() }}/plugins/enabled', {code: code, enabled: enabled}).then(function (res) {
        if (res.success) {
          updatePluginCard($item, res.data);
          layer.msg(res.message, { icon: 1 });
        } else {
          $switch.prop('checked', !enabled);
          inno.alert(res.message || '{{ __("common/base.error") }}');
        }
      }).catch(function () {
        $switch.prop('checked', !enabled);
      });
    });

    function updatePluginCard($item, plugin) {
      var code = $item.data('code');
      $item.attr('data-installed', plugin.installed ? 1 : 0);
      var $btns = $item.find('.btns');
      var $switch = $item.find('.plugin-enabled-switch input');

      if (plugin.installed) {
        var html = '';
        if (plugin.menu_url) {
          html += '<a href="' + plugin.menu_url + '" target="_blank" class="btn btn-success btn-sm">' + pluginLabels.use + '</a> ';
        }
        if (plugin.edit_url) {
          html += '<a href="' + plugin.edit_url + '" class="btn btn-primary btn-sm">' + pluginLabels.setting + '</a> ';
        }
        html += '<div class="btn btn-warning btn-sm reset-plugin" data-code="' + code + '">' + pluginLabels.reset + '</div> ';
        html += '<div class="btn btn-danger btn-sm uninstall-plugin">' + pluginLabels.uninstall + '</div>';
        $btns.html(html);
        $switch.prop('disabled', false).prop('checked', !!plugin.enabled);
      } else {
        $btns.html('<div class="btn btn-primary btn-sm install-plugin">' + pluginLabels.install + '</div>');
        $switch.prop('disabled', true).prop('checked', false);
      }
    }

    function pluginsUpdate($item, code, type) {
      var url = type === 'install' ? '/{{ panel_name() }}/plugins' : '/{{ panel_name() }}/plugins/' + code;
      var method = type === 'install' ? 'post' : 'delete';

      axios[method](url, {code: code}).then(function (res) {
        if (res.success) {
          updatePluginCard($item, res.data);
          layer.msg(res.message, { icon: 1 });
        } else {
          inno.alert(res.message || '{{ __("common/base.error") }}');
        }
      }).catch(function (error) {
        var data = error.response && error.response.data;
        layer.msg((data && data.message) || '{{ __("common/base.error") }}', { icon: 2 });
      });
    }
  </script>
@endpush
