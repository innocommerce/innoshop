<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.woff') }}">
<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/fonts/element-icons.ttf') }}">
<link rel="stylesheet" href="{{ plugin_asset('l_offline','/element-ui/index.css') }}">

<script src="{{ plugin_asset('l_offline','/element-ui/vue.js') }}"></script>
<script src="{{ plugin_asset('l_offline','/element-ui/index.js') }}"></script>

<div id="bk-offline-app" v-cloak>
    <el-form :model="imageDialog" label-position="top" :rules="rules" ref="form1">
        <el-form-item label="{{ __('LOffline::common.pay_des') }}">
            {!! $offline_des !!}
        </el-form-item>
        <el-form-item label="{{ __('LOffline::common.img_label') }}" label-width="100px" prop="imgs">
            <el-upload
                :headers="imageDialog.headers"
                :action="imageDialog.uploadUrl"
                :auto-upload="true"
                :before-upload="onUploadBefore"
                :file-list="imageDialog.fileList"
                :limit="3"
                :on-exceed="handleExceed"
                :on-preview="handlePictureCardPreview"
                :on-remove="handleRemove"
                :on-success="onUploadSuccess"
                list-type="picture-card"
            >
                <i class="el-icon-plus" slot="default"></i>
            </el-upload>
        </el-form-item>
    </el-form>

    <el-button type="danger"
               @click="checkedBtnCheckoutConfirm"
               v-loading="pay_loading" >{{ __('LOffline::common.btn_submit') }}</el-button>
</div>

<script>
    new Vue({
        el: '#bk-offline-app',

        data: function () {
            const validatorImgs = (rule, value, callback) => {
                if (this.imageDialog.fileList.length == 0) {
                    callback(new Error("{{ __('LOffline::common.certificate_empty') }}"));
                } else {
                    callback();
                }
            };
            return {
                pay_loading: false,
                imageDialog: {
                    fileList: [],
                    uploadUrl: "{{front_route('l_offline.upload_payment_data')}}",
                    showImgUrl: "",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                rules: {
                    imgs: [{required: true, validator: validatorImgs, trigger: 'blur'}],
                },
            }
        },

        methods: {
            handleExceed(files, fileList) {
                this.$alert("{{ __('LOffline::common.certificate_limit',["limit"=>1]) }}", 'Fail', {
                    confirmButtonText: "{{ __('common.confirm') }}",
                    callback: action => {
                    }
                });
                return;
            },
            handlePictureCardPreview(file) {
                this.imageDialog.dialogImageUrl = file.url;
                this.imageDialog.dialogVisible = true;
            },
            handleRemove(file) {
                console.log(file);
            },
            onUploadBefore(file) {


            },
            onUploadSuccess(response, file, fileList) {
                console.log(fileList);
                this.imageDialog.fileList = fileList;
                console.log(response);
            },

            checkedBtnCheckoutConfirm() {
                let that = this;
                this.$refs['form1'].validate((valid) => {
                    if (valid) {
                        //console.log(that.imageDialog.fileList);return;
                        let imgs = [];
                        that.imageDialog.fileList.forEach(function (item) {
                            imgs.push(item.response.data.path)
                        })
                        that.pay_loading = true;
                        axios.post("{{front_route('l_offline.submit')}}", {
                            imgs: imgs,
                            order_no:@json($order->number ?? null)}).then((res) => {
                            if (res.success) {
                                window.location.href = res.data.callback;
                            } else {
                                that.pay_loading = false;
                                that.$message.warning(res.message)
                            }
                        })
                    }
                });
            }
        }
    })
</script>
