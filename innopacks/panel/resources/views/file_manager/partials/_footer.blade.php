@push('footer')
  <script>
    new Vue({
      el: '#app',
      created() {},
      mounted() {
        this.loadFiles();
        this.loadFolders();

        // 获取当前存储配置
        this.getStorageConfig();
        
        // 确保上传路径正确初始化
        this.updateUploadPath();
      },
      data() {
        return {
          files: [],
          selectedFiles: [],
          currentFolder: null,
          folders: [],
          defaultProps: {
            children: 'children',
            label: 'name'
          },
          folderDialog: {
            visible: false,
            form: {
              name: '',
              parent_id: ''
            }
          },
          pagination: {
            page: 1,
            per_page: 18,
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
            path: '/',
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
          },
          defaultExpandedKeys: ['/'], // 默认展开根节点
          renameDialog: {
            visible: false,
            form: {
              newName: '',
              extension: '', // 添加扩展名字段
              file: null
            }
          },
          moveDialog: {
            visible: false,
            targetPath: null
          },
          sortField: 'created', // 默认按时间排序
          sortOrder: 'desc',    // 默认降序
          contextMenu: {
            visible: false,
            style: {
              top: '0px',
              left: '0px'
            },
            file: null
          },
          copyDialog: {
            visible: false,
            targetPath: null
          },
          isMultiSelectMode: false, // 多选模式状态
          folderContextMenu: {
            visible: false,
            style: {
              top: '0px',
              left: '0px'
            },
            folder: null
          },
          folderRenameDialog: {
            visible: false,
            form: {
              newName: '',
              folder: null
            }
          },
          folderMoveDialog: {
            visible: false,
            targetPath: null,
            folder: null
          },
          isDragging: false,
          videoDialog: {
            visible: false,
            url: ''
          },
          isIframeMode: {{ json_encode($isIframe) }},
          fileType: '{{ $type }}',
          storageConfigDialog: {
            visible: false,
            driver: 'local',
            currentDriver: 'local',
          },
          storageOptions: [
            {
              value: 'local',
              label: "{{ __('panel/file_manager.local_storage') }}",
              desc: "{{ __('panel/file_manager.local_storage_desc') }}",
              icon: 'el-icon-monitor',
            },
            {
              value: 'oss',
              label: "{{ __('panel/file_manager.alibaba_oss') }}",
              desc: "{{ __('panel/file_manager.alibaba_oss_desc') }}",
              icon: 'el-icon-cloudy',
            },
            {
              value: 'cos',
              label: "{{ __('panel/file_manager.tencent_cos') }}",
              desc: "{{ __('panel/file_manager.tencent_cos_desc') }}",
              icon: 'el-icon-upload2',
            },
            {
              value: 'qiniu',
              label: "{{ __('panel/file_manager.qiniu') }}",
              desc: "{{ __('panel/file_manager.qiniu_desc') }}",
              icon: 'el-icon-data-line',
            },
            {
              value: 's3',
              label: "{{ __('panel/file_manager.aws_s3') }}",
              desc: "{{ __('panel/file_manager.aws_s3_desc') }}",
              icon: 'el-icon-coordinate',
            },
            {
              value: 'obs',
              label: "{{ __('panel/setting.storage_driver_obs') }}",
              desc: 'Huawei Cloud OBS',
              icon: 'el-icon-office-building',
            },
            {
              value: 'r2',
              label: "{{ __('panel/setting.storage_driver_r2') }}",
              desc: 'Cloudflare R2',
              icon: 'el-icon-lightning',
            },
            {
              value: 'minio',
              label: "{{ __('panel/setting.storage_driver_minio') }}",
              desc: 'MinIO',
              icon: 'el-icon-box',
            },
          ].filter(function(opt) {
            var enabled = window.fileManagerConfig.enabledDrivers || ['local'];
            return enabled.indexOf(opt.value) !== -1;
          }),
        }
      },
      methods: {
        refreshAll() {
          this.loadFolders();
          this.loadFiles();
        },
        updateUploadPath() {
          // 更新上传路径，确保始终有有效值
          this.uploadData.path = this.currentFolder ? this.currentFolder.path : '/';
        },
        onUploadDialogOpen() {
          // 对话框打开时确保路径正确
          this.updateUploadPath();
        },
        uploadFile() {
          // 确保路径正确设置
          this.updateUploadPath();
          this.uploadDialog.visible = true;
        },
        createFolder() {
          this.folderDialog.visible = true;
        },
        submitCreateFolder() {
          if (!this.folderDialog.form.name) {
            this.$message.warning("{{ __('panel/file_manager.prompt_enter_folder_name') }}");
            return;
          }

          http.post('file_manager/directories', {
            name: this.folderDialog.form.name,
            parent_id: this.currentFolder ? this.currentFolder.path : '/'
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.create_success') }}");
              this.folderDialog.visible = false;
              this.folderDialog.form.name = '';
              // 重新加载文件夹树
              this.loadFolders();
            } else {
              this.$message.error(res.message || "{{ __('panel/file_manager.create_fail') }}");
            }
          }).catch(err => {
            this.$message.error("{{ __('panel/file_manager.create_fail_prefix') }}" + err.message);
          });
        },
        deleteFiles() {
          if (!this.selectedFiles.length) return;

          this.$confirm("{{ __('panel/file_manager.confirm_delete_selected_files') }}", "{{ __('panel/file_manager.prompt') }}", {
            confirmButtonText: "{{ __('panel/file_manager.ok') }}",
            cancelButtonText: "{{ __('panel/file_manager.cancel') }}",
            type: 'warning'
          }).then(() => {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            // 获取选中文件的文件名列表
            const fileNames = this.selectedFiles.map(fileId => {
              const file = this.files.find(f => f.id === fileId);
              return file ? file.name : null;
            }).filter(name => name !== null);

            http.delete('file_manager/files', {
              data: {
                path: currentPath,
                files: fileNames
              }
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/file_manager.delete_success") }}');
                this.selectedFiles = [];
                this.loadFiles(currentPath);
              }
            });
          });
        },
        moveFiles() {
          if (!this.selectedFiles.length) return;
          this.moveDialog.visible = true;
        },
        copyFiles() {
          if (!this.selectedFiles.length) return;
          this.copyDialog.visible = true;
        },
        handleFileClick(event, file) {
          if (this.isDragging) return;

          if (this.isIframeMode && !file.is_dir) {
            if (window.fileManagerConfig.multiple) {
              this.toggleFileSelect(file);
            } else {
              window.parent.fileManagerCallback(file);
              parent.layer.closeAll();
            }
          } else {
            const fileId = file.id || file.path;
            const index = this.selectedFiles.indexOf(fileId);

            // 如果是文件夹，保持单选模式
            if (file.is_dir) {
              if (index === -1) {
                this.selectedFiles = [fileId];
              } else {
                this.selectedFiles = [];
              }
            } else {
              // 如果是文件，只从选中列表中移除当前文件
              if (index !== -1) {
                this.selectedFiles.splice(index, 1);
              } else {
                this.selectedFiles.push(fileId);
              }
            }
          }
        },
        handleNodeClick(data) {
          this.currentFolder = data;
          this.loadFiles(data.path);
          // 更新上传路径
          this.updateUploadPath();
        },
        loadFiles(path = null) {
          this.loading = true;
          const currentPath = path !== null ? path : (this.currentFolder ? this.currentFolder.path : '/');
          
          const params = {
            page: this.pagination.page,
            per_page: this.pagination.per_page,
            base_folder: currentPath,
            sort: this.sortField,
            order: this.sortOrder
          };

          http.get('file_manager/files', {
              params
            })
            .then(res => {
              // 处理文件列表数据

              this.files = res.items.map(file => ({
                ...file,
                id: file.id || file.path, // 确保每个文件都有唯一标识
                selected: false,
                preview_url: file.url, // 保存预览URL（缩略图）
                url: file.url, // 缩略图URL（用于列表显示）
                origin_url: file.origin_url // 原图URL（用于插入编辑器）
              }));

              // 更新分页信息
              this.pagination.total = res.total;
              this.pagination.page = res.page;
            })
            .catch(err => {
              this.$message.error(err.message);
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

        // 上传文件方法
        uploadFileToServer(file, path, type) {
          // 验证路径参数
          if (!path) {
            this.$message.error('{{ __("panel/file_manager.upload_path_empty") }}');
            return Promise.reject(new Error('{{ __("panel/file_manager.upload_path_empty") }}'));
          }

          const formData = new FormData();
          formData.append('file', file);
          formData.append('path', path);
          formData.append('type', type);
          
          return http.post('file_manager/upload', formData)
            .then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/file_manager.upload_success") }}');
                this.uploadDialog.visible = false;
                // 上传成功后，重置到第一页并重新加载文件列表，确保新上传的文件显示在最前面
                this.pagination.page = 1;
                this.loadFiles();
              } else {
                this.$message.error(res.message || '{{ __("panel/file_manager.upload_failed") }}');
              }
            })
            .catch(err => {
              this.$message.error(err.message);
            });
        },

        beforeUpload(file) {
          // 验证路径参数
          if (!this.uploadData.path) {
            this.$message.error('{{ __("panel/file_manager.upload_path_reset") }}');
            return false;
          }

          const isImage = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type);
          const isVideo = ['video/mp4', 'video/webm', 'video/ogg'].includes(file.type)
          const isDoc = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
          ].includes(file.type)
          if (!isImage && !isVideo && !isDoc) {
            this.$message.error('{{ __("panel/file_manager.image_video_only") }}');
            return false;
          }

          // 将PHP ini格式的大小转换为字节
          function iniSizeToBytes(size) {
            if (!size || size === 'unknown') return 0;
            
            const unit = size.slice(-1).toUpperCase();
            const value = parseInt(size.slice(0, -1));
            
            switch (unit) {
              case 'K':
                return value * 1024;
              case 'M':
                return value * 1024 * 1024;
              case 'G':
                return value * 1024 * 1024 * 1024;
              default:
                return parseInt(size);
            }
          }
          
          // 获取服务器配置的上传限制
          const uploadMaxFileSizeBytes = iniSizeToBytes(window.fileManagerConfig.uploadMaxFileSize);
          const postMaxSizeBytes = iniSizeToBytes(window.fileManagerConfig.postMaxSize);
          
          // 使用较小的限制值
          const serverMaxSizeBytes = Math.min(uploadMaxFileSizeBytes, postMaxSizeBytes);
          
          // 检查文件大小
          if (serverMaxSizeBytes > 0 && file.size > serverMaxSizeBytes) {
            const maxSizeMB = (serverMaxSizeBytes / 1024 / 1024).toFixed(2);
            this.$message.error(`{{ __("panel/file_manager.file_too_large") }} ${maxSizeMB}MB!`);
            return false;
          }
          if (isVideo || isDoc) {
            const type = isVideo ? 'videos' : 'application';
            this.uploadFileToServer(file, this.uploadData.path, type);
            return false;
          } else {
            // 根据 enableCrop 变量决定是否进行裁剪
            if (window.fileManagerConfig.enableCrop) {
              this.cropImage(file);
            } else {
              this.uploadFileToServer(file, this.uploadData.path, 'images');
            }
            return false;
          }
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
            <button class="el-button el-button--default el-button--small cancel-btn">{{ __("panel/file_manager.cancel_btn") }}</button>
            <button class="el-button el-button--primary el-button--small confirm-btn">{{ __("panel/file_manager.ok_btn") }}</button>
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
                this.uploadFileToServer(blob, this.uploadData.path, 'images')
                  .then(() => {
                    // 上传成功后，重置到第一页并重新加载文件列表，确保新上传的文件显示在最前面
                    this.pagination.page = 1;
                    this.loadFiles();
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
            // 上传成功后，重置到第一页并重新加载文件列表，确保新上传的文件显示在最前面
            this.pagination.page = 1;
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
          this.$message.error(err.message || '{{ __("panel/file_manager.upload_failed") }}');
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
        },

        // 获取文件夹树
        loadFolders() {
          http.get('file_manager/directories').then(res => {
            const raw = Array.isArray(res.data) ? res.data : [];

            // Normalize items: ensure each node has id, name, path, children
            const normalize = (items) => items.map(item => ({
              id: item.id || item.path,
              name: item.name,
              path: item.path,
              children: item.children ? normalize(item.children) : []
            }));

            // API returns tree with root node (children already nested)
            if (raw.length > 0 && raw[0].isRoot) {
              const root = raw[0];
              this.folders = [{
                id: '/',
                name: "{{ __('panel/file_manager.root_name') }}",
                path: '/',
                isRoot: true,
                children: root.children ? normalize(root.children) : []
              }];
            } else {
              // Fallback: flat or tree without root wrapper
              this.folders = [{
                id: '/',
                name: "{{ __('panel/file_manager.root_name') }}",
                path: '/',
                isRoot: true,
                children: normalize(raw)
              }];
            }

            // 默认选中根目录
            this.currentFolder = {
              id: '/',
              name: "{{ __('panel/file_manager.root_name') }}",
              path: '/'
            };

            // 设置默认展开的节点
            this.defaultExpandedKeys = ['/'];

            // 加载根目录的文件
            this.loadFiles('/');
          }).catch(err => {
            this.$message.error("{{ __('panel/file_manager.error_load_folders_prefix') }}" + err.message);
          });
        },

        // 重命名文件
        renameFile() {
          const file = this.contextMenu.file;
          this.renameDialog.form.file = file;
          // 分离文件名和扩展名
          const extension = file.name.split('.').pop();
          const nameWithoutExt = file.name.slice(0, -(extension.length + 1));
          this.renameDialog.form.newName = nameWithoutExt;
          this.renameDialog.form.extension = extension;
          this.renameDialog.visible = true;
          this.hideContextMenu();
        },

        // 重命名选中的文件
        renameSelectedFile() {
          if (this.selectedFiles.length !== 1) return;

          const selectedFile = this.files.find(file => file.id === this.selectedFiles[0]);
          if (selectedFile) {
            this.renameDialog.form.file = selectedFile;
            // 分离文件名和扩展名
            const extension = selectedFile.name.split('.').pop();
            const nameWithoutExt = selectedFile.name.slice(0, -(extension.length + 1));
            this.renameDialog.form.newName = nameWithoutExt;
            this.renameDialog.form.extension = extension;
            this.renameDialog.visible = true;
          }
        },

        // 提交重命名
        submitRename() {
          if (!this.renameDialog.form.newName) {
            this.$message.warning("{{ __('panel/file_manager.enter_new_name') }}");
            return;
          }

          const file = this.renameDialog.form.file;
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          // 组合新的文件名
          const newFullName = `${this.renameDialog.form.newName}.${this.renameDialog.form.extension}`;

          http.post('file_manager/rename', {
            origin_name: currentPath + '/' + file.name,
            new_name: newFullName
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.rename_success') }}");
              this.renameDialog.visible = false;
              this.loadFiles(currentPath);
            }
          });
        },

        // 删除单个文件
        deleteFile() {
          const file = this.contextMenu.file;
          this.$confirm("{{ __('panel/file_manager.delete_file_confirm') }}", "{{ __('panel/file_manager.prompt') }}", {
            confirmButtonText: "{{ __('panel/file_manager.ok') }}",
            cancelButtonText: "{{ __('panel/file_manager.cancel') }}",
            type: 'warning'
          }).then(() => {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            http.delete('file_manager/files', {
              data: {
                path: currentPath,
                files: [file.name]
              }
            }).then(res => {
              if (res.success) {
                this.$message.success("{{ __('panel/file_manager.delete_success') }}");
                this.selectedFiles = [];
                this.loadFiles(currentPath);
              }
            });
          });
          this.hideContextMenu();
        },

        // 移动文件
        moveFile() {
          const file = this.contextMenu.file;
          // 保持单状态
          this.selectedFiles = [file.id || file.path];
          this.moveDialog.visible = true;
          this.hideContextMenu();
        },

        // 选择移动目标文件夹
        handleMoveTargetSelect(data) {
          this.moveDialog.targetPath = data.path;
        },

        // 提交移动
        submitMove() {
          if (!this.moveDialog.targetPath) {
            this.$message.warning("{{ __('panel/file_manager.select_target_folder') }}");
            return;
          }

          // 获取选中文的完整路径
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const files = this.selectedFiles.map(fileId => {
            const file = this.files.find(f => f.id === fileId);
            return currentPath + '/' + file.name;
          });

          http.post('file_manager/move_files', {
            files: files,
            dest_path: this.moveDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.move_success') }}");
              this.moveDialog.visible = false;
              this.selectedFiles = [];
              this.loadFiles(currentPath);
            }
          });
        },

        // 显示右键菜单
        showContextMenu(event, file) {
          event.preventDefault();
          // 右键点击时，清除之前的选择，只选中当前文件
          this.selectedFiles = [file.id || file.path];

          this.contextMenu.visible = true;
          this.contextMenu.style.top = event.clientY + 'px';
          this.contextMenu.style.left = event.clientX + 'px';
          this.contextMenu.file = file;

          // 点击其他地方关闭菜单
          document.addEventListener('click', this.hideContextMenu);
        },

        // 隐藏右键菜单
        hideContextMenu() {
          this.contextMenu.visible = false;
          document.removeEventListener('click', this.hideContextMenu);
        },

        // 复制单个文件
        copyFile() {
          const file = this.contextMenu.file;
          // 保持单选状态
          this.selectedFiles = [file.id || file.path];
          this.copyDialog.visible = true;
          this.hideContextMenu();
        },

        // 批量复制文件
        copyFiles() {
          if (!this.selectedFiles.length) return;
          this.copyDialog.visible = true;
        },

        // 提交复制
        submitCopy() {
          if (!this.copyDialog.targetPath) {
            this.$message.warning("{{ __('panel/file_manager.select_target_folder') }}");
            return;
          }

          // 获取选中文件的完整路径
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const files = this.selectedFiles.map(fileId => {
            const file = this.files.find(f => f.id === fileId);
            return currentPath + '/' + file.name;
          });

          http.post('file_manager/copy_files', {
            files: files,
            dest_path: this.copyDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.copy_success') }}");
              this.copyDialog.visible = false;
              this.selectedFiles = [];
              this.loadFiles(currentPath);
            }
          });
        },

        // 添加选择目标文件夹的方法
        handleCopyTargetSelect(data) {
          this.copyDialog.targetPath = data.path;
        },

        // 添加多选模式切换方法
        toggleMultiSelectMode() {
          this.isMultiSelectMode = !this.isMultiSelectMode;
          if (!this.isMultiSelectMode) {
            // 退出多选模式时清空选择
            this.selectedFiles = [];
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
        },

        // 全选功能
        selectAll() {
          if (this.selectedFiles.length === this.files.length) {
            // 如果已经全选，则取消全选
            this.selectedFiles = [];
          } else {
            // 否则全选
            this.selectedFiles = this.files.map(file => file.id || file.path);
          }
        },

        // 显示文件夹右键菜单
        showFolderContextMenu(event, data, node) {
          if (data.isRoot) return; // 根节点不显示右键菜单

          event.preventDefault();
          this.folderContextMenu.visible = true;
          this.folderContextMenu.style.top = event.clientY + 'px';
          this.folderContextMenu.style.left = event.clientX + 'px';
          this.folderContextMenu.folder = data;

          // 点击其他地方关闭菜单
          document.addEventListener('click', this.hideFolderContextMenu);
        },

        // 隐藏文件夹右键菜单
        hideFolderContextMenu() {
          this.folderContextMenu.visible = false;
          document.removeEventListener('click', this.hideFolderContextMenu);
        },

        // 重命名文件夹
        renameFolder() {
          const folder = this.folderContextMenu.folder;
          this.folderRenameDialog.form.folder = folder;
          this.folderRenameDialog.form.newName = folder.name;
          this.folderRenameDialog.visible = true;
          this.hideFolderContextMenu();
        },

        // 提交文件夹重命名
        submitFolderRename() {
          if (!this.folderRenameDialog.form.newName) {
            this.$message.warning("{{ __('panel/file_manager.enter_new_name') }}");
            return;
          }

          const folder = this.folderRenameDialog.form.folder;
          http.post('file_manager/rename', {
            origin_name: folder.path,
            new_name: this.folderRenameDialog.form.newName
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.rename_success') }}");
              this.folderRenameDialog.visible = false;
              // 重新加载文件夹树
              this.loadFolders();
            }
          });
        },

        // 删除文件夹
        deleteFolder() {
          const folder = this.folderContextMenu.folder;
          this.$confirm("{{ __('panel/file_manager.delete_folder_confirm') }}", "{{ __('panel/file_manager.prompt') }}", {
            confirmButtonText: "{{ __('panel/file_manager.ok') }}",
            cancelButtonText: "{{ __('panel/file_manager.cancel') }}",
            type: 'warning'
          }).then(() => {
            http.delete('file_manager/directories', {
              data: {
                name: folder.path
              }
            }).then(res => {
              if (res.success) {
                this.$message.success("{{ __('panel/file_manager.delete_success') }}");
                this.loadFolders();
              }
            });
          });
          this.hideFolderContextMenu();
        },

        // 显示移动文件夹对话框
        moveFolder() {
          const folder = this.folderContextMenu.folder;
          this.folderMoveDialog.folder = folder;
          this.folderMoveDialog.visible = true;
          this.hideFolderContextMenu();
        },

        // 选择目标文件夹
        handleFolderMoveTargetSelect(data) {
          // 不能移动到自己或自己的子文件夹下
          if (data.path === this.folderMoveDialog.folder.path ||
            data.path.startsWith(this.folderMoveDialog.folder.path + '/')) {
            this.$message.warning("{{ __('panel/file_manager.cannot_move_to_self') }}");
            return;
          }
          this.folderMoveDialog.targetPath = data.path;
        },

        // 提交文件夹移动
        submitFolderMove() {
          if (!this.folderMoveDialog.targetPath) {
            this.$message.warning("{{ __('panel/file_manager.select_target_folder') }}");
            return;
          }

          const folder = this.folderMoveDialog.folder;
          http.post('file_manager/move_directories', {
            source_path: folder.path,
            dest_path: this.folderMoveDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/file_manager.move_success') }}");
              this.folderMoveDialog.visible = false;
              // 重新加载文件夹树
              this.loadFolders();
            }
          });
        },

        // 处理文件双击
        handleFileDoubleClick(file) {
          if (file.is_dir) {
            // 如果是文件夹，进入该文件夹
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            const targetPath = currentPath === '/' ?
              '/' + file.name :
              currentPath + '/' + file.name;

            this.currentFolder = {
              id: targetPath,
              name: file.name,
              path: targetPath
            };

            // 将当前路径添加到展开的节点中
            if (!this.defaultExpandedKeys.includes(targetPath)) {
              this.defaultExpandedKeys.push(targetPath);
            }

            // 加载目标文件夹的内容
            this.loadFiles(targetPath);

            // 同步左侧树的选中状态
            this.$nextTick(() => {
              const treeComponent = this.$refs.folderTree;
              if (treeComponent) {
                treeComponent.setCurrentKey(targetPath);
              }
            });
          } else {
            // 如果是图片文件

            const mainApp = document.querySelector('#app').__vue__;
            if (mainApp && typeof mainApp.confirmSelection === 'function') {
              mainApp.confirmSelection();
            }
          }
        },

        // 处理文件拖拽结束
        handleDragEnd(evt) {
          const draggedFile = this.files[evt.oldIndex];
          const targetFolder = evt.to.dataset.path;

          if (targetFolder && draggedFile) {
            // 移动文件到目标文件夹
            this.moveFilesToFolder([draggedFile], targetFolder);
          }
        },

        // 移动文件到文件夹
        moveFilesToFolder(files, targetPath) {
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const fileNames = files.map(file => currentPath + '/' + file.name);

          http.post('file_manager/move_files', {
            files: fileNames,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/file_manager.moved_success") }}');
              this.loadFiles(currentPath);
            }
          });
        },

        // 处理树节点拖拽
        handleNodeDrop(draggingNode, dropNode, type) {
          if (type !== 'inner') return;

          const sourcePath = draggingNode.data.path;
          const targetPath = dropNode.data.path;

          // 检查是否拖放到当前所在的文件夹
          const sourceDir = this.getParentPath(sourcePath);
          if (sourcePath === targetPath || sourceDir === targetPath) {
            // 如果是拖放到当前文件夹，直接返回，不发送请求
            return;
          }

          http.post('file_manager/move_directories', {
            source_path: sourcePath,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/file_manager.moved_success") }}');
              this.loadFolders();
              if (this.currentFolder && this.currentFolder.path === sourcePath) {
                this.loadFiles(targetPath);
              }
            }
          }).catch(err => {
            this.loadFolders();
            this.$message.error(err.message || "{{ __('panel/file_manager.move_fail') }}");
          });
        },

        // 判断是否允许拖放
        handleAllowDrop(draggingNode, dropNode, type) {
          // 安全检查
          if (!draggingNode || !dropNode) return false;

          // 处理文件拖放
          if (!draggingNode.data) {
            return type === 'inner';
          }

          // 处理文件夹拖放
          if (dropNode.data.isRoot) {
            return type === 'inner';
          }
          if (draggingNode.data.path === dropNode.data.path) return false;
          if (dropNode.data.path.startsWith(draggingNode.data.path + '/')) return false;
          return type === 'inner';
        },

        // 判断节点是否可拖动
        handleAllowDrag(node) {
          // 根节点不可拖动
          return !node.data.isRoot;
        },

        // 处理拖拽结束
        handleNodeDragEnd(draggingNode, dropNode) {
          // 使用 nextTick 确保 DOM 更新完成
          this.$nextTick(() => {
            // 清理所有拖拽相关的样式
            document.querySelectorAll('.el-tree-node').forEach(node => {
              node.classList.remove('is-dragging', 'is-drop-inner');
            });
          });

          // 如果没有成功放置，重新加载文件夹树
          if (!dropNode) {
            this.loadFolders();
          }
        },

        // 开始拖拽时
        handleDragStart(node) {
          if (node && node.$el) {
            node.$el.classList.add('is-dragging');
          }
        },

        // 进入可放置目标时
        handleDragEnter(draggingNode, dropNode) {
          // 安全检查
          if (!dropNode || !dropNode.$el) return;

          if (this.handleAllowDrop(draggingNode, dropNode, 'inner')) {
            // 移除所有其他节点的拖拽样式
            document.querySelectorAll('.el-tree-node').forEach(node => {
              node.classList.remove('is-drop-inner');
            });
            // 添加当前节点的拖拽样式
            dropNode.$el.classList.add('is-drop-inner');
          }
        },

        // 离开放置目标时
        handleDragLeave(draggingNode, dropNode) {
          // 添加安全检查
          if (!dropNode || !dropNode.$el) return;

          dropNode.$el.classList.remove('is-drop-inner');
        },

        // 文件开始拖拽
        handleFileDragStart(event, file) {
          this.isDragging = true;
          this.draggedFile = file;
          event.dataTransfer.effectAllowed = 'move';
          event.target.classList.add('dragging');
        },

        // 文件拖拽中
        handleFileDrag(event) {
          // 可以添加拖拽过程中的视觉效果
        },

        // 文件拖拽结束
        handleFileDragEnd(event) {
          this.isDragging = false;
          this.draggedFile = null;
          event.target.classList.remove('dragging');
          document.querySelectorAll('.file-card').forEach(card => {
            card.classList.remove('drag-over');
          });
        },
        handleConfirm() {
          // 获取主 Vue 实例并调用其方法
          const mainApp = document.querySelector('#app').__vue__;
          if (mainApp && typeof mainApp.confirmSelection === 'function') {
            mainApp.confirmSelection();
          }
        },
        // 树节点接收拖拽进入
        handleTreeDragEnter(event, node, data) {
          if (!this.isDragging || !this.draggedFile) return;

          // 文件夹拖拽检查是否是同一个文件夹
          if (this.draggedFile.is_dir) {
            // 获取当前拖拽文件夹的完整路径
            const draggedPath = this.currentFolder.path + '/' + this.draggedFile.name;

            // 如果是拖到自己或者自己的父文件夹，直接返回
            if (draggedPath === data.path || data.path.startsWith(draggedPath + '/')) {
              return;
            }

            // 如果是拖到当前所在文件夹，直接返回
            if (data.path === this.currentFolder.path) {
              return;
            }
          }

          // 清除所有高亮样式
          document.querySelectorAll('.el-tree-node').forEach(node => {
            node.classList.remove('is-drop-target');
          });
          this.$refs.folderTree.$el.classList.remove('is-drop-target');

          if (data.isRoot) {
            // 如果是根目录，高亮整个树容器
            this.$refs.folderTree.$el.classList.add('is-drop-target');
          } else {
            // 如果是普通文件夹，高亮当前节点
            const treeNode = event.target.closest('.el-tree-node');
            if (treeNode) {
              treeNode.classList.add('is-drop-target');
            }
          }
        },

        // 处理树节点离开拖拽
        handleTreeDragLeave(event, node) {
          // 检查鼠是否真的离开了目标元素及其子元素
          const relatedTarget = event.relatedTarget;
          const currentTarget = event.currentTarget;

          if (!currentTarget.contains(relatedTarget)) {
            const treeNode = event.target.closest('.el-tree-node');
            if (treeNode) {
              treeNode.classList.remove('is-drop-target');
            }
            // 移除根目录高亮
            this.$refs.folderTree.$el.classList.remove('is-drop-target');
          }
        },

        // 处理树节点放置
        handleTreeDrop(event, node, data) {
          // 移除所有高亮样式
          document.querySelectorAll('.el-tree-node').forEach(node => {
            node.classList.remove('is-drop-target');
          });
          this.$refs.folderTree.$el.classList.remove('is-drop-target');

          // 如果是从右侧拖来的文件
          if (this.isDragging && this.draggedFile) {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            const targetPath = data.path;

            // 检查是否拖放到当前所在文件夹
            if (currentPath === targetPath) {
              this.isDragging = false;
              this.draggedFile = null;
              return;
            }

            // 如果是文件夹且正在拖拽的也是文件夹，检查是否是同一个文件夹
            if (this.draggedFile.is_dir) {
              // 获取当前拖拽文件夹的完整路径
              const draggedPath = currentPath + '/' + this.draggedFile.name;

              // 如果是拖到自己或者自己的父文件夹，直接返回
              if (draggedPath === targetPath || targetPath.startsWith(draggedPath + '/')) {
                this.isDragging = false;
                this.draggedFile = null;
                return;
              }
            }

            const files = [currentPath + '/' + this.draggedFile.name];

            http.post('file_manager/move_files', {
              files: files,
              dest_path: targetPath
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/file_manager.moved_success") }}');
                this.loadFiles(currentPath);
              }
            }).catch(err => {
              this.$message.error(err.message || '{{ __("panel/file_manager.move_fail") }}');
            }).finally(() => {
              this.isDragging = false;
              this.draggedFile = null;
            });
            return;
          }

          // 处理文件夹树内部的拖拽
          if (node && data) {
            const sourcePath = node.data.path;
            const targetPath = data.path;

            // 检查是否拖放到当前所在的文件夹
            const sourceDir = this.getParentPath(sourcePath);
            if (sourcePath === targetPath || sourceDir === targetPath) {
              return;
            }

            http.post('file_manager/move_directories', {
              source_path: sourcePath,
              dest_path: targetPath
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/file_manager.moved_success") }}');
                this.loadFolders();
                if (this.currentFolder && this.currentFolder.path === sourcePath) {
                  this.loadFiles(targetPath);
                }
              }
            }).catch(err => {
              this.loadFolders();
              this.$message.error(err.message || '{{ __("panel/file_manager.move_fail") }}');
            });
          }
        },

        // 文件拖入目标
        handleFileDragEnter(event, file) {
          // 如果目标不是文件夹，或者是自己，不允许拖入
          if (!this.isDragging || !this.draggedFile || !file.is_dir ||
            this.draggedFile.id === file.id ||
            this.draggedFile.path === file.path) {
            return;
          }

          const card = event.target.closest('.file-card');
          if (card) {
            // 清除其他文件夹的样式
            document.querySelectorAll('.file-card').forEach(c => {
              c.classList.remove('drag-over');
            });
            // 添加当前文件夹的样式
            card.classList.add('drag-over');
          }
        },

        // 添加文件拖离目标的处理方法
        handleFileDragLeave(event) {
          const card = event.target.closest('.file-card');
          if (card) {
            card.classList.remove('drag-over');
          }
        },

        // 文件放置处理
        handleFileDrop(event, targetFile) {
          event.preventDefault();

          // 如果不是拖拽状态，或者目标不是文件夹，或者是拖拽到自己，直接返回
          if (!this.isDragging || !this.draggedFile || !targetFile.is_dir ||
            this.draggedFile.id === targetFile.id ||
            this.draggedFile.path === targetFile.path) {
            this.isDragging = false;
            this.draggedFile = null;
            document.querySelectorAll('.file-card').forEach(card => {
              card.classList.remove('drag-over');
            });
            return;
          }

          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const targetPath = currentPath === '/' ?
            '/' + targetFile.name :
            currentPath + '/' + targetFile.name;

          // 检查是否在同一个文件夹内拖放
          const draggedFilePath = this.draggedFile.path;
          const draggedFileDir = draggedFilePath.substring(0, draggedFilePath.lastIndexOf('/')) || '/';

          if (draggedFileDir === targetPath) {
            // 如果是在同一个文件夹内拖放，直接返回，不执行移动
            this.isDragging = false;
            this.draggedFile = null;
            document.querySelectorAll('.file-card').forEach(card => {
              card.classList.remove('drag-over');
            });
            return;
          }

          const files = [currentPath + '/' + this.draggedFile.name];

          http.post('file_manager/move_files', {
            files: files,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/file_manager.moved_success") }}');
              this.loadFiles(currentPath);
            }
          }).catch(err => {
            this.$message.error(err.message || '移动失败');
          }).finally(() => {
            this.isDragging = false;
            this.draggedFile = null;
            document.querySelectorAll('.file-card').forEach(card => {
              card.classList.remove('drag-over');
            });
          });
        },
        // 添加辅助方法（如果还没有的话）
        getParentPath(path) {
          if (!path) return '/';
          const parts = path.split('/');
          parts.pop();
          return parts.join('/') || '/';
        },
        // 处理文件选择
        handleFileSelect(file) {
          if (this.isIframeMode && window.parent.fileManagerCallback) {
            if (!this.isMultiSelectMode) {
              window.parent.fileManagerCallback(file);
              parent.layer.closeAll();
              return;
            }
            this.toggleFileSelect(file);
          }
        },
        // 确认选择（多选模式）
        confirmSelection() {
          if (this.isIframeMode && window.parent.fileManagerCallback) {
            if (this.selectedFiles.length === 0) {
              this.$message.warning('{{ __("panel/file_manager.select_at_least") }}');
              return;
            }
            const selectedFiles = this.files.filter(file =>
              this.selectedFiles.includes(file.id || file.path)
            );
            if (window.fileManagerConfig.multiple) {
              // 多选模式：返回数组
              window.parent.fileManagerCallback(selectedFiles);
            } else {
              // 单选模式：返回单个文件
              window.parent.fileManagerCallback(selectedFiles[0]);
            }
            parent.layer.closeAll();
          }
        },
        saveStorageConfig() {
          http.post('file_manager/storage_config', { driver: this.storageConfigDialog.driver })
            .then(res => {
              if (res.success) {
                this.$message.success(res.message || "{{ __('panel/file_manager.storage_config_saved') }}");
                this.storageConfigDialog.visible = false;
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                this.$message.error(res.message || "{{ __('panel/file_manager.storage_config_save_failed') }}");
              }
            })
            .catch(err => {
              this.$message.error(err.response?.data?.message || "{{ __('panel/file_manager.storage_config_save_failed') }}");
            });
        },

        getStorageConfig() {
          http.get('file_manager/storage_config')
            .then(res => {
              if (res.success && res.data) {
                this.storageConfigDialog.driver = res.data.driver || 'local';
                this.storageConfigDialog.currentDriver = res.data.driver || 'local';
              }
            })
            .catch(() => {
              this.storageConfigDialog.driver = 'local';
              this.storageConfigDialog.currentDriver = 'local';
            });
        },
        onSortChange() {
          this.pagination.page = 1;
          this.loadFiles();
        },

        playVideo(file) {
          this.videoDialog.url = file.origin_url || file.url;
          this.videoDialog.visible = true;
        },

        onVideoDialogClose() {
          this.videoDialog.url = '';
        },

        toggleSortField(field) {
          if (this.sortField === field) {
            this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
          } else {
            this.sortField = field;
            this.sortOrder = 'desc';
          }
          this.pagination.page = 1;
          this.loadFiles();
        },

        toggleSortOrder() {
          this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
          this.pagination.page = 1;
          this.loadFiles();
        },
      },
      beforeDestroy() {
        document.removeEventListener('click', this.hideContextMenu);
        document.removeEventListener('click', this.hideFolderContextMenu);
      }
    });
  </script>
@endpush
