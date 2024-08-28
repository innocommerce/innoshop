<div class="container">
  <div class="row">
    <div class="col-6">
      <x-common-form-select title="{{ __('panel/setting.ai_model') }}" name="email_engine"
                            :options="$mail_engines" key="code" label="name" :emptyOption="false"
                            value="{{ old('ai_model', system_setting('ai_model')) }}" required
                            placeholder="{{ __('panel/setting.email_engine') }}"/>
    </div>
    <div class="col-6 d-flex align-items-center">
      <div class="form-group mt-4">
        <div class="d-flex flex-nowrap">
          <a class="btn btn-primary me-1">设置 {{ system_setting('ai_model') }}</a>
          <a class="btn btn-primary">获取更多</a>
        </div>
      </div>
    </div>
  </div>

  @foreach($ai_prompts as $prompt)
    <x-common-form-textarea title="{{ __('panel/setting.'.$prompt) }}" name="{{ $prompt }}"
                            value="{{ old($prompt, system_setting($prompt)) }}"
                            placeholder="{{ __('panel/setting.'.$prompt) }}"/>
  @endforeach
</div>
