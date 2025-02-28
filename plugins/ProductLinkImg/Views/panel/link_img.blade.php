<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
<div id="product-link-img-app">
    <div style="text-align: center">
        <el-alert
            title="{{__('ProductLinkImg::common.iframe_alert')}}"
            type="warning"
            effect="dark">
        </el-alert>
        <br>
        <el-button
            size="mini"
            type="success"
            @click="addProductImgLinkRow">{{__('ProductLinkImg::common.iframe_add')}}
        </el-button>
        <el-table
            :data="product_link_img_links_data"
            style="width: 100%">
            <el-table-column
                label="{{__('ProductLinkImg::common.iframe_status')}}"
                width="120"
            >
                <template slot-scope="scope">
                    <img :src="scope.row.link" style="width: 50px;height: 50px">
                </template>
            </el-table-column>
            <el-table-column
                label="{{__('ProductLinkImg::common.iframe_img_link')}}">
                <template slot-scope="scope">
                    <el-input v-model="scope.row.link"></el-input>
                </template>
            </el-table-column>
            <el-table-column
                width="80">
                <template slot-scope="scope">
                    <el-button
                        size="mini"
                        type="danger"
                        @click="delProductImgLinkRow(scope.$index)">{{__('ProductLinkImg::common.del')}}
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="okProductImagesByLink">{{ __('panel/common.confirm') }}</el-button>
            </span>
    </div>
</div>

<!-- import Vue before Element -->
<script src="https://unpkg.com/vue@2/dist/vue.js"></script>
<!-- import JavaScript -->
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script>
    new Vue({
        el: '#product-link-img-app',

        data: {
            product_link_img_add_dialog: false,
            product_link_img_sku_index: null,
            product_link_img_links_dialog: false,
            product_link_img_links_data: [],
        },

        beforeMount() {

        },

        methods: {
            addProductImgLinkRow() {
                this.product_link_img_links_data.push({"link": ""});
            },
            delProductImgLinkRow(index) {
                this.product_link_img_links_data.splice(index, 1);
            },

            okProductImagesByLink() {
                let that = this;
                this.product_link_img_links_dialog = false;
                this.product_link_img_links_data.forEach(function (item2) {
                    if (item2.link != '' && item2.link.substr(0, 4) == 'http') {

                        let val = item2.link;
                        let url = item2.link;

                        var parentElement = window.parent.document.querySelector('.img-upload-trigger');

                        console.log(parentElement)
                        let item = '<div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">';
                        item += '<div class="position-absolute tool-wrap d-flex top-0 start-0 w-100 bg-primary bg-opacity-75"><div class="show-img w-100 text-center"><i class="bi bi-eye text-white"></i></div><div class="w-100 delete-img text-center"><i class="bi bi-trash text-white"></i></div></div>';
                        item += '<div class="img-info d-flex justify-content-center align-items-center h-100 w-80 bg-white">';
                        item += '<img src="' + url + '" class="img-fluid" data-origin-img="' + url + '">';
                        item += '</div>';
                        item += '<input class="d-none" name="images[]" value="' + val + '">';
                        item += '</div>';

                        var tempDiv = window.parent.document.createElement('div');
                        tempDiv.innerHTML = item;

                        parentElement.before(tempDiv.firstChild);
                        var index = window.parent.layer.getFrameIndex(window.name); // 获取窗口索引
                        window.parent.layer.close(index); // 关闭窗口
                        //parentElement[0].parents('.is-up-file').find('.imgs-count').text(parentElement[0].parents('.is-up-file').find('.img-upload-item').length - 1);


                    }


                })
            },

        }
    })
</script>
