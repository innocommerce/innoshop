@section('content')
  <div id="app">
    <div class="file-manager">
      <div class="file-toolbar">
        <el-row type="flex" justify="space-between" align="middle">
          <el-col :span="12">
            <el-button-group>
              <el-button type="primary" size="small" @click="uploadFile">
                <el-icon><component :is="'Upload'"></component></el-icon> {{ __('panel/file_manager.upload') }}
              </el-button>
              <el-button size="small" @click="createFolder">
                <el-icon><component :is="'FolderAdd'"></component></el-icon> {{ __('panel/file_manager.create_folder') }}
              </el-button>
            </el-button-group>
            <el-button-group v-if="isIframeMode">
              <el-button size="small" :type="isMultiSelectMode ? 'primary' : 'default'" @click="toggleMultiSelectMode">
                <el-icon><component :is="'Check'"></component></el-icon> {{ __('panel/file_manager.multi_select_mode') }}
              </el-button>
              <el-button v-if="isMultiSelectMode" size="small" @click="selectAll">
                <el-icon><component :is="'Finished'"></component></el-icon> {{ __('panel/file_manager.select_all') }}
              </el-button>
              <el-button type="primary" size="small" @click="handleConfirm">{{ __('panel/file_manager.select_submit') }}</el-button>
            </el-button-group>
          </el-col>
          <el-col :span="12" class="file-toolbar-actions">
            <el-button-group>
              <el-button size="small" :disabled="selectedFiles.length !== 1" @click="renameSelectedFile">
                <el-icon><component :is="'Edit'"></component></el-icon> {{ __('panel/file_manager.rename') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="deleteFiles">
                <el-icon><component :is="'Delete'"></component></el-icon> {{ __('panel/file_manager.delete') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="moveFiles">
                <el-icon><component :is="'Folder'"></component></el-icon> {{ __('panel/file_manager.move_to') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="copyFiles">
                <el-icon><component :is="'CopyDocument'"></component></el-icon> {{ __('panel/file_manager.copy_to') }}
              </el-button>
            </el-button-group>
            <el-button-group>
              <el-button size="small" :type="sortField === 'created' ? 'primary' : 'default'" @click="toggleSortField('created')" title="{{ __('panel/file_manager.sort_by_time') }}">
                <el-icon><component :is="'Clock'"></component></el-icon>
              </el-button>
              <el-button size="small" :type="sortField === 'name' ? 'primary' : 'default'" @click="toggleSortField('name')" title="{{ __('panel/file_manager.sort_by_name') }}">
                <el-icon><component :is="'Operation'"></component></el-icon>
              </el-button>
              <el-button size="small" @click="toggleSortOrder" :title="sortOrder === 'asc' ? '{{ __("panel/file_manager.sort_asc") }}' : '{{ __("panel/file_manager.sort_desc") }}'">
                <el-icon v-if="sortOrder === 'asc'"><component :is="'SortUp'"></component></el-icon>
                <el-icon v-else><component :is="'SortDown'"></component></el-icon>
              </el-button>
            </el-button-group>
            <el-button size="small" @click="refreshAll" :title="'{{ __("panel/file_manager.refresh") }}'">
              <el-icon><component :is="'Refresh'"></component></el-icon>
            </el-button>
            <el-button size="small" @click="storageConfigDialog.visible = true">
              <el-icon><component :is="'Setting'"></component></el-icon>
            </el-button>
          </el-col>
        </el-row>
      </div>
      <el-row :gutter="0">
        <!-- 左侧文件夹树 -->
        <el-col :xs="8" :sm="6" :md="5" :lg="4"
          :xl="3">
          <div class="folder-tree">
            <el-tree ref="folderTree" :data="folders" :props="defaultProps" @node-click="handleNodeClick"
              :highlight-current="true" :default-expanded-keys="defaultExpandedKeys"
              :expand-on-click-node="false"
              :current-node-key="currentFolder ? currentFolder.id : '/'" node-key="id" draggable
              :allow-drop="handleAllowDrop" :allow-drag="handleAllowDrag" @node-drag-start="handleDragStart"
              @node-drag-enter="handleDragEnter" @node-drag-leave="handleDragLeave" @node-drag-end="handleNodeDragEnd"
              @node-drop="handleNodeDrop" class="folder-tree-container">
              <template #default="{ node, data }">
              <div class="el-tree-node__wrapper"
                @contextmenu.prevent="showFolderContextMenu($event, data, node)" @dragover.prevent
                @dragenter.prevent="handleTreeDragEnter($event, node, data)"
                @dragleave.prevent="handleTreeDragLeave($event, node)" @drop.prevent="handleTreeDrop($event, node, data)">
                <span class="custom-tree-node">
                  <el-icon v-if="node.expanded"><component :is="'FolderOpened'"></component></el-icon>
                  <el-icon v-else><component :is="'Folder'"></component></el-icon>
                  <span>@{{ node.label }}</span>
                </span>
              </div>
              </template>
            </el-tree>
          </div>
        </el-col>
        <!-- 右侧文件列表 -->
        <el-col :xs="16" :sm="18" :md="19" :lg="20"
          :xl="21">
          <div class="file-list">
            <div class="file-list-content">
              <div v-loading="loading" element-loading-text="{{ __('panel/file_manager.loading') }}">
                <template v-if="files.length > 0">
                <el-row :gutter="20">
                  <el-col :xs="12" :sm="8" :md="6" :lg="4"
                    :xl="3" v-for="file in files" :key="file.id || file.path">
                    <div :class="['file-card', selectedFiles.includes(file.id || file.path) ? 'selected' : '']"
                      @click="handleFileClick($event, file)" @dblclick="handleFileDoubleClick(file)"
                      @contextmenu.prevent="showContextMenu($event, file)" :data-is-dir="file.is_dir" draggable="true"
                      @dragstart="handleFileDragStart($event, file)" @dragend="handleFileDragEnd($event)"
                      @dragenter.prevent="handleFileDragEnter($event, file)" @dragover.prevent
                      @dragleave.prevent="handleFileDragLeave($event)" @drop.prevent="handleFileDrop($event, file)">
                      <div v-if="isMultiSelectMode" class="file-checkbox">
                        <el-checkbox :model-value="selectedFiles.includes(file.id || file.path)"
                          @click.stop="toggleFileSelect(file)">
                        </el-checkbox>
                      </div>
                      <div class="file-thumb">
                        <template v-if="file.is_dir">
                          <div class="folder-icon">
                            <img :src="file.thumb" alt="folder" class="folder-image"></img>
                          </div>
                        </template>
                        <template v-else>
                            <template v-if="file.mime && file.mime.startsWith('image/')">
                              <div class="preview-button"
                                @click.stop="showPreview(file)">
                                <el-icon><component :is="'ZoomIn'"></component></el-icon>
                                <span>{{ __('panel/file_manager.preview') }}</span>
                              </div>
                              <img :src="file.url" :alt="file.name">
                            </template>
                            <template v-else-if="file.mime && file.mime.startsWith('video/')">
                              <div class="preview-button"
                                @click.stop="playVideo(file)">
                                <el-icon><component :is="'VideoPlay'"></component></el-icon>
                                <span>{{ __('panel/file_manager.play') }}</span>
                              </div>
                              <div class="video-thumb">
                                <el-icon><component :is="'VideoCamera'"></component></el-icon>
                              </div>
                            </template>
                            <template v-else>
                              <div class="file-icon-default">
                                <el-icon><component :is="'Document'"></component></el-icon>
                              </div>
                            </template>
                        </template>
                      </div>
                      <div class="file-info">
                        <p class="file-name" :title="file.name">@{{ file.name }}</p>
                        <p class="file-type">@{{ file.is_dir ? {!! json_encode(__('panel/file_manager.folder')) !!} : file.mime }}</p>
                      </div>
                    </div>
                  </el-col>
                </el-row>
                <!-- 分页 -->
                <div class="pagination-container">
                  <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange"
                    v-model:current-page="pagination.page" :page-sizes="[18, 36, 54, 72]" v-model:page-size="pagination.per_page"
                    layout="total, sizes, prev, pager, next, jumper" :total="pagination.total">
                    <template #prev-text><span>{{ __('panel/file_manager.prev') }}</span></template>
                    <template #next-text><span>{{ __('panel/file_manager.next') }}</span></template>
                  </el-pagination>
                </div>
                </template>
                <!-- 添加空状态 -->
                <el-empty v-else :description="'{{ __('panel/file_manager.empty') }}'" :image-size="120"></el-empty>
              </div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 新建文件夹对话框 -->
    <el-dialog title="{{ __('panel/file_manager.new_folder_dialog') }}" v-model="folderDialog.visible" width="400px">
      <el-form :model="folderDialog.form" label-width="80px">
        <el-form-item label="{{ __('panel/file_manager.folder_name') }}">
          <el-input v-model="folderDialog.form.name" placeholder="{{ __('panel/file_manager.prompt_enter_folder_name') }}"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="folderDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitCreateFolder">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 上传文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.upload_dialog') }}" v-model="uploadDialog.visible" width="500px" @open="onUploadDialogOpen">
      <el-upload class="file-uploader" drag multiple :action="uploadUrl" :headers="uploadHeaders"
        :data="uploadData" :before-upload="beforeUpload" :on-success="handleUploadSuccess"
        :on-error="handleUploadError" :on-progress="handleUploadProgress">
        <el-icon class="el-icon--upload"><component :is="'Upload'"></component></el-icon>
        <div class="el-upload__text">{{ __('panel/file_manager.drag_here') }}<em>{{ __('panel/file_manager.click_upload') }}</em></div>
        <template #tip>
        <div class="el-upload__tip">{{ __('panel/file_manager.upload_tip') }}</div>
        <div class="el-upload__tip">{{ __('panel/file_manager.upload_max_size') }}: {{ $uploadMaxFileSize ?? '-' }}</div>
        <div class="el-upload__tip">{{ __('panel/file_manager.post_max_size') }}: {{ $postMaxSize ?? '-' }}</div>
        </template>
      </el-upload>
    </el-dialog>

    <!-- 修改重命名对话框 -->
    <el-dialog title="{{ __('panel/file_manager.rename') }}" v-model="renameDialog.visible" custom-class="rename-dialog" width="500px">
      <el-form :model="renameDialog.form" label-width="100px">
        <el-form-item :label="'{{ __('panel/file_manager.rename') }}'">
          <el-input v-model="renameDialog.form.newName" :placeholder="'{{ __('panel/file_manager.enter_new_name') }}'">
            <template #append>.@{{ renameDialog.form.extension }}</template>
          </el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="renameDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitRename">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 移动文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.move_to') }}" v-model="moveDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleMoveTargetSelect"
        :highlight-current="true" node-key="id">
        <template #default="{ node, data }">
        <span class="custom-tree-node">
          <el-icon><component :is="'Folder'"></component></el-icon>
          <span>@{{ node.label }}</span>
        </span>
        </template>
      </el-tree>
      <template #footer>
        <el-button @click="moveDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitMove">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 在文件卡片上添加右键菜单 -->
    <div class="file-card-context-menu" v-show="contextMenu.visible" :style="contextMenu.style">
      <ul>
        <li @click="renameFile"><el-icon><component :is="'Edit'"></component></el-icon> {{ __('panel/file_manager.rename') }}</li>
        <li @click="deleteFile"><el-icon><component :is="'Delete'"></component></el-icon> {{ __('panel/file_manager.delete') }}</li>
        <li @click="moveFile"><el-icon><component :is="'Folder'"></component></el-icon> {{ __('panel/file_manager.move_to') }}</li>
        <li @click="copyFile"><el-icon><component :is="'CopyDocument'"></component></el-icon> {{ __('panel/file_manager.copy_to') }}</li>
      </ul>
    </div>

    <!-- 复制文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.copy_to') }}" v-model="copyDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleCopyTargetSelect"
        :highlight-current="true" node-key="id">
        <template #default="{ node, data }">
        <span class="custom-tree-node">
          <el-icon><component :is="'Folder'"></component></el-icon>
          <span>@{{ node.label }}</span>
        </span>
        </template>
      </el-tree>
      <template #footer>
        <el-button @click="copyDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitCopy">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 文件夹右键菜单 -->
    <div v-if="folderContextMenu.visible" class="file-card-context-menu"
      :style="{
          top: folderContextMenu.style.top,
          left: folderContextMenu.style.left
      }">
      <ul>
        <li @click="renameFolder">
          <el-icon><component :is="'Edit'"></component></el-icon> {{ __('panel/file_manager.rename') }}
        </li>
        <li @click="moveFolder">
          <el-icon><component :is="'Right'"></component></el-icon> {{ __('panel/file_manager.move_to') }}
        </li>
        <li @click="deleteFolder">
          <el-icon><component :is="'Delete'"></component></el-icon> {{ __('panel/file_manager.delete') }}
        </li>
      </ul>
    </div>

    <!-- 文件夹重命名对话框 -->
    <el-dialog title="{{ __('panel/file_manager.rename_folder') }}" v-model="folderRenameDialog.visible" width="400px">
      <el-form :model="folderRenameDialog.form" label-width="80px">
        <el-form-item :label="'{{ __('panel/file_manager.folder_name') }}'">
          <el-input v-model="folderRenameDialog.form.newName" :placeholder="'{{ __('panel/file_manager.enter_new_name') }}'"></el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="folderRenameDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitFolderRename">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 文件夹移动对话框 -->
    <el-dialog title="{{ __('panel/file_manager.move_folder') }}" v-model="folderMoveDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleFolderMoveTargetSelect"
        :highlight-current="true" node-key="id">
        <template #default="{ node, data }">
        <span class="custom-tree-node">
          <el-icon><component :is="'Folder'"></component></el-icon>
          <span>@{{ node.label }}</span>
        </span>
        </template>
      </el-tree>
      <template #footer>
        <el-button @click="folderMoveDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitFolderMove">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </template>
    </el-dialog>

    <!-- 视频播放对话框 -->
    <el-dialog v-model="videoDialog.visible" width="800px" :show-close="true" @close="onVideoDialogClose"
      custom-class="video-dialog" append-to-body>
      <video v-if="videoDialog.visible" :src="videoDialog.url" controls autoplay
        class="video-player"></video>
    </el-dialog>

    <!-- 存储配置对话框 -->
    <el-dialog v-model="storageConfigDialog.visible" width="560px" custom-class="storage-config-dialog" :close-on-click-modal="false">
      <template #header>
      <div class="storage-dialog-header">
        <div class="storage-dialog-title">
          <el-icon><component :is="'Setting'"></component></el-icon>
          <span>{{ __('panel/file_manager.storage_config') }}</span>
        </div>
        <p class="storage-dialog-desc">{{ __('panel/file_manager.storage_config_desc') }}</p>
      </div>
      </template>
      <div class="storage-card-grid">
        <div
          v-for="option in storageOptions"
          :key="option.value"
          :class="['storage-card', { 'is-selected': storageConfigDialog.driver === option.value }]"
          @click="storageConfigDialog.driver = option.value"
        >
          <div class="storage-card-icon">
            <el-icon><component :is="option.icon"></component></el-icon>
          </div>
          <div class="storage-card-body">
            <div class="storage-card-name">@{{ option.label }}</div>
            <div class="storage-card-desc">@{{ option.desc }}</div>
          </div>
          <div v-if="storageConfigDialog.driver === option.value" class="storage-card-check">
            <el-icon><component :is="'Check'"></component></el-icon>
          </div>
          <div class="storage-card-current" v-if="option.value === storageConfigDialog.currentDriver && storageConfigDialog.driver !== option.value">
            {{ __('panel/file_manager.storage_current') }}
          </div>
        </div>
      </div>
      <template #footer>
      <span class="storage-dialog-footer">
        <a href="{{ route('panel.settings.index') }}?tab=tab-setting-storage" target="_blank" class="storage-settings-link">
          <el-icon><component :is="'Link'"></component></el-icon> {{ __('panel/file_manager.storage_settings_link') }}
        </a>
        <el-button @click="storageConfigDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="saveStorageConfig">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
      </template>
    </el-dialog>

    <!-- Image Preview Viewer -->
    <el-image-viewer
      v-if="previewImageUrl"
      :url-list="[previewImageUrl]"
      :hide-on-click-modal="true"
      @close="previewImageUrl = ''"
    ></el-image-viewer>
  </div>
@endsection
