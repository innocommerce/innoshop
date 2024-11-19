@extends('panel::layouts.app')

@section('title', __('enterprise::file_manager.title'))

<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
  <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
  <script src="https://unpkg.com/element-ui/lib/index.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

  <script>
    // http 请求封装
    (function(window) {
      'use strict';

      // 创建 axios 实例
      const http = axios.create({
        baseURL: '/api/panel/',
        timeout: 30000,
        headers: {
          'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
        }
      });

      // 请求拦截器
      http.interceptors.request.use(
        config => {
          // 添加 loading
          if (window.layer) {
            layer.load(2, { shade: [0.3, '#fff'] });
          }
          return config;
        },
        error => {
          if (window.layer) {
            layer.closeAll('loading');
          }
          return Promise.reject(error);
        }
      );

      // 响应拦截器
      http.interceptors.response.use(
        response => {
          if (window.layer) {
            layer.closeAll('loading');
          }
          return response.data;
        },
        error => {
          if (window.layer) {
            layer.closeAll('loading');
          }

          // 统一错误处理
          if (error.response) {
            const message = error.response.data.message || '请求失败';
            // 使用 Element UI 的消息提示
            if (window.Vue && window.ELEMENT) {
              ELEMENT.Message.error(message);
            }

            switch (error.response.status) {
              case 401:
                // 未授权处理
                break;
              case 403:
                // 禁止访问处理
                break;
              case 404:
                // 未找到处理
                break;
              default:
                // 其他错误
                break;
            }
          }
          return Promise.reject(error);
        }
      );

      // 暴露到全局
      window.http = http;
    })(window);
  </script>

  <style>
    .file-manager {
      background: #fff;
      border-radius: 4px;
      min-height: calc(100vh - 180px);
    }

    /* 左侧文件夹树样式 */
    .folder-tree {
      border-right: 1px solid #EBEEF5;
      height: calc(100vh - 180px);
      overflow-y: auto;
      padding: 20px;
    }

    /* 右侧文件列表样式 */
    .file-list {
      padding: 20px;
      height: calc(100vh - 240px);
      overflow-y: auto;
    }

    /* 顶部工具栏 */
    .file-toolbar {
      padding: 15px 20px;
      border-bottom: 1px solid #EBEEF5;
      background: #fff;
      border-radius: 4px 4px 0 0;
    }

    /* 文件卡片样式 */
    .file-card {
      border: 1px solid #EBEEF5;
      border-radius: 4px;
      transition: all 0.3s;
      cursor: pointer;
      position: relative;
      margin-bottom: 15px;
    }

    .file-card:hover {
      border-color: #8446df;
      box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
    }

    .file-card.selected {
      border-color: #8446df;
      background: rgba(132, 70, 223, 0.05);
    }

    .file-card .file-thumb {
      padding: 8px;
      height: 140px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fafafa;
      border-radius: 4px 4px 0 0;
    }

    .file-card .file-thumb img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .file-card .file-info {
      padding: 10px;
      border-top: 1px solid #EBEEF5;
    }

    .file-card .file-name {
      font-size: 13px;
      color: #606266;
      margin: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .file-card .file-size {
      font-size: 12px;
      color: #909399;
      margin: 5px 0 0;
    }

    /* 自定义 Element UI 主题色 */
    .el-button--primary {
      background-color: #8446df;
      border-color: #8446df;
    }
    .el-button--primary:hover,
    .el-button--primary:focus {
      background: #9969e5;
      border-color: #9969e5;
    }
    .el-tree-node.is-current > .el-tree-node__content {
      background-color: #f0e6fc !important;
      color: #8446df;
    }
    .el-tree-node__content:hover {
      background-color: #f5f7fa;
    }

    /* 分页容器样式 */
    .pagination-container {
      padding: 20px 0;
      text-align: right;
      background: #fff;
      border-top: 1px solid #EBEEF5;
      margin-top: 20px;
    }

    /* 加载状态样式 */
    .el-loading-spinner .el-loading-text {
      color: #8446df;
    }

    .el-loading-spinner .path {
      stroke: #8446df;
    }

    /* 分页组件主题色自定义 */
    .el-pagination.is-background .el-pager li:not(.disabled).active {
      background-color: #8446df;
    }

    .el-pagination.is-background .el-pager li:not(.disabled):hover {
      color: #8446df;
    }

    .el-pagination .el-select .el-input .el-input__inner:hover {
      border-color: #8446df;
    }

    /* 空状态样式 */
    .el-empty {
      padding: 40px 0;
    }

    /* 文件列表加载状态容器 */
    .file-list .el-loading-mask {
      border-radius: 4px;
    }

    /* 调整文件列表高度，为分页腾出空间 */
    .file-list {
      height: calc(100vh - 280px);
    }

    /* 调整文件网格布局 */
    .el-row {
      margin-right: -7.5px !important;
      margin-left: -7.5px !important;
    }

    .el-col {
      padding-right: 7.5px !important;
      padding-left: 7.5px !important;
    }

    /* 修改按钮样式部分 */
    /* Element UI 按钮样式重置 */
    .el-button {
      font-weight: normal;
      border-radius: 4px;
      padding: 8px 15px;
      line-height: 1;
      height: auto;
    }

    .el-button--small {
      padding: 7px 12px;
      font-size: 12px;
    }

    .el-button--mini {
      padding: 5px 10px;
      font-size: 12px;
    }

    /* 主要按钮样式 */
    .el-button--primary {
      background-color: #8446df;
      border-color: #8446df;
      color: #fff;
    }

    .el-button--primary:hover,
    .el-button--primary:focus {
      background: #9969e5;
      border-color: #9969e5;
      color: #fff;
    }

    .el-button--primary:active {
      background: #7339c7;
      border-color: #7339c7;
      color: #fff;
    }

    /* 默认按钮样式 */
    .el-button--default {
      background: #fff;
      border-color: #dcdfe6;
      color: #606266;
    }

    .el-button--default:hover,
    .el-button--default:focus {
      background: #f4f4f5;
      border-color: #8446df;
      color: #8446df;
    }

    /* 按钮组样式 */
    .el-button-group {
      display: inline-flex;
      vertical-align: middle;
    }

    .el-button-group .el-button {
      border-radius: 0;
    }

    .el-button-group .el-button:first-child {
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
    }

    .el-button-group .el-button:last-child {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
    }

    .el-button-group .el-button:not(:first-child):not(:last-child) {
      margin: 0 -1px;
    }

    /* 禁用状态 */
    .el-button.is-disabled,
    .el-button.is-disabled:hover,
    .el-button.is-disabled:focus {
      color: #c0c4cc;
      cursor: not-allowed;
      background-image: none;
      background-color: #fff;
      border-color: #ebeef5;
    }

    /* 图标按钮 */
    .el-button [class*="el-icon-"] + span {
      margin-left: 5px;
    }

    /* 工具栏按钮间距 */
    .file-toolbar .el-button-group + .el-button-group {
      margin-left: 10px;
    }

    /* 文字按钮 */
    .el-button--text {
      border: 0;
      padding: 0;
      background: transparent;
      color: #8446df;
    }

    .el-button--text:hover,
    .el-button--text:focus {
      color: #9969e5;
      background: transparent;
    }

    .file-uploader {
      text-align: center;
    }

    .file-uploader .el-upload {
      width: 100%;
    }

    .file-uploader .el-upload-dragger {
      width: 100%;
      height: 200px;
    }

    .file-uploader .el-icon-upload {
      margin: 40px 0 16px;
      font-size: 48px;
      color: #8446df;
    }

    .file-uploader .el-upload__text {
      color: #606266;
      font-size: 14px;
      margin: 0 0 16px;
    }

    .file-uploader .el-upload__text em {
      color: #8446df;
      font-style: normal;
    }

    .file-uploader .el-upload__tip {
      color: #909399;
    }

    .cropper-dialog {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      z-index: 2001;
      width: 900px;
    }

    .cropper-container {
      width: 100%;
      height: 500px;
      margin-bottom: 20px;
      overflow: hidden;
    }

    .cropper-container img {
      max-width: 100%;
      display: block;
    }

    .cropper-controls {
      text-align: right;
      padding-top: 15px;
      border-top: 1px solid #eee;
    }

    .cropper-controls button {
      margin-left: 10px;
      padding: 6px 20px;
    }

    .cropper-mask {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 2000;
    }

    /* Element UI 对话框层级参考 */
    .el-dialog__wrapper {
      z-index: 1000;
    }
    .v-modal {
      z-index: 999;
    }
  </style>
@endpush

@section('content')
  <div id="app">
    <div class="file-manager">
      <div class="file-toolbar">
        <el-row type="flex" justify="space-between" align="middle">
          <el-col :span="12">
            <el-button-group>
              <el-button type="primary" size="small" @click="uploadFile">
                <i class="el-icon-upload2"></i> 上传文件
              </el-button>
              <el-button size="small" @click="createFolder">
                <i class="el-icon-folder-add"></i> 新建文件夹
              </el-button>
            </el-button-group>
          </el-col>
          <el-col :span="12" style="text-align: right">
            <el-button-group>
              <el-button size="small" :disabled="!selectedFiles.length" @click="deleteFiles">
                <i class="el-icon-delete"></i> 删除
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="moveFiles">
                <i class="el-icon-folder"></i> 移动到
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="copyFiles">
                <i class="el-icon-document-copy"></i> 复制到
              </el-button>
            </el-button-group>
          </el-col>
        </el-row>
      </div>

      <el-row :gutter="0">
        <!-- 左侧文件夹树 -->
        <el-col :span="6">
          <div class="folder-tree">
            <el-tree
              :data="folders"
              :props="defaultProps"
              @node-click="handleNodeClick"
              :highlight-current="true"
              :default-expanded-keys="['1']"
              node-key="id">
              <span class="custom-tree-node" slot-scope="{ node, data }">
                <i :class="data.children ? 'el-icon-folder' : 'el-icon-folder-opened'"></i>
                <span style="margin-left: 4px">@{{ node.label }}</span>
              </span>
            </el-tree>
          </div>
        </el-col>

        <!-- 右侧文件列表 -->
        <el-col :span="18">
          <div class="file-list">
            <div v-loading="loading" element-loading-text="加载中...">
              <el-row :gutter="15">
                <el-col :xs="12" :sm="8" :md="6" :lg="4" v-for="file in files" :key="file.id">
                  <div :class="['file-card', {selected: selectedFiles.includes(file.id)}]"
                       @click="toggleSelect(file)">
                    <div class="file-thumb">
                      <img :src="file.thumb" :alt="file.name">
                    </div>
                    <div class="file-info">
                      <p class="file-name" :title="file.name">@{{ file.name }}</p>
                      <p class="file-size">@{{ file.size }}</p>
                    </div>
                  </div>
                </el-col>
              </el-row>

              <!-- 添加分页组件 -->
              <div class="pagination-container" v-if="files.length">
                <el-pagination
                  @size-change="handleSizeChange"
                  @current-change="handleCurrentChange"
                  :current-page="pagination.page"
                  :page-sizes="[20, 40, 60, 80]"
                  :page-size="pagination.per_page"
                  layout="total, sizes, prev, pager, next, jumper"
                  :total="pagination.total"
                  background>
                </el-pagination>
              </div>

              <!-- 添加空状态 -->
              <el-empty v-else description="暂无文件" :image-size="120"></el-empty>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 新建文件夹对话框 -->
    <el-dialog
      title="新建文件夹"
      :visible.sync="folderDialog.visible"
      width="400px">
      <el-form :model="folderDialog.form" label-width="80px">
        <el-form-item label="文件夹名">
          <el-input v-model="folderDialog.form.name" placeholder="请输入文件夹名称"></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button @click="folderDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitCreateFolder">确 定</el-button>
      </span>
    </el-dialog>

    <!-- 上传文件对话框 -->
    <el-dialog
      title="上传文件"
      :visible.sync="uploadDialog.visible"
      width="500px">
      <el-upload
        class="file-uploader"
        drag
        multiple
        :action="uploadUrl"
        :headers="uploadHeaders"
        :data="uploadData"
        :before-upload="beforeUpload"
        :on-success="handleUploadSuccess"
        :on-error="handleUploadError"
        :on-progress="handleUploadProgress">
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
        <div class="el-upload__tip" slot="tip">支持 jpg、jpeg、png、gif 格式的图片文件</div>
      </el-upload>
    </el-dialog>
  </div>
@endsection

@push('footer')
<script>
new Vue({
  el: '#app',
  data() {
    return {
      files: [],
      selectedFiles: [],
      currentFolder: null,
      folders: [{
        id: '1',
        label: '图片空间',
        children: [{
          id: '2',
          label: 'demo'
        }]
      }],
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      folderDialog: {
        visible: false,
        form: {
          name: ''
        }
      },
      pagination: {
        page: 1,
        per_page: 20,
        total: 0
      },
      loading: false,
      uploadDialog: {
        visible: false
      },
      uploadUrl: '/api/panel/file_manager/upload',
      uploadHeaders: {
        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
      },
      uploadData: {
        path: '/demo',
        type: 'images' // 默认上传路径
      },
      cropperOptions: {
        viewMode: 1,
        autoCropArea: 1, // 默认裁剪全图
        zoomable: true,
        cropBoxResizable: true,
        cropBoxMovable: true,
        dragMode: 'move',
        guides: true,
        center: true,
        highlight: false,
        background: true,
        modal: true
      }
    }
  },
  methods: {
    uploadFile() {
      this.uploadData.path = this.currentFolder ? this.currentFolder.id : '/demo';
      this.uploadDialog.visible = true;
    },
    createFolder() {
      this.folderDialog.visible = true;
    },
    submitCreateFolder() {
      this.folderDialog.visible = false;
    },
    deleteFiles() {
      this.$confirm('确认删除选中的文件?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        http.post('file_manager/files/delete', {
          files: this.selectedFiles
        }).then(res => {
          if (res.success) {
            this.$message.success('删除成功');
            this.selectedFiles = [];
            this.loadFiles();
          }
        });
      });
    },
    moveFiles() {
      // 实现移动文件
    },
    copyFiles() {
      // 实现复制文件
    },
    toggleSelect(file) {
      const index = this.selectedFiles.indexOf(file.id);
      if (index === -1) {
        this.selectedFiles.push(file.id);
      } else {
        this.selectedFiles.splice(index, 1);
      }
    },
    handleNodeClick(data) {
      this.currentFolder = data;
      this.loadFiles();
    },
    loadFiles() {
      this.loading = true;
      const params = {
        page: this.pagination.page,
        per_page: this.pagination.per_page,
        base_folder: this.currentFolder ? this.currentFolder.id : '/demo'
      };

      http.get('file_manager/files', { params })
        .then(res => {
          // 当前API不需要检查 success
          // 处理文件列表数据
          this.files = res.images.map(file => ({
            id: file.path, // 使用文件路径作为唯一标识
            name: file.name,
            thumb: file.url, // 缩略图URL
            url: file.origin_url, // 原始图片URL
            size: file.mime, // 显示文件类型
            selected: false
          }));

          // 更新分页信息
          this.pagination.total = res.image_total;
          this.pagination.page = res.image_page;
        })
        .catch(err => {
          this.$message.error('获取文件列表失败：' + err.message);
        })
        .finally(() => {
          this.loading = false;
        });
    },

    handleCurrentChange(page) {
    this.pagination.page = page;
    this.loadFiles();
     },

    handleSizeChange(size) {
        this.pagination.per_page = size;
        this.pagination.page = 1;
        this.loadFiles();
    },

    // 上传前验证
    beforeUpload(file) {
      // 验证文件类型
      const isImage = ['image/jpeg', 'image/png', 'image/gif'].includes(file.type);
      if (!isImage) {
        this.$message.error('只能上传图片文件！');
        return false;
      }

      // 验证文件大小（默认限制 8MB）
      const isLt2M = file.size / 1024 / 1024 < 8;
      if (!isLt2M) {
        this.$message.error('图片大小不能超过 8MB！');
        return false;
      }

      // 显示裁剪对话框
      this.cropImage(file);
      return false; // 阻止自动上传
    },

    cropImage(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        // 创建遮罩层
        const mask = document.createElement('div');
        mask.className = 'cropper-mask';
        document.body.appendChild(mask);

        // 创建裁剪对话框
        const dialog = document.createElement('div');
        dialog.className = 'cropper-dialog';
        dialog.innerHTML = `
          <div class="cropper-container">
            <img src="${e.target.result}">
          </div>
          <div class="cropper-controls">
            <button class="el-button el-button--default el-button--small cancel-btn">取消</button>
            <button class="el-button el-button--primary el-button--small confirm-btn">确认</button>
          </div>
        `;

        document.body.appendChild(dialog);

        // 初始化 cropper
        const image = dialog.querySelector('img');
        const cropper = new Cropper(image, this.cropperOptions);

        // 确认裁剪
        dialog.querySelector('.confirm-btn').onclick = () => {
          const canvas = cropper.getCroppedCanvas({
            width: 800,
            height: 800
          });

          canvas.toBlob((blob) => {
            const formData = new FormData();
            formData.append('file', blob, file.name);
            formData.append('path', this.uploadData.path);
            formData.append('type', 'images');

            // 上传裁剪后的图片
            http.post('file_manager/upload', formData)
              .then(res => {
                if (res.success) {
                  this.$message.success('上传成功');

                  this.cleanupDialog(dialog, mask);

                  // 关闭上传对话框
                  this.uploadDialog.visible = false;

                  // 刷新文件列表
                  this.loadFiles();
                } else {
                  this.$message.error(res.message || '上传失败');
                }
              })
              .catch(err => {
                this.$message.error('上传失败：' + err.message);
              })
              .finally(() => {
                this.cleanupDialog(dialog, mask);
                this.uploadDialog.visible = false;
              });
          });
        };

        // 取消裁剪
        dialog.querySelector('.cancel-btn').onclick = () => {
          this.cleanupDialog(dialog, mask);
        };
      };
      reader.readAsDataURL(file);
    },

    // 上传成功回调
    handleUploadSuccess(response, file, fileList) {
      if (response.success) {
        this.$message.success('上传成功');
        // 刷新文件列表
        this.loadFiles();
      } else {
        this.$message.error(response.message || '上传失败');
      }

      // 如果所有文件都上传完成，关闭对话框
      if (fileList.every(file => file.status === 'success' || file.status === 'error')) {
        this.uploadDialog.visible = false;
      }
    },

    // 上传失败回调
    handleUploadError(err, file) {
      this.$message.error('上传失败：' + (err.message || '未知错误'));
    },

    // 上传进度回调
    handleUploadProgress(event, file) {

    },

    cleanupDialog(dialog, mask) {
      // 检查并移除对话框
      if (dialog && dialog.parentNode) {
        dialog.parentNode.removeChild(dialog);
      }
      // 检查并移除遮罩
      if (mask && mask.parentNode) {
        mask.parentNode.removeChild(mask);
      }
    }
  },
  mounted() {
    this.loadFiles();
  }
});
</script>
@endpush
