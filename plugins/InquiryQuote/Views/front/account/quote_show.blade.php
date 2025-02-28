@extends('layouts.app')
@section('body-class', 'page-order-info')
 
@section('content')

 <x-front-breadcrumb type="route" value="account.quotes.index" title="{{ __('InquiryQuote::quote.quotes') }}" />

 <div class="container">
  <div class="row">
   <div class="col-12 col-lg-3">
    @include('shared.account-sidebar')
   </div>
   <div class="col-12 col-lg-9">
    <div class="account-card-box order-info-box">
     <div class="account-card-title d-flex justify-content-between align-items-center">
      <span class="fw-bold">询价详情</span>
     </div>
     <table class="table table-bordered table-striped mb-3 table-response">
      <thead>
       <tr>
        <th>订单编号</th>
        <th>日期:</th>
        <th>总额:</th>
        <th>状态:</th>
       </tr>
      </thead>
      <tbody>
       <tr>
        <td data-title="Order ID">{{ $quote['number'] }}</td>
        <td data-title="Order Date">{{ $quote['created_at'] }}</td>
        <td data-title="Order Total">{{ $quote['total_format'] }}</td>
        <td data-title="Order Status">{{ $quote['status_format'] }}</td>
       </tr>
      </tbody>
     </table>
     <table class="table">
      <thead>
       <tr>
        <th scope="col">产品名称</th>
        <th scope="col">原价</th>
        <th scope="col">期望价格</th>
        <th scope="col">数量</th>
        <th scope="col">总计</th>
       </tr>
      </thead>
      <tbody>
       @foreach ($inquiry_list as $item)
        @foreach ($item['inquiries'] as $inquiry)
         <tr data-id="{{ $inquiry['id'] }}" data-sku-code="{{ $inquiry['sku_code'] }}">
          <td class="align-middle">
           <div class="d-flex gap-2 align-items-center">
            <img class="wh-50" src="{{ $inquiry['image'] }}" />
            <div>
             <div>{{ $inquiry['product_name'] }}</div>
             <div>{{ $inquiry['sku_code'] }}</div>
            </div>
           </div>
          </td>
          <td class="align-middle">{{ $inquiry['origin_price_format'] }}</td>
          <td class="align-middle">
           <div class="quantity-wrap price-view">
            <input type="number" class="form-control price fs-6" value="{{ $inquiry['inquiry_price'] ?? 0 }}">
           </div>
          </td>
          <td class="align-middle">
           <div class="quantity-wrap number-view">
            <div class="minus"><i class="bi bi-dash-lg"></i></div>
            <input type="number" class="form-control number fs-6" value="{{ $inquiry['quantity'] ?? 1 }}">
            <div class="plus"><i class="bi bi-plus-lg"></i></div>
           </div>
          </td>
          <td class="align-middle">{{ $inquiry['inquiry_subtotal_format'] }}</td>
         </tr>
        @endforeach
       @endforeach
      </tbody>
      @foreach ($quote_fees as $item)
       <tbody>
        <tr>
         <td class="align-middle"></td>
         <td class="align-middle"></td>
         <td class="align-middle"></td>
         <td class="align-middle">{{ $item['label'] }}({{ $item['origin_amount_format'] }})</td>
         @if ($item['code'] !== 'subtotal')
          <td id="{{ $item['code'] }}" class="align-middle"><input type="text" class="form-control"
            value="{{ $item['inquiry_amount'] }}"></td>
         @else
          <td>{{ $item['inquiry_amount'] }}</td>
         @endif
        </tr>
       </tbody>
      @endforeach
      <tbody>
       <tr>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class=" align-middle d-flex justify-content-center">
         <button type="button" data-quote-id="{{ $quote['id'] }}" class="btn btn-primary submit">提交</button>
        </td>
        <td class="align-middle"></td>
       </tr>
      </tbody>
     </table>
     <div class="account-card-sub-title d-flex justify-content-between align-items-center">
      <span class="fw-bold">询价备注</span>
     </div>
     <div class="checkout-select">
      <textarea class="form-control" rows="4" id="orderComment" placeholder="{{ __('front/checkout.order_comment') }}"></textarea>
     </div>
     <div class="mt-3 d-flex justify-content-end">
      <button type="button" id="submitButton" data-quote-id="{{ $quote['id'] }}"
       class="btn btn-primary m-3">提交</button>
     </div>
     <div class="table-responsive border-top">
      <table class="table table-response mt-3">
       <thead>
        <tr>
         <th>{{ __('front/order.order_status') }}</th>
         <th>{{ __('front/order.remark') }}</th>
         <th>{{ __('front/order.order_date') }}</th>
        </tr>
       </thead>
       <tbody>

        @foreach ($quote->histories as $history)
         <tr>
          <td class="w-50" data-title="State">{{ $history->status_format }}</td>
          <td data-title="Remark">{{ $history->comment }}</td>
          <td data-title="Update Time">{{ $history->created_at }}</td>
         </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    </div>

   </div>
  </div>
 </div>
@endsection

@push('footer')
 <script>
  $('.submit').on('click', function() {
   const quoteId = $(this).data('quote-id');
   const shipping = $('#shipping input').val();
   const handling = $('#handling input').val();
   const tax = $('#tax input').val();
   const inquiries = [];
   $('table.table tbody tr').each(function() {
    const row = $(this);
    const sku_code = row.data('sku-code');
    const quantity = parseFloat(row.find('.number-view .number').val());
    const inquiry_price = parseFloat(row.find('.price-view .price').val());
    if (!isNaN(quantity) && !isNaN(inquiry_price) && quantity > 0) {
     inquiries.push({
      sku_code,
      quantity,
      inquiry_price,
     });
    }
   });
   const shipping_address_id = '{{ $quote['shipping_address_id'] }}';
   const shipping_method_code = '{{ $quote['shipping_method_code'] }}';
   const based = '{{ $quote['based'] }}';


   console.log(inquiries);
   axios.put(`${urls.api_base}/quotes/${quoteId}`, {
     fees:{shipping, handling,tax},
     based,
     inquiries,
     shipping_address_id,
     shipping_method_code
    })
    .then(function(res) {
     inno.msg(res.message);
     window.location.reload();
    })
    .catch(function(err) {
     console.error(err);
     inno.msg(err.response.data.message || 'Error occurred');
    });
  });
  //  $('#submitButton').click(function() {
  // var comment = $('#orderComment').val();
  // var quoteId = $(this).data('quote-id');
  // axios.post(`${urls.api_base}/quotes/${quoteId}/histories`, {
  // comment,
  // })
  // .then(function(res) {
  // inno.msg(res.message);
  // window.location.reload();
  // })
  // .catch(function(err) {
  // console.error(err);
  // inno.msg(err.response.data.message || 'Error occurred');
  // })
  // });
  $(document).ready(function() {
   $('.number-view input').on('change', function() {
    const newValue = $(this).val();
    if (!isNaN(newValue) && newValue > 0) {
     updateCarts($(this).closest('tr').data('id'), newValue);
    } else {
     inno.msg('Please enter a valid quantity greater than 0.');
    }
   });
  });
  $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function() {
   const quantity = parseInt($(this).siblings('input').val());
   if ($(this).hasClass('plus')) {
    $(this).siblings('input').val(quantity + 1);
   } else {
    if (quantity > 1) {
     $(this).siblings('input').val(quantity - 1);
    }
   }
  });
  $('.price-view input').on('change', function() {});
  $('.delete-cart').on('click', function() {
   const id = $(this).closest('tr').data('id');
   updateCarts(id, 0, 'delete');
  });
  $('.to-checkout').on('click', function(e) {
   e.preventDefault();
   const ids = [];
   $('.product-item-check:checked').each(function() {
    ids.push($(this).val());
   });

   if (!ids.length) {
    inno.msg('Please select the product to checkout!');
    return false;
   }
   window.location.href = $(this).attr('href');
  });
  $(document).ready(function() {
   $('#submitButton').click(function() {
    var comment = $('#orderComment').val();
    var quoteId = $(this).data('quote-id');
    axios.post(`${urls.api_base}/quotes/${quoteId}/histories`, {
      comment,
     })
     .then(function(res) {
      inno.msg(res.message);
      window.location.reload();
     })
     .catch(function(err) {
      console.log(err.response.data.message)
     })
   });
  });
 </script>
@endpush
