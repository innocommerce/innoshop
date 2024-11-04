@extends('layouts.app')
@section('body-class', 'page-review')

@section('content')
  <x-front-breadcrumb type="route" value="account.reviews.index" title="{{ __('front/account.reviews') }}"/>

  @hookinsert('account.review_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box review-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/review.review') }}</span>
          </div>

          @if ($reviews->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('panel/review.product') }}</th>
                <th>{{ __('front/review.rating') }}</th>
                <th>{{ __('front/review.review_content') }}</th>
                <th>{{ __('front/common.date') }}</th>
                <th>{{ __('panel/common.actions') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($reviews as $review)
                <tr class="review-card-actions" data-id="{{ $review->id }}">
                  <td data-title="Product" data-bs-toggle="tooltip"
                  title="{{ sub_string($review->product->translation->name, 30) }}">
                    <img src="{{ $review->product->image_url }}" alt="{{ $review->product->name }}" class="img-fluid wh-30">
                    {{ sub_string($review->product->translation->name, 12) }}
                  </td>
                  <td data-title="Rating"><x-front-review :rating="$review['rating']"/></td>
                  <td data-title="content" data-bs-toggle="tooltip"
                      title="{{ $review->content }}">{{ sub_string($review->content, 12)}}</td>
                  <td data-title="Date">{{ $review->created_at->format('Y-m-d') }}</td>
                  <td data-title="Actions">
                    <button type="button" class="btn delete-review btn-sm btn-outline-danger"
                            data-url="{{ account_route('reviews.destroy', $review->id) }}">{{ __('front/common.delete') }}</button>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>

            {{ $reviews->links('panel::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.review_index.bottom')

@endsection

@push('footer')
  <script>
    $('.delete-review').on('click', function () {
      const url = $(this).data('url');
      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: ['{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}']
      }, function () {
        axios.delete(url).then(function (res) {
          if (res.success) {
            layer.msg(res.message, {icon: 1, time: 1000}, function () {
              window.location.reload()
            });
          }
        })
      });
    });
  </script>
@endpush
