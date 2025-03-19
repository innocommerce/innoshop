@extends('panel::layouts.app')


@section('title', __('LOffline::common.payment_name'))
@push('header')
    <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
    <link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.woff') }}">
    <link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.ttf') }}">
    <link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/index.css') }}">

@endpush

@section('content')
    <div class="mb-5" id="app">
        <el-alert
            title="填写支付说明。在用户支付页面会显示这个内容，让用户知晓如何支付"
            type="success"
            :closable="false">
        </el-alert>
        <el-tabs v-model="activeName" type="card">
            @foreach ($languages2 as $language)
                <el-tab-pane label="{{ $language->name }}" name="{{ $language->code }}">
        <textarea name="descriptions" data-locale="{{$language->code}}" class="form-control tinymce">

        </textarea>
                </el-tab-pane>
            @endforeach
        </el-tabs>
        <br/>

        <button type="button" class="btn btn-primary save-btn" onclick="app.submit()">{{ __('front/common.submit') }}</button>
    </div>
@endsection

@push('footer')

    <script src="{{ plugin_asset('l_offline','/element-ui/vue.js') }}"></script>
    <script src="{{ plugin_asset('l_offline','/element-ui/index.js') }}"></script>
    <script>
        let app = new Vue({
            el: '#app',
            activeName: "",
            data: {
                languages:@json($languages2),
                offline_config_descriptions: @json($offline_payment_descriptions),
            },

            computed: {},
            created: function () {
                let that = this;
                this.activeName = this.languages[0]['code']
                console.log(this.languages);
                setTimeout(function () {
                    that.initData()
                },1000)
            },
            methods: {
                submit() {
                    var descriptions = {};
                    let len = tinyMCE.editors.length;
                    for (var i = 0; i < len; i++) {
                        var entry = tinyMCE.editors[i];
                        descriptions[$(entry.targetElm).data("locale")] = entry.getContent();
                    }
                    console.log(descriptions);
                    axios.post("{{panel_route("l_offline.save_config")}}", {descriptions: descriptions}).then((res) => {
                        if (res.code == 0) {
                            layer.msg("保存成功")
                        } else {
                            layer.msg(res.msg)
                        }

                    })
                },


                initData() {
                    let tmpDescriptions = [];
                    let len1 = this.offline_config_descriptions.length;
                    for(var i = 0 ; i < len1 ; i ++){
                        var entry = this.offline_config_descriptions[i];
                        tmpDescriptions[entry.locale] = entry.content;
                    }

                    let len = tinyMCE.editors.length;
                    console.log(len);
                    for (var i = 0; i < len; i++) {
                        var entry = tinyMCE.editors[i];
                        var locale = $(entry.targetElm).data("locale");
                        if (typeof (tmpDescriptions[locale]) == 'undefined') {
                            entry.setContent("");
                        } else {
                            entry.setContent(tmpDescriptions[locale]);
                        }
                    }
                },
            }
        })
    </script>
@endpush
