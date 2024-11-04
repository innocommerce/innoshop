<div class="card variants-box mb-3" id="variants-box">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/attribute.attribute') }}</h5>
  </div>
  <div id="hide-attribute" class="card-body {{ $attribute_count ? '' : 'd-none'  }}">
    <table id="attributeTable" class="table table-condensed table-bordered">
      <tr>
        <td>{{ __('panel/attribute.attribute') }}</td>
        <td>{{ __('panel/attribute.attribute_value') }}</td>
        <td class="text-center align-middle">
          <a id="add-attribute" class="button btn-primary" href="javascript:void(0)">
            <i class="bi-plus-lg"></i>
          </a>
        </td>
      </tr>

      @if($product->productAttributes->count())
        @foreach($product->productAttributes as $index => $attr)
          <tr>
            <td class="col-5">
              <select class="form-control attribute-id" name="attributes[{{ $index }}][attribute_id]">
                @foreach($all_attributes as $item)
                  <option
                      value="{{ $item['id'] }}" {{ $attr->attribute_id == $item['id'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td class="col-5">
              <select class="form-control attribute-value-id" name="attributes[{{ $index }}][attribute_value_id]"
                      data-attr-value-id="{{ $attr->attribute_value_id }}">
                @foreach($all_attribute_values as $item)
                  <option
                      value="{{ $item['id'] }}" {{ $attr->attribute_value_id == $item['id'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                @endforeach
              </select>
            </td>
            <td class="col-2 text-center align-middle "><a type="button" class="btn-lg del-attr"><i
                    class="bi-trash"></i></a></td>
          </tr>
        @endforeach
      @else
        <tr>
          <td class="col-5">
            <select class="form-control attribute-id" name="attributes[0][attribute_id]">
              @foreach($all_attributes as $item)
                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
              @endforeach
            </select>
          </td>
          <td class="col-5">
            <select class="form-control attribute-value-id" name="attributes[0][attribute_value_id]"></select>
          </td>
          <td class="col-2 text-center align-middle "><a type="button" class="btn-lg del-attr"><i
                  class="bi-trash"></i></a></td>
        </tr>
      @endif

    </table>
  </div>

  <div class="cursor-pointer m-3 text-primary {{ $attribute_count ? 'd-none' : 'd-inline-block' }}" id="new-attribute">
    <i class="bi bi-plus-square me-1"></i> {{ __('panel/attribute.set_attribute') }}
  </div>
</div>

@push('footer')
  <script>
    $('#new-attribute').click(function () {
      $(this).addClass('d-none');
      $('#hide-attribute').removeClass('d-none');
    });
    $('#delete-all-attribute').click(function () {
      $('#hide-attribute').addClass('d-none');
    });

    let lineNo = {{ $attribute_count }};
    $(document).ready(function () {
      $('.attribute-id').trigger('change');
      $('#add-attribute').click(function () {
        let markup = '<tr>\
         <td class="col-5"><select class="form-control attribute-id" name="attributes[' + lineNo + '][attribute_id]"></select></td>\
         <td class="col-5"><select class="form-control attribute-value-id" name="attributes[' + lineNo + '][attribute_value_id]"></select></td>\
         <td class="col-2 text-center align-middle "><a type="button" class="btn-lg del-attr"><i class="bi-trash"></i></a></td>\
       </tr>';
        let tableBody = $("table tbody");
        tableBody.append(markup);

        let eleObj = $('select[name="attributes[' + lineNo + '][attribute_id]"]')
        setAttribute(eleObj);
        lineNo++;
      });
    });

    $(document).on('click', '.del-attr', function () {
      $(this).parent().parent().remove();
    });

    $(document).on('change', '.attribute-id', function () {
      let attributeEle = $(this);
      let attributeID = attributeEle.val();
      let attributeValueEle = attributeEle.parent().next().find('select');
      $.ajax({
        url: '{{ route('api.panel.attribute_values.index') }}',
        type: 'GET',
        data: {
          locale: '{{ panel_locale_code() }}',
          attribute_id: attributeID
        },
        success: function (response) {
          let options = '';
          $.each(response.data, function (index, value) {
            options += '<option value="' + value.id + '">' + value.name + '</option>';
          });
          attributeValueEle.html(options);

          let currentValueID = attributeValueEle.data('attr-value-id');
          if (currentValueID) {
            attributeValueEle.val(currentValueID);
          }
        }
      });
    });

    function setAttribute(eleObj) {
      $.ajax({
        url: '{{ route('api.panel.attributes.index') }}',
        type: 'GET',
        data: {
          locale: '{{ panel_locale_code() }}',
        },
        success: function (response) {
          let options = '';
          $.each(response.data, function (index, value) {
            options += '<option value="' + value.id + '">' + value.name + '</option>';
          });
          eleObj.html(options);
          eleObj.trigger('change');
        },
        error: function (error) {
          console.log('Error:', error);
        }
      });
    }
  </script>
@endpush