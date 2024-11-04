<div class="container">
  <div class="row">
    <div class="col-6">
      <x-common-form-select title="{{ __('panel/setting.ai_model') }}" name="ai_model"
                            :options="$ai_models" key="code" label="name" :emptyOption="false"
                            value="{{ old('ai_model', system_setting('ai_model')) }}" required />
    </div>
    <div class="col-6 d-flex align-items-center">
      <div class="form-group mt-4">
        <div class="d-flex flex-nowrap">
          <a class="btn btn-primary me-3" href="{{ panel_route('plugins.index', ['type'=>'intelli']) }}" target="_blank">
            {{ __('panel/common.setting') }}
          </a>
          <a class="btn btn-primary" href="{{ panel_route('plugin_market.index', ['type'=>'intelli']) }}" target="_blank">
            {{ __('panel/common.get_more') }}
          </a>
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
