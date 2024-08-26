@extends('panel::layouts.app')

@section('title', __('panel/menu.tax_classes'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.tax_classes') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $tax_class->id ? panel_route('tax_classes.update', [$tax_class->id]) : panel_route('tax_classes.store') }}"
      method="POST">
      @csrf
      @method($tax_class->id ? 'PUT' : 'POST')

      <div class="wp-500 m-auto">
        <x-common-form-input title="名称" name="name" value="{{ old('name', $tax_class->name) }}" required placeholder="名称" />
        <x-common-form-input title="描述" name="description" value="{{ old('description', $tax_class->description) }}" required placeholder="描述" />
        <x-panel::form.row title="规则" required>
          @php ($index = 0)
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>所属税率</th>
                <th>基于地址</th>
                <th>优先级</th>
                <th class="text-end">操作</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tax_rules as $index=>$rule)
                <tr>
                  <td>
                    <select class="form-select form-select-sm" name="tax_rules[{{ $index }}][tax_rate_id]" required>
                      @foreach($tax_rates as $rate)
                        <option value="{{ $rate->id }}" {{ ($rule->tax_rate_id == $rate->id)? 'selected' : '' }}>
                          {{ $rate->name }}({{ $rate->rate }})
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="form-select form-select-sm" name="tax_rules[{{ $index }}][based]" required>
                      @foreach($address_types as $type)
                        <option value="{{ $type['code'] }}" {{ ($rule->based == $type['code'])? 'selected' : '' }}>
                          {{ $type['label'] }}
                        </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="text" name="tax_rules[{{ $index }}][priority]" class="form-control form-control-sm"
                           value="{{ $rule->priority }}" placeholder="优先级">
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-tax">{{ __('panel/common.delete')}}</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4" class="text-end">
                  <button type="button" class="btn add-tax btn-sm btn-outline-primary">{{ __('panel/common.add') }}</button>
                </td>
              </tr>
            </tfoot>
          </table>
        </x-panel::form.row>
      </div>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>
  let index = @json($index);

  $(function () {
    $('.add-tax').click(function () {
      var html = `
        <tr>
          <td>
            <select class="form-select form-select-sm" name="tax_rules[${index}][tax_rate_id]" required>
              <option value="0">0%</option>
              <option value="5">5%</option>
              <option value="10">10%</option>
              <option value="15">15%</option>
              <option value="20">20%</option>
            </select>
          </td>
          <td>
            <select class="form-select form-select-sm" name="tax_rules[${index}][based]" required>
              <option value="price">价格</option>
              <option value="weight">重量</option>
            </select>
          </td>
          <td>
          <input type="text" name="tax_rules[{{ $index }}][priority]" class="form-control form-control-sm" value="0" placeholder="优先级">
          </td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-tax">{{ __('panel/common.delete')}}</button>
          </td>
        </tr>
      `;
      $('table tbody').append(html);
    });

    $(document).on('click', '.remove-tax', function () {
      $(this).closest('tr').remove();
    });
  });
</script>
@endpush