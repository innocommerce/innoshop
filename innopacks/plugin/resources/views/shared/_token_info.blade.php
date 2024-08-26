@section('page-title-right')
    @if(system_setting('domain_token'))
        <span class="text-success mx-2 align-middle">{{ __('panel/plugin.has_token') }}</span>
        <button type="button" data-bs-toggle="modal" data-bs-target="#modalBindToken" class="btn btn-primary submit-form d-inline-block align-middle">{{ __('panel/plugin.get_token') }}</button>
    @else
        <div class="title-right-btns">
            <span class="text-danger mx-2 align-middle">{{ __('panel/plugin.need_bind_token') }}</span>
            <button type="button" data-bs-toggle="modal" data-bs-target="#modalBindToken" class="btn btn-primary submit-form d-inline-block align-middle">{{ __('panel/plugin.get_token') }}</button>
        </div>
    @endif

    <!-- Modal -->
    <div class="modal fade" id="modalBindToken" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalBindTokenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{ __('panel/plugin.bind_token') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="domainTokenInput" name="domain_token" placeholder="{{ __('panel/plugin.input_token') }}" aria-label="Recipient's username" aria-describedby="button-addon2">
                        <button class="btn btn-outline-primary" type="button" id="btnBind">{{ __('panel/plugin.bind') }}</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.close') }}</button>
                    <a href="{{config('innoshop.api_url').'/account/domains'}}" type="button" class="btn btn-primary" target="_blank">{{ __('panel/plugin.point_to_get_token') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('footer')
    <script>
        var domainToken = '{{system_setting('domain_token')}}'
        if (domainToken != ''){
            $('#domainTokenInput').val(domainToken)
        }
        $(function () {

            $('#btnBind').click(function (){
                if ($('#domainTokenInput').val()==''){
                    layer.msg('{{ __('panel/plugin.token_empty') }}',{ icon:2 })
                    return
                }
                axios.put('{{ panel_route('marketplaces.domain_token') }}',{
                    'domain_token' : $('#domainTokenInput').val()
                }).then(function (res){
                    layer.msg(res.message,{ icon:1 })
                    location.reload();
                }).catch(function (err) {
                    layer.msg(err.message ,{icon:2})
                })
            })
        })
    </script>
@endpush
