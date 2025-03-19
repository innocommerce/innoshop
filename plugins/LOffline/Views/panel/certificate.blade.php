<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.woff') }}">
<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.ttf') }}">
<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/index.css') }}">
<div class="card mb-4" id="l_offline_app">
  <div class="card-body">
    <div class="table-push">
      <div class="demo-image__preview">
        @foreach ($offline_imgs as $img)
          <el-image
            style="width: 100px; height: 100px"
            src="{{$img}}"
            :preview-src-list="srcList">
          </el-image>
        @endforeach
      </div>
    </div>
  </div>
</div>

<script src="{{ plugin_asset('l_offline','/element-ui/vue.js') }}"></script>
<script src="{{ plugin_asset('l_offline','/element-ui/index.js') }}"></script>
<script>
  new Vue({
    el: '#l_offline_app',
    data: function () {
      return {
        url: '',
        srcList: @json($offline_imgs)
      }
    },
    created() {

    },
    methods: {}
  })
</script>
