@extends('panel::layouts.app')
@section('title', __('panel/menu.orders'))

@section('page-title-right')
  <div class="title-right-btns">
    <div class="status-wrap" id="status-app">
      @foreach($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach
      <a class="btn btn-success ms-2" href="{{ panel_route('orders.printing', $order) }}" target="_blank">{{
            panel_trans('order.print') }}</a>
      @hookinsert('panel.orders.info.print.after')
      <el-dialog v-model="statusDialog" title="{{ __('panel/order.status') }}" width="500">
        <div class="mb-2">{{ __('panel/order.comment') }}</div>
        <textarea v-model="comment" class="form-control" placeholder="{{ __('panel/order.comment') }}"
                  rows="3"></textarea>
        <template #footer>
          <div class="dialog-footer">
            <el-button @click="statusDialog = false">{{ __('panel/common.close') }}</el-button>
            <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
          </div>
        </template>
      </el-dialog>
    </div>
  </div>
@endsection

@section('content')
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.order_info') }}</h5>
    </div>
    <div class="card-body">
      <table class="table align-middle">
        <thead>
        <tr>
          <th>{{ __('panel/order.number') }}</th>
          <th>{{ __('panel/order.created_at') }}</th>
          <th>{{ __('panel/order.total') }}</th>
          <th>{{ __('panel/order.billing_method_code') }}</th>
          <th>{{ __('panel/order.shipping_method_code') }}</th>
          <th>{{ __('panel/common.status') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>{{ $order->number }}</td>
          <td>{{ $order->created_at }}</td>
          <td>{{ $order->total_format }}</td>
          <td>{{ $order->billing_method_code }}</td>
          <td>{{ $order->shipping_method_code }}</td>
          <td>{{ $order->status }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.order_items') }}</h5>
    </div>
    <div class="card-body">
      @hookupdate('panel.orders.info.order_items')
      <table class="table products-table align-middle">
        <thead>
        <tr>
          <th>{{ __('panel/common.id') }}</th>
          <th>{{ __('panel/order.product') }}</th>
          <th>{{ __('panel/order.sku_code') }}</th>
          <th>{{ __('panel/order.quantity') }}</th>
          <th>{{ __('panel/order.unit_price') }}</th>
          <th>{{ __('panel/order.subtotal') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($order->items as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>
              <div class="product-item d-flex align-items-center">
                <div class="product-image wh-40 border"><img src="{{ $item->image }}" class="img-fluid">
                </div>
                <div class="product-info ms-2">
                  <div class="name">{{ $item->name }}</div>
                  @if($item->productSku->variantLabel ?? '')
                    <span class="small fst-italic">{{ $item->productSku->variantLabel }}</span>
                  @endif
                </div>
              </div>
            </td>
            <td>{{ $item->product_sku }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->price_format }}</td>
            <td>{{ $item->subtotal_format }}</td>
          </tr>
        @endforeach
        @foreach ($order->fees as $total)
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong>{{ $total->title }}</strong></td>
            <td>{{ $total->value_format }}</td>
          </tr>
        @endforeach
        <tr>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td><strong>{{ __('panel/order.total') }}</strong></td>
          <td>{{ $order->total_format }}</td>
        </tr>
        </tbody>
      </table>
      @endhookupdate
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.address') }}</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-md-6">
          <div class="address-card">
            <div class="address-card-header mb-3">
              <h5 class="address-card-title">{{ __('panel/order.shipping_address') }}</h5>
            </div>
            <div class="address-card-body">
              <p>{{ $order->shipping_customer_name }}</p>
              <p>{{ $order->shipping_telephone }} {{ $order->shipping_zipcode }}</p>
              <p>{{ $order->shipping_address_1 }} {{ $order->shipping_address_2 }} </p>
              <p>{{ $order->shipping_city }} {{ $order->shipping_state }} {{ $order->shipping_country }}</p>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="address-card">
            <div class="address-card-header mb-3">
              <h5 class="address-card-title">{{ __('panel/order.billing_address') }}</h5>
            </div>
            <div class="address-card-body">
              <p>{{ $order->billing_customer_name }}</p>
              <p>{{ $order->billing_telephone }} {{ $order->billing_zipcode }}</p>
              <p>{{ $order->billing_address_1 }} {{ $order->billing_address_2 }} </p>
              <p>{{ $order->billing_city }} {{ $order->billing_state }} {{ $order->billing_country }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('front/checkout.order_comment') }}</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-6 mb-4">
            <h6 class="fs-5">{{ __('panel/order.customer_remarks') }}</h6>
            <p class="mb-0">{{ $order->comment }}</p>
          </div>
          <div class="col-12 col-md-6 mb-3">
            <h6 class="fs-5">{{ __('panel/order.administrator_remarks') }}</h6>
            <p class="mb-0">{{ $order->admin_note }}</p>
            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#admin_note">
              {{ __('panel/common.edit') }}
            </button>

            <div class="modal fade" id="admin_note" tabindex="-1" aria-labelledby="admin_noteLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border border-secondary rounded">
                  <div class="modal-header">
                    <h4 class="modal-title" id="admin_noteLabel">{{ __('panel/order.administrator_remarks') }}</h4>
                  </div>
                  <div class="modal-body">
                    <textarea class="form-control admin-comment-input" rows="5"
                              data-order-id="{{ $order->id }}">{{ $order->admin_note }}</textarea>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-bs-dismiss="modal">{{ __('panel/order.close') }}</button>
                    <button type="button" class="btn btn-primary"
                            onclick="submitComment()">{{ __('panel/order.submit') }}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('panel/order.shipping_information') }}</h5>
      <button class="btn btn-sm btn-primary mt-2" id="addRow">{{ __('panel/order.add') }}</button>
    </div>
    <div class="card-body">
      <table class="table table-response align-middle table-bordered" id="logisticsTable">
        <thead>
        <tr>
            <td>ID</td>
          <th>{{ __('panel/order.express_company') }}</th>
          <th>{{ __('panel/order.express_number') }}</th>
          <th>{{ __('panel/order.create_time') }}</th>
          <th>{{ __('panel/order.operation') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->shipments as $shipment)
          <tr>
            <td data-title="id">{{ $shipment->id }}</td>
            <td data-title="express_company">{{ $shipment->express_company }}</td>
            <td data-title="express_number">{{ $shipment->express_number }}</td>
            <td data-title="created_at">{{ $shipment->created_at }}</td>
            <td>
              <button class="btn btn-sm btn-primary deleteRow" onclick="deleteShipment('{{ $shipment->id }}')">{{ __('panel/order.delete') }}</button>
              <button class="btn btn-sm btn-primary viewRow" onclick="viewShipmentDetails('{{ $shipment->id }}')">{{ __('panel/order.view') }}</button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/order.history') }}</h5>
    </div>
    <div class="card-body">
      <table class="table table-response align-middle">
        <thead>
        <tr>
          <th>{{ __('panel/order.status') }}</th>
          <th>{{ __('panel/order.comment') }}</th>
          <th>{{ __('panel/order.date_time') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->histories as $history)
          <tr>
            <td>{{ $history->status }}</td>
            <td>{{ $history->comment }}</td>
            <td>{{ $history->created_at }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal fade" id="newShipmentModal" tabindex="-1" aria-labelledby="newShipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="newShipmentModalLabel">{{ __('panel/order.shipment_information') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table">
            <tbody>
              <tr>
                <th class="col-3">{{ __('panel/order.time') }}</th>
                <th class="col-9">{{ __('panel/order.logistics_information') }}</th>
              </tr>
            </tbody>
            <tbody>
            </tbody>    
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('panel/order.confirm') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editModal" tabindex="-1" aria-bs-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">{{ __('panel/order.edit_logistics_information') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="logisticsCompany" class="form-label">{{ __('panel/order.express_company') }}</label>
              <select class="form-control" id="logisticsCompany">
                @foreach(system_setting('logistics', []) as $expressCompany)
                  <option value="{{ $expressCompany['code'] }}">{{ $expressCompany['company'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="trackingNumber" class="form-label">{{ __('panel/order.express_number') }}</label>
              <input type="text" class="form-control" id="trackingNumber">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('panel/order.close') }}</button>
          <button type="button" class="btn btn-primary" onclick="submitEdit()">{{ __('panel/order.save_changes') }}</button>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('footer')
  <script>
    var admin_note = new bootstrap.Modal(document.getElementById('admin_note'));
    document.querySelector('[data-bs-toggle="modal"]').addEventListener('click', function () {
      admin_note.show();
    });

    $(document).ready(function () {
        
      $('.admin-comment-input').on('keydown', function (event) {
        if (event.keyCode === 13) {
          event.preventDefault();
          var comment = $(this).val();
          var orderId = $(this).data('order-id');
          var apiUrl = `${urls.api_base}/orders/${orderId}/notes`;
          axios.post(apiUrl, {
            admin_note: comment,
          })
            .then(function (res) {
              inno.msg(res.message);
              $('.admin-comment-input').val(res.data.admin_note);
              window.location.reload()
            })
        }
      });

      $('#addRow').click(function() {
        $('#editModal').modal('show');
      });

      $(document).on('click', '.deleteRow', function() {
        $(this).closest('tr').remove();
      });

      window.viewShipmentDetails = function(shipmentId) {
        axios.get(`${urls.api_base}/shipments/${shipmentId}/traces`)
            .then(function(response) {
                if (response.data && response.data.traces) {
                    const tbody = $('#newShipmentModal .modal-body table tbody').last();
                    tbody.empty();
                    response.data.traces.forEach(trace => {
                        const row = `<tr>
                            <td>${trace.time}</td>
                            <td>${trace.station}</td>
                         </tr>`;
                        tbody.append(row);
                    });
                    var newShipmentModal = new bootstrap.Modal(document.getElementById('newShipmentModal'));
                    newShipmentModal.show();
                } 
            })
            .catch(function(error) {
                inno.msg('{{ __('panel/order.no_logistics_information') }}');
            });
      }
    });

    function submitComment() {
      var comment = $('.admin-comment-input').val();
      var orderId = $('.admin-comment-input').data('order-id');
      var apiUrl = `${urls.api_base}/orders/${orderId}/notes`;
      axios.post(apiUrl, {
        admin_note: comment,
      })
        .then(function (res) {
          inno.msg(res.message);
          var admin_note = bootstrap.Modal.getInstance(document.getElementById('admin_note'));
          if (admin_note) {
            admin_note.hide();
          }
          $('.admin-comment-input').val(res.data.admin_note);
          window.location.reload();
        })
    }

    function submitEdit() {
      const logisticsCompany = $('#logisticsCompany').val();
      const trackingNumber = $('#trackingNumber').val();
      const selectedCompanyName = $('#logisticsCompany option:selected').text();
      const orderId = {{ $order->id }};
      axios.post(`${urls.api_base}/orders/${orderId}/shipments`, {
        express_code: logisticsCompany,
        express_company: selectedCompanyName,
        express_number: trackingNumber,
      })
        .then(function(response) {
          inno.msg('{{ __('panel/order.add_successfully') }}');
          $('#editModal').modal('hide');
          window.location.reload();
        }).catch(function(res) {
          inno.msg('{{ __('panel/order.add_failed!') }}');
        });
    }

    function deleteShipment(shipmentId) {
      const apiUrl = `${urls.api_base}/shipments/${shipmentId}`;
      axios.delete(apiUrl)
        .then(function(response) {
          inno.msg('{{ __('panel/order.delete_successfully') }}');
          window.location.reload();
        })
    }

    const {createApp, ref} = Vue
    const api = @json(panel_route('orders.change_status', $order));
    const statusApp = createApp({
      setup() {
        const statusDialog = ref(false)
        const comment = ref('')
        let status = '';

        const edit = (code) => {
          statusDialog.value = true
          status = code
        }

        const submit = () => {
          axios.put(api, {status: status, comment: comment.value}).then(() => {
            statusDialog.value = false
            window.location.reload()
          })
        }

        return {
          edit,
          submit,
          comment,
          statusDialog,
        }
      }
    })
    statusApp.use(ElementPlus);
    statusApp.mount('#status-app');
  </script>
@endpush
