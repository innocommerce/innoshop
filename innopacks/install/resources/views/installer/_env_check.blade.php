<table class="table">
  <thead>
  <tr>
    <th colspan="3" class="bg-light">{{ __('install/common.env_detection') }}</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>{{ __('install/common.environment') }}</td>
    <td>{{ __('install/common.current') }}</td>
    <td>{{ __('install/common.status') }}</td>
  </tr>
  <tr>
    <td>{{ __('install/common.php_version') }}(8.2+)</td>
    <td>{{ $php_version }}</td>
    <td><i
          class="bi {{ $php_env ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
    </td>
  </tr>
  @foreach($extensions as $key => $value)
    <tr>
      <td>{{ $key }}</td>
      <td></td>
      <td><i
            class="bi {{ $value ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
      </td>
    </tr>
  @endforeach

  </tbody>

  <thead>
  <tr>
    <th colspan="3" class="bg-light">{{ __('install/common.perm_detection') }}</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>{{ __('install/common.dir_file') }}</td>
    <td>{{ __('install/common.config') }}</td>
    <td>{{ __('install/common.status') }}</td>
  </tr>
  @foreach($permissions as $key => $value)
    <tr>
      <td>{{ $key }}</td>
      <td>755</td>
      <td><i
            class="bi {{ $value ? 'text-success bi-check-circle-fill' : 'bi-x-circle-fill text-danger' }}"></i>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>