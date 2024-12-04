@extends('panel::layouts.blank')

@section('title', __('enterprise::file_manager.title'))

@push('header')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.24.3/vuedraggable.umd.min.js"></script>

  <script>
    // http 请求封装
    const http = axios.create({
      baseURL: '/api/panel/',
      timeout: 30000,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
      }
    });

    // ... 其他代码保持不变 ...
  </script>
@endpush

@section('content')
<div id="app" class="file-manager">
  <div class="row g-0">
    <!-- 左侧文件夹树 -->
    <div class="col-md-3">
      <div class="folder-tree">
        <el-tree
          ref="folderTree"
          :data="folders"
          :props="defaultProps"
          @node-click="handleNodeClick"
          :highlight-current="true"
          :default-expanded-keys="defaultExpandedKeys"
          :current-node-key="currentFolder ? currentFolder.id : '/'"
          node-key="id"
          draggable
          :allow-drop="handleAllowDrop"
          :allow-drag="handleAllowDrag"
          @node-drag-start="handleDragStart"
          @node-drag-enter="handleDragEnter"
          @node-drag-leave="handleDragLeave"
          @node-drag-end="handleNodeDragEnd"
          @node-drop="handleNodeDrop">
          <div class="el-tree-node__wrapper"
               slot-scope="{ node, data }"
               @contextmenu.prevent="showFolderContextMenu($event, data, node)"
               @dragover.prevent
               @dragenter.prevent="handleTreeDragEnter($event, node, data)"
               @dragleave.prevent="handleTreeDragLeave($event, node)"
               @drop.prevent="handleTreeDrop($event, node, data)">
            <span class="custom-tree-node">
              <i :class="[
                data.isRoot ? 'el-icon-folder' : 'el-icon-folder',
                {'el-icon-folder-opened': node.expanded}
              ]" style="margin-right: 4px; color: #8446df;"></i>
              <span>@{{ node.label }}</span>
            </span>
          </div>
        </el-tree>
      </div>
    </div>

    <!-- 右侧文件列表 -->
    <div class="col-md-9">
      <!-- 工具栏 -->
      <div class="file-toolbar">
        <el-button-group>
          <el-button size="small" type="primary" @click="$refs.upload.click()">
            <i class="el-icon-upload"></i> 上传文件
          </el-button>
          <el-button size="small" @click="createFolderDialog.visible = true">
            <i class="el-icon-folder-add"></i> 新建文件夹
          </el-button>
        </el-button-group>

        <input
          type="file"
          ref="upload"
          style="display: none"
          @change="handleFileUpload"
          multiple>
      </div>

      <!-- 文件列表 -->
      <div class="file-list">
        <el-row :gutter="15">
          <el-col :span="6" v-for="file in files" :key="file.id || file.path">
            <!-- 文件卡片组件 -->
            <div :class="['file-card', {selected: selectedFiles.includes(file.id || file.path)}]"
                 @click="handleFileClick($event, file)"
                 @dblclick="handleFileDoubleClick(file)"
                 @contextmenu.prevent="showContextMenu($event, file)"
                 :data-is-dir="file.is_dir"
                 draggable="true"
                 @dragstart="handleFileDragStart($event, file)"
                 @dragend="handleFileDragEnd($event)"
                 @dragenter.prevent="handleFileDragEnter($event, file)"
                 @dragover.prevent
                 @dragleave.prevent="handleFileDragLeave($event)"
                 @drop.prevent="handleFileDrop($event, file)">
              <!-- 文件缩略图 -->
              <div class="file-thumb">
                <template v-if="file.is_dir">
                  <div class="folder-icon">
                    <img :src="file.thumb" alt="folder" draggable="false">
                  </div>
                </template>
                <template v-else>
                  <img :src="file.url" :alt="file.name" draggable="false">
                </template>
              </div>
              <!-- 文件信息 -->
              <div class="file-info">
                <div class="file-name">@{{ file.name }}</div>
                <div class="file-type">@{{ file.mime }}</div>
              </div>
            </div>
          </el-col>
        </el-row>
      </div>

      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
          :current-page="currentPage"
          :page-sizes="[20, 40, 60, 80]"
          :page-size="pageSize"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total">
        </el-pagination>
      </div>
    </div>
  </div>

  <!-- 各种对话框组件 -->
  <!-- ... 其他对话框和组件代码保持不变 ... -->
</div>

<!-- Vue 实例和其他 JavaScript 代码 -->
<script>
  new Vue({
    el: '#app',
    data() {
      return {
        folders: [],
        files: [],
        currentFolder: null,
        defaultProps: {
          children: 'children',
          label: 'name'
        },
        defaultExpandedKeys: ['/'],
        selectedFiles: [],
        isMultiSelectMode: false,
        isDragging: false,
        draggedFile: null,
        currentPage: 1,
        pageSize: 20,
        total: 0,
        keyword: '',
        sort: 'created',
        order: 'desc',
        // iframe 相关配置
        isIframeMode: false,
        iframeConfig: null,
        // 其他对话框状态
        createFolderDialog: {
          visible: false,
          form: {
            name: ''
          }
        },
        contextMenu: {
          visible: false,
          style: {
            top: '0px',
            left: '0px'
          },
          file: null
        },
        folderContextMenu: {
          visible: false,
          style: {
            top: '0px',
            left: '0px'
          },
          folder: null
        }
      }
    },
    methods: {
      // 加载文件夹树
      loadFolders() {
        http.get('file_manager/directories').then(res => {
          if (res.success) {
            this.folders = [{
              id: '/',
              name: '图片空间',
              path: '/',
              isRoot: true,
              children: res.data
            }];
          }
        });
      },

      // 加载文件列表
      loadFiles(path = '') {
        http.get('file_manager/files', {
          params: {
            base_folder: path,
            keyword: this.keyword,
            sort: this.sort,
            order: this.order,
            page: this.currentPage,
            per_page: this.pageSize
          }
        }).then(res => {
          this.files = res.images;
          this.total = res.image_total;
        });
      },

      // 处理节点点击
      handleNodeClick(data) {
        this.currentFolder = data;
        this.loadFiles(data.path);
      },

      // 处理文件选择
      handleFileSelect(file) {
        if (this.isIframeMode && this.iframeConfig.callback) {
          if (!this.iframeConfig.multiple) {
            this.iframeConfig.callback(file);
            parent.layer.closeAll();
            return;
          }
          this.toggleFileSelect(file);
        }
      },

      // 确认选择（多选模式）
      confirmSelection() {
        if (this.isIframeMode && this.iframeConfig.callback) {
          const selectedFiles = this.files.filter(file =>
            this.selectedFiles.includes(file.id || file.path)
          );
          this.iframeConfig.callback(selectedFiles);
          parent.layer.closeAll();
        }
      },

      // 处理文件上传
      handleFileUpload(event) {
        const files = event.target.files;
        const currentPath = this.currentFolder ? this.currentFolder.path : '/';

        for (let i = 0; i < files.length; i++) {
          const formData = new FormData();
          formData.append('file', files[i]);
          formData.append('path', currentPath);

          http.post('file_manager/upload', formData).then(res => {
            if (res.success) {
              this.$message.success('上传成功');
              this.loadFiles(currentPath);
            }
          });
        }
        // 清空 input，以便可以上传相同的文件
        event.target.value = '';
      },

      // 提交创建文件夹
      submitCreateFolder() {
        if (!this.createFolderDialog.form.name) {
          this.$message.warning('请输入文件夹名称');
          return;
        }

        const currentPath = this.currentFolder ? this.currentFolder.path : '/';
        const folderPath = currentPath === '/' ?
          this.createFolderDialog.form.name :
          currentPath + '/' + this.createFolderDialog.form.name;

        http.post('file_manager/directories', {
          name: folderPath
        }).then(res => {
          if (res.success) {
            this.$message.success('创建成功');
            this.createFolderDialog.visible = false;
            this.createFolderDialog.form.name = '';
            this.loadFolders();
            this.loadFiles(currentPath);
          }
        });
      },

      // 处理分页大小改变
      handleSizeChange(val) {
        this.pageSize = val;
        this.loadFiles(this.currentFolder ? this.currentFolder.path : '/');
      },

      // 处理当前页改变
      handleCurrentChange(val) {
        this.currentPage = val;
        this.loadFiles(this.currentFolder ? this.currentFolder.path : '/');
      },

      // 处理文件点击
      handleFileClick(event, file) {
        if (this.isIframeMode) {
          this.handleFileSelect(file);
        } else if (this.isMultiSelectMode) {
          this.toggleFileSelect(file);
        } else {
          const fileId = file.id || file.path;
          this.selectedFiles = [fileId];
        }
      },

      // 切换文件选择状态
      toggleFileSelect(file) {
        const fileId = file.id || file.path;
        const index = this.selectedFiles.indexOf(fileId);
        if (index === -1) {
          this.selectedFiles.push(fileId);
        } else {
          this.selectedFiles.splice(index, 1);
        }
      }
    },
    mounted() {
      this.loadFolders();

      // 检查是否在 iframe 中运行
      this.isIframeMode = window.self !== window.top;
      if (this.isIframeMode && window.fileManagerConfig) {
        this.iframeConfig = window.fileManagerConfig;
        this.isMultiSelectMode = this.iframeConfig.multiple;
      }

      // 初始加载根目录文件
      this.loadFiles();
    }
  });
</script>

<!-- 添加新建文件夹对话框 -->
<el-dialog
  title="新建文件夹"
  :visible.sync="createFolderDialog.visible"
  width="30%">
  <el-form :model="createFolderDialog.form">
    <el-form-item label="文件夹名称">
      <el-input v-model="createFolderDialog.form.name"></el-input>
    </el-form-item>
  </el-form>
  <span slot="footer">
    <el-button @click="createFolderDialog.visible = false">取消</el-button>
    <el-button type="primary" @click="submitCreateFolder">确定</el-button>
  </span>
</el-dialog>

<!-- 添加必要的样式 -->
<style>
.file-manager {
  background: #fff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.folder-tree {
  padding: 20px;
  border-right: 1px solid #EBEEF5;
  height: 100vh;
  overflow-y: auto;
}

.file-toolbar {
  padding: 15px 20px;
  border-bottom: 1px solid #EBEEF5;
}

.file-list {
  padding: 20px;
  flex: 1;
  overflow-y: auto;
  height: calc(100vh - 130px);
}

.pagination-container {
  padding: 15px 20px;
  text-align: right;
  border-top: 1px solid #EBEEF5;
}
</style>
@endsection
