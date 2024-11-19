@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.order_returns.index" title="{{ __('front/account.order_returns') }}" />

  @hookinsert('account.order_return_index.top')

    <div class="container">
     <div class="row">
       <div class="col-12 col-lg-3">
         @include('shared.account-sidebar')
       </div>
       <div class="col-12 col-lg-9">
         <div class="account-card-box order-box">
           <div class="account-card-title d-flex justify-content-between align-items-center">
             <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
           </div>

           @if ($order_returns->count())
             <table class="table table-bordered ">
               <thead>
               <tr>
                 <th>{{ __('front/return.number') }}</th>
                 <th>{{ __('front/order.order_number') }}</th>
                 <th>{{ __('front/return.product_name') }}</th>
                 <th>{{ __('front/return.quantity') }}</th>
                 <th>{{ __('front/common.created_at') }}</th>
                 <th>{{ __('front/common.status') }}</th>
                 <th>{{ __('front/common.action') }}</th>
               </tr>
               </thead>
               <tbody>
               @foreach($order_returns as $item)
                 <tr>
                   <td class="align-middle" data-title="{{ __('front/return.return_number') }}">{{ $item->number }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_number') }}">{{ $item->order_number }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_date') }}">{{ $item->product_name }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.quantity') }}">{{ $item->quantity }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_date') }}">{{ $item->created_at }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_status') }}">{{ $item->status_format }}</td>
                   <td class="align-middle" data-title="{{ __('front/common.action') }}">
                    <a  href="{{ account_route('order_returns.show', ['order_return'=>$item->id]) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                   </td>
                 </tr>
               @endforeach
               </tbody>
             </table>
           @else
             <x-common-no-data />
           @endif
         </div>
       </div>
     </div>
   </div>

  @hookinsert('account.order_return_index.bottom')

@endsection