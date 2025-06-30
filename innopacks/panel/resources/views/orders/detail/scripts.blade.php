{{-- Scripts --}}
<script>
  $(document).ready(function() {
    // Admin comment input handler
    $('.admin-comment-input').on('keydown', function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        var comment = $(this).val();
        var orderId = $(this).data('order-id');
        var apiUrl = `${urls.api_base}/orders/${orderId}/notes`;
        axios.post(apiUrl, {
            admin_note: comment,
          })
          .then(function(res) {
            inno.msg(res.message);
            $('.admin-comment-input').val(res.data.admin_note);
            window.location.reload()
          })
      }
    });

    // Add shipment button handler
    $('#addRow').click(function() {
      $('#editModal').modal('show');
    });

    // Delete row handler
    $(document).on('click', '.deleteRow', function() {
      $(this).closest('tr').remove();
    });

    // View shipment details function
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

  // Submit comment function
  function submitComment() {
    let elment = $('.admin-comment-input');
    let comment = elment.val();
    let orderId = elment.data('order-id');
    let apiUrl = `${urls.api_base}/orders/${orderId}/notes`;
    axios.post(apiUrl, {
        admin_note: comment,
      })
      .then(function(res) {
        inno.msg(res.message);
        var admin_note = bootstrap.Modal.getInstance(document.getElementById('admin_note'));
        if (admin_note) {
          admin_note.hide();
        }
        $('.admin-comment-input').val(res.data.admin_note);
        window.location.reload();
      })
  }

  // Submit edit function
  function submitEdit() {
    const logisticsCompany = $('#logisticsCompany').val();
    const trackingNumber = $('#trackingNumber').val();
    const selectedCompanyName = $('#logisticsCompany option:selected').text();
    const orderId = {{ $order->id }};
    axios.post(`${urls.api_base}/orders/${orderId}/shipments`, {
      express_code: logisticsCompany,
      express_company: selectedCompanyName,
      express_number: trackingNumber,
    }).then(function(response) {
      inno.msg('{{ __('panel/order.add_successfully') }}');
      $('#editModal').modal('hide');
      window.location.reload();
    }).catch(function(res) {
      inno.msg('{{ __('panel/order.add_failed!') }}');
    });
  }

  // Delete shipment function
  function deleteShipment(shipmentId) {
    const apiUrl = `${urls.api_base}/shipments/${shipmentId}`;
    axios.delete(apiUrl)
      .then(function(response) {
        inno.msg('{{ __('panel/order.delete_successfully') }}');
        window.location.reload();
      })
  }

  // Vue.js status app
  const {
    createApp,
    ref
  } = Vue
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
        axios.put(api, {
          status: status,
          comment: comment.value
        }).then(() => {
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