@push('footer')
  <script>
    const __fmApp = Vue.createApp({
      created() {},
      mounted() {
        try { this.showFolders = localStorage.getItem('media_show_folders') === '1'; } catch (e) {}
        this.loadFolders();

        this.getStorageConfig();

        this.loadMediaStats();
      },
      data() {
        return {
          files: [],
          selectedFiles: [],
          currentFolder: null,
          rootName: "{{ __('panel/media.root_directory') }}",
          folders: [],
          foldersKey: 0,
          showFolders: false,
          folderCache: {},
          defaultProps: {
            children: 'children',
            label: 'name',
            isLeaf(data) {
              return data.hasChildren === false;
            }
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
          previewImageUrl: '',
          mediaStats: {
            total_files: 0,
            total_size: 0,
            size_readable: '—',
            by_disk: {},
            by_mime: [],
            loaded: false,
          },
          mediaDetail: {
            visible: false,
            loading: false,
            saving: false,
            data: null,
            altDraft: '',
          },
          uploadDialog: {
            visible: false
          },
          uploadUrl: '/api/panel/media/upload',
          uploadHeaders: {
            'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content')
          },
          uploadData: {
            path: '/',
            type: 'images'
          },
          cropperOptions: {
            viewMode: 1,
            autoCropArea: 1,
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
          defaultExpandedKeys: [], // unused - kept for compatibility
          renameDialog: {
            visible: false,
            form: {
              newName: '',
              extension: '',
              file: null
            }
          },
          moveDialog: {
            visible: false,
            targetPath: null
          },
          sortField: 'created',
          sortOrder: 'desc',
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
          isMultiSelectMode: false,
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
              label: "{{ __('panel/media.local_storage') }}",
              desc: "{{ __('panel/media.local_storage_desc') }}",
              icon: 'Monitor',
            },
            {
              value: 'oss',
              label: "{{ __('panel/media.alibaba_oss') }}",
              desc: "{{ __('panel/media.alibaba_oss_desc') }}",
              icon: 'Cloudy',
            },
            {
              value: 'cos',
              label: "{{ __('panel/media.tencent_cos') }}",
              desc: "{{ __('panel/media.tencent_cos_desc') }}",
              icon: 'Upload',
            },
            {
              value: 'qiniu',
              label: "{{ __('panel/media.qiniu') }}",
              desc: "{{ __('panel/media.qiniu_desc') }}",
              icon: 'TrendCharts',
            },
            {
              value: 's3',
              label: "{{ __('panel/media.aws_s3') }}",
              desc: "{{ __('panel/media.aws_s3_desc') }}",
              icon: 'Coordinate',
            },
            {
              value: 'obs',
              label: "{{ __('panel/setting.storage_driver_obs') }}",
              desc: 'Huawei Cloud OBS',
              icon: 'OfficeBuilding',
            },
            {
              value: 'r2',
              label: "{{ __('panel/setting.storage_driver_r2') }}",
              desc: 'Cloudflare R2',
              icon: 'Promotion',
            },
            {
              value: 'minio',
              label: "{{ __('panel/setting.storage_driver_minio') }}",
              desc: 'MinIO',
              icon: 'Box',
            },
          ].filter(function(opt) {
            var enabled = window.mediaConfig.enabledDrivers || ['local'];
            return enabled.indexOf(opt.value) !== -1;
          }),
          aiImageDialog: {
            visible: false,
            prompt: '',
            size: '1:1',
            quality: 'medium',
            loading: false,
            previewUrl: '',
            resultPath: '',
            modelInfo: '',
            referenceImage: '',
            referencePreviewUrl: '',
          },
          aiLabelPrompt: "{{ __('panel/media.ai_prompt') }}",
          aiLabelPromptPlaceholder: "{{ __('panel/media.ai_prompt_placeholder') }}",
          aiLabelSize: "{{ __('panel/media.ai_size') }}",
          aiLabelQuality: "{{ __('panel/media.ai_quality') }}",
          aiLabelLow: "{{ __('panel/media.ai_low') }}",
          aiLabelMedium: "{{ __('panel/media.ai_medium') }}",
          aiLabelHigh: "{{ __('panel/media.ai_high') }}",
          aiLabelGenerate: "{{ __('panel/media.ai_generate') }}",
          aiLabelGenerating: "{{ __('panel/media.ai_generating') }}",
        }
      },
      computed: {
        breadcrumbItems() {
          const items = [{ name: this.rootName, path: '/' }];
          const path = this.currentFolder ? this.currentFolder.path : '/';
          if (!path || path === '/') return items;
          const segments = path.split('/').filter(Boolean);
          let cur = '';
          for (const seg of segments) {
            cur += '/' + seg;
            items.push({ name: seg, path: cur });
          }
          return items;
        },
      },
      methods: {
        refreshAll() {
          this.refreshFolders();
          this.loadFiles();
        },
        toggleShowFolders() {
          this.showFolders = !this.showFolders;
          try { localStorage.setItem('media_show_folders', this.showFolders ? '1' : '0'); } catch (e) {}
          this.pagination.page = 1;
          this.loadFiles();
        },
        updateUploadPath() {
          this.uploadData.path = this.currentFolder ? this.currentFolder.path : '/';
        },
        onUploadDialogOpen() {
          this.updateUploadPath();
        },
        uploadFile() {
          this.updateUploadPath();
          this.uploadDialog.visible = true;
        },
        createFolder() {
          this.folderDialog.form.parent_id = this.currentFolder ? this.currentFolder.path : '/';
          this.folderDialog.visible = true;
        },
        createFolderUnder(node) {
          this.hideFolderContextMenu();
          this.folderDialog.form.parent_id = node && node.path
            ? node.path
            : (this.currentFolder ? this.currentFolder.path : '/');
          this.folderDialog.visible = true;
        },
        submitCreateFolder() {
          if (!this.folderDialog.form.name) {
            this.$message.warning("{{ __('panel/media.prompt_enter_folder_name') }}");
            return;
          }

          const target = this.folderDialog.form.parent_id || (this.currentFolder ? this.currentFolder.path : '/');
          http.post('media/directories', {
            name: this.folderDialog.form.name,
            parent_id: target
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.create_success') }}");
              this.folderDialog.visible = false;
              this.folderDialog.form.name = '';
              // Navigate into the target so the new subfolder shows up in both
              // the tree and the right pane. refreshFolders() clears the cache,
              // invalidates the target path and re-expands to it.
              const node = this.findNode(this.folders, target);
              this.currentFolder = node || {
                id: target,
                name: target === '/' ? this.rootName : target.split('/').filter(Boolean).pop(),
                path: target,
              };
              this.pagination.page = 1;
              this.loadFiles(target);
              this.updateUploadPath();
              this.saveCurrentPath(target);
              this.refreshFolders();
            } else {
              this.$message.error(res.message || "{{ __('panel/media.create_fail') }}");
            }
          }).catch(err => {
            this.$message.error("{{ __('panel/media.create_fail_prefix') }}" + err.message);
          });
        },
        deleteFiles() {
          if (!this.selectedFiles.length) return;

          this.$confirm("{{ __('panel/media.confirm_delete_selected_files') }}", "{{ __('panel/media.prompt') }}", {
            confirmButtonText: "{{ __('panel/media.ok') }}",
            cancelButtonText: "{{ __('panel/media.cancel') }}",
            type: 'warning'
          }).then(() => {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            const fileNames = this.selectedFiles.map(fileId => {
              const file = this.files.find(f => f.id === fileId);
              return file ? file.name : null;
            }).filter(name => name !== null);

            http.delete('media/files', {
              data: {
                path: currentPath,
                files: fileNames
              }
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/media.delete_success") }}');
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
            if (window.mediaConfig.multiple) {
              this.toggleFileSelect(file);
            } else {
              window.parent.mediaCallback(file);
              parent.layer.closeAll();
            }
          } else {
            const fileId = file.id || file.path;
            const index = this.selectedFiles.indexOf(fileId);

            if (file.is_dir) {
              if (index === -1) {
                this.selectedFiles = [fileId];
              } else {
                this.selectedFiles = [];
              }
            } else {
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
          this.updateUploadPath();
          this.saveCurrentPath(data.path);
        },
        navigateToBreadcrumb(path) {
          if (!path) return;
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          if (path === currentPath) return;
          const node = this.findNode(this.folders, path);
          this.currentFolder = node || {
            id: path,
            name: path.split('/').filter(Boolean).pop() || this.rootName,
            path: path,
          };
          this.pagination.page = 1;
          this.loadFiles(path);
          this.updateUploadPath();
          this.saveCurrentPath(path);
          this.$nextTick(() => this.expandToPath(path));
        },
        loadFiles(path = null) {
          this.loading = true;
          const currentPath = path !== null ? path : (this.currentFolder ? this.currentFolder.path : '/');
          
          const params = {
            page: this.pagination.page,
            per_page: this.pagination.per_page,
            base_folder: currentPath,
            include_directories: this.showFolders ? 1 : 0,
            sort: this.sortField,
            order: this.sortOrder
          };

          http.get('media/files', {
              params
            })
            .then(res => {
              this.files = res.items.map(file => ({
                ...file,
                id: file.id || file.path,
                selected: false,
                preview_url: file.url,
                url: file.url,
                origin_url: file.origin_url
              }));

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

        uploadFileToServer(file, path, type) {
          if (!path) {
            this.$message.error('{{ __("panel/media.upload_path_empty") }}');
            return Promise.reject(new Error('{{ __("panel/media.upload_path_empty") }}'));
          }

          const formData = new FormData();
          formData.append('file', file);
          formData.append('path', path);
          formData.append('type', type);
          
          return http.post('media/upload', formData)
            .then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/media.upload_success") }}');
                this.uploadDialog.visible = false;
                this.pagination.page = 1;
                this.loadFiles();
              } else {
                this.$message.error(res.message || '{{ __("panel/media.upload_failed") }}');
              }
            })
            .catch(err => {
              this.$message.error(err.message);
            });
        },

        beforeUpload(file) {
          if (!this.uploadData.path) {
            this.$message.error('{{ __("panel/media.upload_path_reset") }}');
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
            this.$message.error('{{ __("panel/media.image_video_only") }}');
            return false;
          }

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
          
          // Get server upload limits
          const uploadMaxFileSizeBytes = iniSizeToBytes(window.mediaConfig.uploadMaxFileSize);
          const postMaxSizeBytes = iniSizeToBytes(window.mediaConfig.postMaxSize);

          const serverMaxSizeBytes = Math.min(uploadMaxFileSizeBytes, postMaxSizeBytes);

          if (serverMaxSizeBytes > 0 && file.size > serverMaxSizeBytes) {
            const maxSizeMB = (serverMaxSizeBytes / 1024 / 1024).toFixed(2);
            this.$message.error(`{{ __("panel/media.file_too_large") }} ${maxSizeMB}MB!`);
            return false;
          }
          if (isVideo || isDoc) {
            const type = isVideo ? 'videos' : 'application';
            this.uploadFileToServer(file, this.uploadData.path, type);
            return false;
          } else {
            if (window.mediaConfig.enableCrop) {
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
            const mask = document.createElement('div');
            mask.className = 'cropper-mask';
            document.body.appendChild(mask);

            // Create crop dialog
            const dialog = document.createElement('div');
            dialog.className = 'cropper-dialog';
            dialog.innerHTML = `
          <div class="cropper-container">
            <img src="${e.target.result}">
          </div>
          <div class="cropper-controls">
            <button class="el-button el-button--default el-button--small cancel-btn">{{ __("panel/media.cancel_btn") }}</button>
            <button class="el-button el-button--primary el-button--small confirm-btn">{{ __("panel/media.ok_btn") }}</button>
          </div>
        `;

            document.body.appendChild(dialog);

            const image = dialog.querySelector('img');
            const cropper = new Cropper(image, this.cropperOptions);

            dialog.querySelector('.confirm-btn').onclick = () => {
              const canvas = cropper.getCroppedCanvas({
                width: 800,
                height: 800
              });

              // Determine output format based on original file type
              const mimeType = file.type && file.type !== 'image/gif' ? file.type : 'image/png';
              const ext = mimeType === 'image/jpeg' ? '.jpg' : (mimeType === 'image/webp' ? '.webp' : '.png');
              const baseName = file.name.replace(/\.[^.]+$/, '');
              const outputName = baseName + '_cropped' + ext;

              canvas.toBlob((blob) => {
                // Wrap blob in a File to preserve filename and MIME type
                const croppedFile = new File([blob], outputName, { type: mimeType });
                this.uploadFileToServer(croppedFile, this.uploadData.path, 'images')
                  .then(() => {
                    this.pagination.page = 1;
                    this.loadFiles();
                  })
                  .finally(() => {
                    this.cleanupDialog(dialog, mask);
                    this.uploadDialog.visible = false;
                  });
              });
            };

            dialog.querySelector('.cancel-btn').onclick = () => {
              this.cleanupDialog(dialog, mask);
            };
          };
          reader.readAsDataURL(file);
        },

        handleUploadSuccess(response, file, fileList) {
          if (response.success) {
            this.$message.success('{{ __("panel/media.upload_success") }}');
            this.pagination.page = 1;
            this.loadFiles();
          } else {
            this.$message.error(response.message || '{{ __("panel/media.upload_failed") }}');
          }

          if (fileList.every(file => file.status === 'success' || file.status === 'error')) {
            this.uploadDialog.visible = false;
          }
        },

        handleUploadError(err, file) {
          this.$message.error(err.message || '{{ __("panel/media.upload_failed") }}');
        },

        handleUploadProgress(event, file) {

        },

        cleanupDialog(dialog, mask) {
          if (dialog && dialog.parentNode) {
            dialog.parentNode.removeChild(dialog);
          }
          if (mask && mask.parentNode) {
            mask.parentNode.removeChild(mask);
          }
        },

        // Save current path to localStorage
        saveCurrentPath(path) {
          try { localStorage.setItem('media_last_path', path || '/'); } catch(e) {}
        },

        // Get last saved path
        getSavedPath() {
          try { return localStorage.getItem('media_last_path') || '/'; } catch(e) { return '/'; }
        },

        // Recursively find node by path
        findNode(nodes, path) {
          for (const n of nodes) {
            if (n.path === path) return n;
            if (n.children) {
              const found = this.findNode(n.children, path);
              if (found) return found;
            }
          }
          return null;
        },

        // Build ancestor key list for tree expansion
        ancestorKeys(path) {
          const keys = ['/'];
          let cur = '';
          for (const seg of path.split('/').filter(Boolean)) {
            cur += '/' + seg;
            keys.push(cur);
          }
          return keys;
        },

        // Init folder tree: lazy load top-level and restore last path
        loadFolders() {
          const savedPath = this.getSavedPath();
          this.currentFolder = { id: savedPath, name: savedPath === '/' ? '/' : savedPath.split('/').filter(Boolean).pop(), path: savedPath };
          this.loadFiles(savedPath);
          this.updateUploadPath();

          // Set root node as the initial tree data so user can click it to go back to root
          this.folders = [{
            id: '/',
            name: '{{ __("panel/media.root_directory") }}',
            path: '/',
            isRoot: true,
            hasChildren: true,
          }];

          // Restore last expanded/selected folder
          if (savedPath && savedPath !== '/') {
            this.$nextTick(() => this.expandToPath(savedPath));
          } else {
            // At root, expand root node to show first-level directories
            this.$nextTick(() => this.expandToPath('/'));
          }
        },

        // Expand tree to target path: pre-fetch in parallel, then expand level by level
        async expandToPath(targetPath) {
          const tree = this.$refs.folderTree;
          if (!tree) return;

          // Root only: just expand and highlight the root node
          if (targetPath === '/') {
            const node = tree.getNode('/');
            if (node) {
              if (!node.expanded && typeof node.expand === 'function') {
                node.expand();
              }
              tree.setCurrentKey('/');
            }
            return;
          }

          const hasSlash = targetPath.startsWith('/');
          const segments = targetPath.replace(/^\//, '').split('/').filter(Boolean);
          if (!segments.length) return;

          const keys = ['/'];
          let cur = '';
          for (const seg of segments) {
            cur += (cur ? '/' : '') + seg;
            keys.push(hasSlash ? '/' + cur : cur);
          }

          // 1. Pre-fetch all directory levels in parallel
          const allPaths = ['/'];
          let c = '';
          for (const seg of segments) {
            c += (c ? '/' : '') + seg;
            allPaths.push(hasSlash ? '/' + c : c);
          }

          const mapItem = item => ({
            id: item.id || item.path,
            name: item.name,
            path: item.path,
            hasChildren: item.hasChildren || false,
          });

          await Promise.all(allPaths.map(async (p) => {
            if (this.folderCache[p]) return;
            try {
              const res = await http.get('media/directories', { params: { base_folder: p } });
              const raw = Array.isArray(res.data) ? res.data : [];
              let items;
              if ((p === '/' || p === '') && raw.length > 0 && raw[0].isRoot) {
                items = (raw[0].children || []).map(mapItem);
              } else {
                items = raw.map(mapItem);
              }
              this.folderCache[p] = items;
            } catch (e) {
              this.folderCache[p] = [];
            }
          }));

          // 2. Expand level by level (cached data, instant resolve)
          let step = 0;
          const startTime = Date.now();

          const tryExpand = () => {
            if (step >= keys.length) {
              tree.setCurrentKey(keys[keys.length - 1]);
              return;
            }
            if (Date.now() - startTime > 8000) return;

            const node = tree.getNode(keys[step]);
            if (!node) {
              setTimeout(tryExpand, 100);
              return;
            }

            if (!node.expanded || !node.loaded) {
              if (typeof node.expand === 'function') {
                node.expand();
              } else {
                node.expanded = true;
              }
            }

            if (node.loaded) {
              step++;
              tryExpand();
            } else {
              setTimeout(tryExpand, 100);
            }
          };

          setTimeout(tryExpand, 100);
        },

        // Refresh tree after directory change.
        // NOTE: do NOT bump foldersKey here. Destroying and rebuilding the
        // el-tree is async, and expanding in $nextTick raced the rebuild,
        // leaving the tree blank after create/rename/move/delete. Instead we
        // keep the component mounted, invalidate already-loaded nodes along
        // the current path so the lazy loader re-fetches them (picking up the
        // new/renamed/deleted subfolder), then expand + highlight the path.
        refreshFolders() {
          this.folderCache = {};
          const target = this.currentFolder ? this.currentFolder.path : '/';
          this.$nextTick(() => {
            const tree = this.$refs.folderTree;
            if (!tree) return;
            const segs = (target || '/').replace(/^\//, '').split('/').filter(Boolean);
            const keys = ['/'];
            let cur = '';
            for (const s of segs) { cur += '/' + s; keys.push(cur); }
            keys.forEach(k => {
              const n = tree.getNode(k);
              if (n && n.loaded) n.loaded = false;
            });
            this.expandToPath(target || '/');
          });
        },

        // Shared lazy loader for main tree and dialog trees (cached)
        loadTreeNode(node, resolve) {
          // Virtual root call (no real data) — root node is in folders data, skip
          if (!node.data || !node.data.id) {
            resolve([]);
            return;
          }
          const path = node.data.path || '/';
          if (this.folderCache[path]) {
            resolve(this.folderCache[path]);
            return;
          }
          http.get('media/directories', { params: { base_folder: path } }).then(res => {
            const raw = Array.isArray(res.data) ? res.data : [];
            const mapItem = item => ({
              id: item.id || item.path,
              name: item.name,
              path: item.path,
              hasChildren: item.hasChildren || false,
            });
            let items;
            if ((path === '/' || path === '') && raw.length > 0 && raw[0].isRoot) {
              items = (raw[0].children || []).map(mapItem);
            } else {
              items = raw.map(mapItem);
            }
            this.folderCache[path] = items;
            resolve(items);
          }).catch(() => { resolve([]); });
        },

        renameFile() {
          const file = this.contextMenu.file;
          this.renameDialog.form.file = file;
          const extension = file.name.split('.').pop();
          const nameWithoutExt = file.name.slice(0, -(extension.length + 1));
          this.renameDialog.form.newName = nameWithoutExt;
          this.renameDialog.form.extension = extension;
          this.renameDialog.visible = true;
          this.hideContextMenu();
        },

        renameSelectedFile() {
          if (this.selectedFiles.length !== 1) return;

          const selectedFile = this.files.find(file => file.id === this.selectedFiles[0]);
          if (selectedFile) {
            this.renameDialog.form.file = selectedFile;
            const extension = selectedFile.name.split('.').pop();
            const nameWithoutExt = selectedFile.name.slice(0, -(extension.length + 1));
            this.renameDialog.form.newName = nameWithoutExt;
            this.renameDialog.form.extension = extension;
            this.renameDialog.visible = true;
          }
        },

        submitRename() {
          if (!this.renameDialog.form.newName) {
            this.$message.warning("{{ __('panel/media.enter_new_name') }}");
            return;
          }

          const file = this.renameDialog.form.file;
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const newFullName = `${this.renameDialog.form.newName}.${this.renameDialog.form.extension}`;

          http.post('media/rename', {
            origin_name: currentPath + '/' + file.name,
            new_name: newFullName
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.rename_success') }}");
              this.renameDialog.visible = false;
              this.loadFiles(currentPath);
            }
          });
        },

        deleteFile() {
          const file = this.contextMenu.file;
          this.$confirm("{{ __('panel/media.delete_file_confirm') }}", "{{ __('panel/media.prompt') }}", {
            confirmButtonText: "{{ __('panel/media.ok') }}",
            cancelButtonText: "{{ __('panel/media.cancel') }}",
            type: 'warning'
          }).then(() => {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            http.delete('media/files', {
              data: {
                path: currentPath,
                files: [file.name]
              }
            }).then(res => {
              if (res.success) {
                this.$message.success("{{ __('panel/media.delete_success') }}");
                this.selectedFiles = [];
                this.loadFiles(currentPath);
              }
            });
          });
          this.hideContextMenu();
        },

        moveFile() {
          const file = this.contextMenu.file;
          this.selectedFiles = [file.id || file.path];
          this.moveDialog.visible = true;
          this.hideContextMenu();
        },

        handleMoveTargetSelect(data) {
          this.moveDialog.targetPath = data.path;
        },

        submitMove() {
          if (!this.moveDialog.targetPath) {
            this.$message.warning("{{ __('panel/media.select_target_folder') }}");
            return;
          }

          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const files = this.selectedFiles.map(fileId => {
            const file = this.files.find(f => f.id === fileId);
            return currentPath + '/' + file.name;
          });

          http.post('media/move_files', {
            files: files,
            dest_path: this.moveDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.move_success') }}");
              this.moveDialog.visible = false;
              this.selectedFiles = [];
              this.loadFiles(currentPath);
            }
          });
        },

        showContextMenu(event, file) {
          event.preventDefault();
          // Reset first so Vue re-renders the menu for the newly clicked file.
          this.contextMenu.visible = false;
          this.contextMenu.file = null;

          // Right-click selects only the clicked file
          this.selectedFiles = [file.id || file.path];

          this.$nextTick(() => {
            this.contextMenu.file = file;
            this.contextMenu.style.top = event.clientY + 'px';
            this.contextMenu.style.left = event.clientX + 'px';
            this.contextMenu.visible = true;
          });

          // Hide menu on outside click
          document.addEventListener('click', this.hideContextMenu);
        },

        hideContextMenu() {
          this.contextMenu.visible = false;
          this.contextMenu.file = null;
          document.removeEventListener('click', this.hideContextMenu);
        },

        copyFile() {
          const file = this.contextMenu.file;
          this.selectedFiles = [file.id || file.path];
          this.copyDialog.visible = true;
          this.hideContextMenu();
        },

        copyFiles() {
          if (!this.selectedFiles.length) return;
          this.copyDialog.visible = true;
        },

        submitCopy() {
          if (!this.copyDialog.targetPath) {
            this.$message.warning("{{ __('panel/media.select_target_folder') }}");
            return;
          }

          // Get full paths of selected files
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const files = this.selectedFiles.map(fileId => {
            const file = this.files.find(f => f.id === fileId);
            return currentPath + '/' + file.name;
          });

          http.post('media/copy_files', {
            files: files,
            dest_path: this.copyDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.copy_success') }}");
              this.copyDialog.visible = false;
              this.selectedFiles = [];
              this.loadFiles(currentPath);
            }
          });
        },

        handleCopyTargetSelect(data) {
          this.copyDialog.targetPath = data.path;
        },

        // Toggle multi-select mode
        toggleMultiSelectMode() {
          this.isMultiSelectMode = !this.isMultiSelectMode;
          if (!this.isMultiSelectMode) {
            // Clear selection when exiting multi-select
            this.selectedFiles = [];
          }
        },

        toggleFileSelect(file) {
          const fileId = file.id || file.path;
          const index = this.selectedFiles.indexOf(fileId);
          if (index === -1) {
            this.selectedFiles.push(fileId);
          } else {
            this.selectedFiles.splice(index, 1);
          }
        },

        selectAll() {
          if (this.selectedFiles.length === this.files.length) {
            // Deselect all
            this.selectedFiles = [];
          } else {
            // Select all
            this.selectedFiles = this.files.map(file => file.id || file.path);
          }
        },

        // Show folder context menu
        showFolderContextMenu(event, data, node) {
          if (data.isRoot) return; // Root node has no context menu

          event.preventDefault();
          this.folderContextMenu.visible = true;
          this.folderContextMenu.style.top = event.clientY + 'px';
          this.folderContextMenu.style.left = event.clientX + 'px';
          this.folderContextMenu.folder = data;

          // Hide menu on outside click
          document.addEventListener('click', this.hideFolderContextMenu);
        },

        hideFolderContextMenu() {
          this.folderContextMenu.visible = false;
          document.removeEventListener('click', this.hideFolderContextMenu);
        },

        renameFolder() {
          const folder = this.folderContextMenu.folder;
          this.folderRenameDialog.form.folder = folder;
          this.folderRenameDialog.form.newName = folder.name;
          this.folderRenameDialog.visible = true;
          this.hideFolderContextMenu();
        },

        submitFolderRename() {
          if (!this.folderRenameDialog.form.newName) {
            this.$message.warning("{{ __('panel/media.enter_new_name') }}");
            return;
          }

          const folder = this.folderRenameDialog.form.folder;
          http.post('media/rename', {
            origin_name: folder.path,
            new_name: this.folderRenameDialog.form.newName
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.rename_success') }}");
              this.folderRenameDialog.visible = false;
              this.refreshFolders();
            }
          });
        },

        deleteFolder() {
          const folder = this.folderContextMenu.folder;
          this.$confirm("{{ __('panel/media.delete_folder_confirm') }}", "{{ __('panel/media.prompt') }}", {
            confirmButtonText: "{{ __('panel/media.ok') }}",
            cancelButtonText: "{{ __('panel/media.cancel') }}",
            type: 'warning'
          }).then(() => {
            http.delete('media/directories', {
              data: {
                name: folder.path
              }
            }).then(res => {
              if (res.success) {
                this.$message.success("{{ __('panel/media.delete_success') }}");

                // If the current view sits inside the deleted folder, fall back
                // to its parent so the path/bar/right pane stay valid.
                const cur = this.currentFolder ? this.currentFolder.path : '/';
                const deleted = folder.path;
                if (cur === deleted || cur.startsWith(deleted + '/')) {
                  const parent = deleted.lastIndexOf('/') > 0
                    ? deleted.substring(0, deleted.lastIndexOf('/'))
                    : '/';
                  this.currentFolder = {
                    id: parent,
                    name: parent === '/' ? this.rootName : parent.split('/').filter(Boolean).pop(),
                    path: parent,
                  };
                  this.loadFiles(parent);
                  this.saveCurrentPath(parent);
                } else {
                  this.loadFiles();
                }

                this.refreshFolders();
              }
            });
          });
          this.hideFolderContextMenu();
        },

        moveFolder() {
          const folder = this.folderContextMenu.folder;
          this.folderMoveDialog.folder = folder;
          this.folderMoveDialog.visible = true;
          this.hideFolderContextMenu();
        },

        handleFolderMoveTargetSelect(data) {
          // Cannot move into self or child folder
          if (data.path === this.folderMoveDialog.folder.path ||
            data.path.startsWith(this.folderMoveDialog.folder.path + '/')) {
            this.$message.warning("{{ __('panel/media.cannot_move_to_self') }}");
            return;
          }
          this.folderMoveDialog.targetPath = data.path;
        },

        submitFolderMove() {
          if (!this.folderMoveDialog.targetPath) {
            this.$message.warning("{{ __('panel/media.select_target_folder') }}");
            return;
          }

          const folder = this.folderMoveDialog.folder;
          http.post('media/move_directories', {
            source_path: folder.path,
            dest_path: this.folderMoveDialog.targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success("{{ __('panel/media.move_success') }}");
              this.folderMoveDialog.visible = false;
              this.refreshFolders();
            }
          });
        },

        // Handle file double-click
        handleFileDoubleClick(file) {
          if (file.is_dir) {
            // Enter subfolder
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            const targetPath = currentPath === '/' ?
              '/' + file.name :
              currentPath + '/' + file.name;

            this.currentFolder = {
              id: targetPath,
              name: file.name,
              path: targetPath
            };

            if (!this.defaultExpandedKeys.includes(targetPath)) {
              this.defaultExpandedKeys.push(targetPath);
            }

            this.loadFiles(targetPath);
            this.saveCurrentPath(targetPath);

            this.$nextTick(() => {
              const treeComponent = this.$refs.folderTree;
              if (treeComponent) {
                treeComponent.setCurrentKey(targetPath);
              }
            });
          } else {

            this.confirmSelection();
          }
        },

        handleDragEnd(evt) {
          const draggedFile = this.files[evt.oldIndex];
          const targetFolder = evt.to.dataset.path;

          if (targetFolder && draggedFile) {
            this.moveFilesToFolder([draggedFile], targetFolder);
          }
        },

        moveFilesToFolder(files, targetPath) {
          const currentPath = this.currentFolder ? this.currentFolder.path : '/';
          const fileNames = files.map(file => currentPath + '/' + file.name);

          http.post('media/move_files', {
            files: fileNames,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/media.moved_success") }}');
              this.loadFiles(currentPath);
            }
          });
        },

        handleNodeDrop(draggingNode, dropNode, type) {
          if (type !== 'inner') return;

          const sourcePath = draggingNode.data.path;
          const targetPath = dropNode.data.path;

          const sourceDir = this.getParentPath(sourcePath);
          if (sourcePath === targetPath || sourceDir === targetPath) {
            // Skip if same folder
            return;
          }

          http.post('media/move_directories', {
            source_path: sourcePath,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/media.moved_success") }}');
              this.refreshFolders();
              if (this.currentFolder && this.currentFolder.path === sourcePath) {
                this.loadFiles(targetPath);
              }
            }
          }).catch(err => {
            this.refreshFolders();
            this.$message.error(err.message || "{{ __('panel/media.move_fail') }}");
          });
        },

        handleAllowDrop(draggingNode, dropNode, type) {
          // Safety check
          if (!draggingNode || !dropNode) return false;

          if (!draggingNode.data) {
            return type === 'inner';
          }

          if (dropNode.data.isRoot) {
            return type === 'inner';
          }
          if (draggingNode.data.path === dropNode.data.path) return false;
          if (dropNode.data.path.startsWith(draggingNode.data.path + '/')) return false;
          return type === 'inner';
        },

        handleAllowDrag(node) {
          // Root node is not draggable
          return !node.data.isRoot;
        },

        handleNodeDragEnd(draggingNode, dropNode) {
          // Clean up drag styles after DOM update
          this.$nextTick(() => {
            document.querySelectorAll('.el-tree-node').forEach(node => {
              node.classList.remove('is-dragging', 'is-drop-inner');
            });
          });

          // Refresh if drop failed
          if (!dropNode) {
            this.refreshFolders();
          }
        },

        handleDragStart(node) {
          if (node && node.$el) {
            node.$el.classList.add('is-dragging');
          }
        },

        handleDragEnter(draggingNode, dropNode) {
          // Safety check
          if (!dropNode || !dropNode.$el) return;

          if (this.handleAllowDrop(draggingNode, dropNode, 'inner')) {
            // Clear drag styles from other nodes
            document.querySelectorAll('.el-tree-node').forEach(node => {
              node.classList.remove('is-drop-inner');
            });
            // Add drag style to current node
            dropNode.$el.classList.add('is-drop-inner');
          }
        },

        handleDragLeave(draggingNode, dropNode) {
          // Safety check
          if (!dropNode || !dropNode.$el) return;

          dropNode.$el.classList.remove('is-drop-inner');
        },

        handleFileDragStart(event, file) {
          this.isDragging = true;
          this.draggedFile = file;
          event.dataTransfer.effectAllowed = 'move';
          event.target.classList.add('dragging');
        },

        handleFileDrag(event) {
        },

        handleFileDragEnd(event) {
          this.isDragging = false;
          this.draggedFile = null;
          event.target.classList.remove('dragging');
          document.querySelectorAll('.file-card').forEach(card => {
            card.classList.remove('drag-over');
          });
        },
        handleConfirm() {
          this.confirmSelection();
        },
        // Handle drag enter on tree node
        handleTreeDragEnter(event, node, data) {
          if (!this.isDragging || !this.draggedFile) return;

          if (this.draggedFile.is_dir) {
            const draggedPath = this.currentFolder.path + '/' + this.draggedFile.name;

            // Skip if dropping into self or parent
            if (draggedPath === data.path || data.path.startsWith(draggedPath + '/')) {
              return;
            }

            // Skip if current folder
            if (data.path === this.currentFolder.path) {
              return;
            }
          }

          // Clear all highlight styles
          document.querySelectorAll('.el-tree-node').forEach(node => {
            node.classList.remove('is-drop-target');
          });
          this.$refs.folderTree.$el.classList.remove('is-drop-target');

          if (data.isRoot) {
            // Highlight entire tree container for root
            this.$refs.folderTree.$el.classList.add('is-drop-target');
          } else {
            const treeNode = event.target.closest('.el-tree-node');
            if (treeNode) {
              treeNode.classList.add('is-drop-target');
            }
          }
        },

        handleTreeDragLeave(event, node) {
          // Check if mouse truly left the target element
          const relatedTarget = event.relatedTarget;
          const currentTarget = event.currentTarget;

          if (!currentTarget.contains(relatedTarget)) {
            const treeNode = event.target.closest('.el-tree-node');
            if (treeNode) {
              treeNode.classList.remove('is-drop-target');
            }
            // Remove root highlight
            this.$refs.folderTree.$el.classList.remove('is-drop-target');
          }
        },

        // Handle drop on tree node
        handleTreeDrop(event, node, data) {
          // Clear all highlight styles
          document.querySelectorAll('.el-tree-node').forEach(node => {
            node.classList.remove('is-drop-target');
          });
          this.$refs.folderTree.$el.classList.remove('is-drop-target');

          // File dropped from right panel
          if (this.isDragging && this.draggedFile) {
            const currentPath = this.currentFolder ? this.currentFolder.path : '/';
            const targetPath = data.path;

            // Skip if same folder
            if (currentPath === targetPath) {
              this.isDragging = false;
              this.draggedFile = null;
              return;
            }

            // Skip if dragging folder into itself or child
            if (this.draggedFile.is_dir) {
              const draggedPath = currentPath + '/' + this.draggedFile.name;

              // Skip if self or parent
              if (draggedPath === targetPath || targetPath.startsWith(draggedPath + '/')) {
                this.isDragging = false;
                this.draggedFile = null;
                return;
              }
            }

            const files = [currentPath + '/' + this.draggedFile.name];

            http.post('media/move_files', {
              files: files,
              dest_path: targetPath
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/media.moved_success") }}');
                this.loadFiles(currentPath);
              }
            }).catch(err => {
              this.$message.error(err.message || '{{ __("panel/media.move_fail") }}');
            }).finally(() => {
              this.isDragging = false;
              this.draggedFile = null;
            });
            return;
          }

          // Tree internal drag-and-drop
          if (node && data) {
            const sourcePath = node.data.path;
            const targetPath = data.path;

            const sourceDir = this.getParentPath(sourcePath);
            if (sourcePath === targetPath || sourceDir === targetPath) {
              return;
            }

            http.post('media/move_directories', {
              source_path: sourcePath,
              dest_path: targetPath
            }).then(res => {
              if (res.success) {
                this.$message.success('{{ __("panel/media.moved_success") }}');
                this.refreshFolders();
                if (this.currentFolder && this.currentFolder.path === sourcePath) {
                  this.loadFiles(targetPath);
                }
              }
            }).catch(err => {
              this.refreshFolders();
              this.$message.error(err.message || '{{ __("panel/media.move_fail") }}');
            });
          }
        },

        // Handle file drag enter on target
        handleFileDragEnter(event, file) {
          // Skip if not a folder or is self
          if (!this.isDragging || !this.draggedFile || !file.is_dir ||
            this.draggedFile.id === file.id ||
            this.draggedFile.path === file.path) {
            return;
          }

          const card = event.target.closest('.file-card');
          if (card) {
            // Clear other folder styles
            document.querySelectorAll('.file-card').forEach(c => {
              c.classList.remove('drag-over');
            });
            // Add drag-over style
            card.classList.add('drag-over');
          }
        },

        handleFileDragLeave(event) {
          const card = event.target.closest('.file-card');
          if (card) {
            card.classList.remove('drag-over');
          }
        },

        // Handle file drop
        handleFileDrop(event, targetFile) {
          event.preventDefault();

          // Skip if not dragging or not a folder or self
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

          // Skip if dropping in same folder
          const draggedFilePath = this.draggedFile.path;
          const draggedFileDir = draggedFilePath.substring(0, draggedFilePath.lastIndexOf('/')) || '/';

          if (draggedFileDir === targetPath) {
            this.isDragging = false;
            this.draggedFile = null;
            document.querySelectorAll('.file-card').forEach(card => {
              card.classList.remove('drag-over');
            });
            return;
          }

          const files = [currentPath + '/' + this.draggedFile.name];

          http.post('media/move_files', {
            files: files,
            dest_path: targetPath
          }).then(res => {
            if (res.success) {
              this.$message.success('{{ __("panel/media.moved_success") }}');
              this.loadFiles(currentPath);
            }
          }).catch(err => {
            this.$message.error(err.message || '{{ __("panel/media.move_fail") }}');
          }).finally(() => {
            this.isDragging = false;
            this.draggedFile = null;
            document.querySelectorAll('.file-card').forEach(card => {
              card.classList.remove('drag-over');
            });
          });
        },
        getParentPath(path) {
          if (!path) return '/';
          const parts = path.split('/');
          parts.pop();
          return parts.join('/') || '/';
        },
        handleFileSelect(file) {
          if (this.isIframeMode && window.parent.mediaCallback) {
            if (!this.isMultiSelectMode) {
              window.parent.mediaCallback(file);
              parent.layer.closeAll();
              return;
            }
            this.toggleFileSelect(file);
          }
        },
        // Confirm selection (multi-select mode)
        confirmSelection() {
          if (this.isIframeMode && window.parent.mediaCallback) {
            if (this.selectedFiles.length === 0) {
              this.$message.warning('{{ __("panel/media.select_at_least") }}');
              return;
            }
            const selectedFiles = this.files.filter(file =>
              this.selectedFiles.includes(file.id || file.path)
            );
            if (window.mediaConfig.multiple) {
              window.parent.mediaCallback(selectedFiles);
            } else {
              window.parent.mediaCallback(selectedFiles[0]);
            }
            parent.layer.closeAll();
          }
        },
        saveStorageConfig() {
          http.post('media/storage_config', { driver: this.storageConfigDialog.driver })
            .then(res => {
              if (res.success) {
                this.$message.success(res.message || "{{ __('panel/media.storage_config_saved') }}");
                this.storageConfigDialog.visible = false;
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                this.$message.error(res.message || "{{ __('panel/media.storage_config_save_failed') }}");
              }
            })
            .catch(err => {
              this.$message.error(err.response?.data?.message || "{{ __('panel/media.storage_config_save_failed') }}");
            });
        },

        getStorageConfig() {
          http.get('media/storage_config')
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

        showPreview(file) {
          this.previewImageUrl = file.origin_url || file.url;
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

        generateAIImage() {
          var self = this;
          if (!this.aiImageDialog.prompt.trim()) {
            ElementPlus.ElMessage.warning('{{ __("panel/media.ai_enter_prompt") }}');
            return;
          }
          this.aiImageDialog.loading = true;
          this.aiImageDialog.previewUrl = '';
          this.aiImageDialog.resultPath = '';
          var savePath = this.currentFolder ? this.currentFolder.id.replace(/^\//, '') : '';
          if (!savePath) savePath = 'ai-images';
          var params = {
            prompt: this.aiImageDialog.prompt,
            size: this.aiImageDialog.size,
            quality: this.aiImageDialog.quality,
            save_path: savePath,
          };
          if (this.aiImageDialog.referenceImage) {
            params.reference_image = this.aiImageDialog.referenceImage;
          }
          http.post('ai/generate_image', params).then(function(res) {
            var data = res.data;
            if (data.status === 'success' || data.data) {
              var result = data.data || data;
              self.aiImageDialog.previewUrl = result.url || result.origin_url;
              self.aiImageDialog.resultPath = result.path;
              ElementPlus.ElMessage.success('{{ __("panel/media.ai_success") }}');
            } else {
              ElementPlus.ElMessage.error(data.message || '{{ __("panel/media.ai_failed") }}');
            }
          }).catch(function(err) {
            ElementPlus.ElMessage.error(err.response?.data?.message || '{{ __("panel/media.ai_failed") }}');
          }).finally(function() {
            self.aiImageDialog.loading = false;
          });
        },

        useAIImage() {
          if (this.aiImageDialog.previewUrl && this.aiImageDialog.resultPath) {
            this.loadFiles(this.currentFolder ? this.currentFolder.id : '/');
            this.aiImageDialog.visible = false;
            this.aiImageDialog.prompt = '';
            this.aiImageDialog.previewUrl = '';
            this.aiImageDialog.resultPath = '';
            this.aiImageDialog.referenceImage = '';
            this.aiImageDialog.referencePreviewUrl = '';
          }
        },

        isImageFile(file) {
          if (!file || file.is_dir) return false;
          var ext = (file.name || '').split('.').pop().toLowerCase();
          return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].indexOf(ext) !== -1;
        },

        imageToImage(file) {
          if (!file) return;
          this.aiImageDialog.referenceImage = file.path || '';
          this.aiImageDialog.referencePreviewUrl = file.origin_url || file.url || '';
          this.aiImageDialog.prompt = '';
          this.aiImageDialog.previewUrl = '';
          this.aiImageDialog.resultPath = '';
          this.aiImageDialog.visible = true;
          this.hideContextMenu();
          this.loadAIModelInfo();
        },

        loadAIModelInfo() {
          var self = this;
          http.get('ai/models_info').then(function(res) {
            var data = res.data || res;
            if (data && data.image_model) {
              self.aiImageDialog.modelInfo = data.image_model;
            }
          }).catch(function() {});
        },

        openAIDialog() {
          this.aiImageDialog.referenceImage = '';
          this.aiImageDialog.referencePreviewUrl = '';
          this.aiImageDialog.prompt = '';
          this.aiImageDialog.previewUrl = '';
          this.aiImageDialog.resultPath = '';
          this.aiImageDialog.visible = true;
          this.loadAIModelInfo();
        },

        // ===== Media Library: stats & detail panel =====
        loadMediaStats() {
          http.get('media/stats')
            .then(res => {
              if (res?.success && res?.data) {
                this.mediaStats = { ...res.data, loaded: true };
              }
            })
            .catch(err => {
              // silent: stats are nice-to-have
              console.warn('loadMediaStats failed', err);
            });
        },

        showMediaDetail(file) {
          const mediaId = file?.media_id;
          console.log('[file manager] showMediaDetail', { mediaId, path: file?.path, name: file?.name, mediaReference: file?.media_reference });
          if (!mediaId) {
            this.$message.warning("{{ __('panel/media.media_not_found') }}");
            return;
          }
          this.mediaDetail.visible   = true;
          this.mediaDetail.loading   = true;
          this.mediaDetail.data      = null;
          this.mediaDetail.altDraft  = '';
          http.get(`media/media/${mediaId}`)
            .then(res => {
              if (res?.success && res?.data) {
                this.mediaDetail.data    = res.data;
                this.mediaDetail.altDraft = res.data?.alt || '';
              } else {
                this.$message.error(res?.message || "{{ __('panel/media.media_not_found') }}");
                this.mediaDetail.visible = false;
              }
            })
            .catch(err => {
              this.$message.error(err?.message || 'Error');
              this.mediaDetail.visible = false;
            })
            .finally(() => {
              this.mediaDetail.loading = false;
            });
        },

        saveMediaAlt() {
          if (!this.mediaDetail.data?.id) return;
          this.mediaDetail.saving = true;
          http.patch(`media/media/${this.mediaDetail.data.id}`, { alt: this.mediaDetail.altDraft })
            .then(res => {
              if (res?.success) {
                this.$message.success("{{ __('panel/media.media_updated') }}");
                if (this.mediaDetail.data) {
                  this.mediaDetail.data.alt = this.mediaDetail.altDraft || null;
                }
              } else {
                this.$message.error(res?.message || 'Error');
              }
            })
            .catch(err => {
              this.$message.error(err?.message || 'Error');
            })
            .finally(() => {
              this.mediaDetail.saving = false;
            });
        },

        formatBytesForHumans(bytes) {
          if (!bytes || bytes <= 0) return '0 B';
          const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
          const exp = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
          return (bytes / Math.pow(1024, exp)).toFixed(1) + ' ' + units[exp];
        },
      },
      beforeUnmount() {
        document.removeEventListener('click', this.hideContextMenu);
        document.removeEventListener('click', this.hideFolderContextMenu);
      },
    });
    __fmApp.use(ElementPlus, window.ElementPlusLocaleZhCn ? { locale: ElementPlusLocaleZhCn } : {});
    // Register all icons globally (official approach from Element Plus docs)
    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
      __fmApp.component(key, component);
    }
    __fmApp.mount('#app');
  </script>
@endpush
