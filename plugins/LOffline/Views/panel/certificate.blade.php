<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
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
<!-- import Vue before Element -->
<script src="https://unpkg.com/vue@2/dist/vue.js"></script>
<!-- import JavaScript -->
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
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
