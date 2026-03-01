@extends('panel::layouts.app')
@section('body-class', 'page-newsletter-subscribers')

@section('title', __('panel/newsletter.subscribers'))
@section('page-title-right')
  <div class="d-flex gap-2">
    <span class="badge bg-success">{{ __('panel/newsletter.total') }}: {{ $subscribers->total() }}</span>
    <span class="badge bg-primary">{{ __('panel/newsletter.active') }}: {{ \InnoShop\Common\Repositories\NewsletterRepo::getInstance()->getActiveCount() }}</span>
  </div>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">
      <x-panel-data-data-search
        :action="panel_route('newsletter_subscribers.index')"
        :searchFields="$searchFields ?? []"
        :filters="$filterButtons ?? []"
        :enableDateRange="false"
      />

      @if ($subscribers->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <td>{{ __('common/base.id') }}</td>
                <td>{{ __('panel/newsletter.email') }}</td>
                <td>{{ __('panel/newsletter.name') }}</td>
                <td>{{ __('panel/newsletter.customer') }}</td>
                <td>{{ __('panel/newsletter.status') }}</td>
                <td>{{ __('panel/newsletter.source') }}</td>
                <td>{{ __('panel/newsletter.subscribed_at') }}</td>
                <td>{{ __('panel/common.actions') }}</td>
              </tr>
            </thead>
            <tbody>
              @foreach($subscribers as $subscriber)
                <tr>
                  <td>{{ $subscriber->id }}</td>
                  <td>
                    <a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a>
                  </td>
                  <td>{{ $subscriber->name ?? '-' }}</td>
                  <td>
                    @if($subscriber->customer)
                      <a href="{{ panel_route('customers.edit', $subscriber->customer_id) }}">
                        {{ $subscriber->customer->name }}
                      </a>
                    @else
                      -
                    @endif
                  </td>
                  <td>
                    @if($subscriber->status === \InnoShop\Common\Models\NewsletterSubscriber::STATUS_ACTIVE)
                      <span class="badge bg-success">{{ __('panel/newsletter.status_active') }}</span>
                    @elseif($subscriber->status === \InnoShop\Common\Models\NewsletterSubscriber::STATUS_UNSUBSCRIBED)
                      <span class="badge bg-secondary">{{ __('panel/newsletter.status_unsubscribed') }}</span>
                    @else
                      <span class="badge bg-danger">{{ __('panel/newsletter.status_bounced') }}</span>
                    @endif
                  </td>
                  <td>
                    @if($subscriber->source)
                      <span class="badge bg-info">{{ __('panel/newsletter.source_' . $subscriber->source) }}</span>
                    @else
                      -
                    @endif
                  </td>
                  <td>{{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('Y-m-d H:i') : '-' }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      @if($subscriber->status === \InnoShop\Common\Models\NewsletterSubscriber::STATUS_UNSUBSCRIBED)
                        <form action="{{ panel_route('newsletter_subscribers.resubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('PUT')
                          <el-button size="small" plain type="success" native-type="submit">{{ __('panel/newsletter.resubscribe') }}</el-button>
                        </form>
                      @else
                        <form action="{{ panel_route('newsletter_subscribers.unsubscribe', $subscriber->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('PUT')
                          <el-button size="small" plain type="warning" native-type="submit">{{ __('panel/newsletter.unsubscribe') }}</el-button>
                        </form>
                      @endif
                      <form ref="deleteForm" action="{{ panel_route('newsletter_subscribers.destroy', $subscriber->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <el-button size="small" type="danger" plain @click="open({{ $subscriber->id }})">
                          {{ __('common/base.delete') }}
                        </el-button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{ $subscribers->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
    const { createApp, ref } = Vue;
    const { ElMessageBox, ElMessage } = ElementPlus;

    const app = createApp({
      setup() {
        const deleteForm = ref(null);

        const open = (itemId) => {
          ElMessageBox.confirm(
            '{{ __("common/base.hint_delete") }}',
            '{{ __("common/base.cancel") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm") }}',
              cancelButtonText: '{{ __("common/base.cancel") }}',
              type: 'warning',
            }
          )
          .then(() => {
            const deleteUrl = urls.panel_base + '/newsletter-subscribers/' + itemId;
            deleteForm.value.action = deleteUrl;
            deleteForm.value.submit();
          })
          .catch(() => {
            // 取消删除
          });
        };

        return { open, deleteForm };
      }
    });

    app.use(ElementPlus);
    app.mount('#app');
  </script>
@endpush
