@section('page-title-right')
<div class="title-right-btns">
  <button type="button" class="btn btn-primary submit-form" form="{{ $formid ?? 'app-form' }}">{{ __('panel/common.btn_submit') }}</button>
  <button type="button" class="btn btn-outline-secondary ms-2 btn-back" onclick="window.history.back()">{{ __('panel/common.btn_back') }}</button>
</div>
@endsection