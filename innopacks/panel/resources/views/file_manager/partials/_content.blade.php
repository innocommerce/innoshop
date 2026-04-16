@section('content')
  <div id="app">
    <div class="file-manager">
      <div class="file-toolbar">
        <el-row type="flex" justify="space-between" align="middle">
          <el-col :span="12">
            <el-button-group>
              <el-button type="primary" size="small" @click="uploadFile">
                <i class="el-icon-upload2"></i> {{ __('panel/file_manager.upload') }}
              </el-button>
              <el-button size="small" @click="createFolder">
                <i class="el-icon-folder-add"></i> {{ __('panel/file_manager.create_folder') }}
              </el-button>
            </el-button-group>
            <el-button-group v-if="isIframeMode">
              <el-button size="small" :type="isMultiSelectMode ? 'primary' : 'default'" @click="toggleMultiSelectMode">
                <i class="el-icon-check"></i> {{ __('panel/file_manager.multi_select_mode') }}
              </el-button>
              <el-button v-if="isMultiSelectMode" size="small" @click="selectAll">
                <i class="el-icon-finished"></i> {{ __('panel/file_manager.select_all') }}
              </el-button>
              <el-button type="primary" size="small" @click="handleConfirm">{{ __('panel/file_manager.select_submit') }}</el-button>
            </el-button-group>
          </el-col>
          <el-col :span="12" class="file-toolbar-actions">
            <el-button-group>
              <el-button size="small" :disabled="selectedFiles.length !== 1" @click="renameSelectedFile">
                <i class="el-icon-edit"></i> {{ __('panel/file_manager.rename') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="deleteFiles">
                <i class="el-icon-delete"></i> {{ __('panel/file_manager.delete') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="moveFiles">
                <i class="el-icon-folder"></i> {{ __('panel/file_manager.move_to') }}
              </el-button>
              <el-button size="small" :disabled="!selectedFiles.length" @click="copyFiles">
                <i class="el-icon-document-copy"></i> {{ __('panel/file_manager.copy_to') }}
              </el-button>
            </el-button-group>
            <el-button-group>
              <el-button size="small" :type="sortField === 'created' ? 'primary' : 'default'" @click="toggleSortField('created')" title="{{ __('panel/file_manager.sort_by_time') }}">
                <i class="el-icon-time"></i>
              </el-button>
              <el-button size="small" :type="sortField === 'name' ? 'primary' : 'default'" @click="toggleSortField('name')" title="{{ __('panel/file_manager.sort_by_name') }}">
                <i class="el-icon-s-order"></i>
              </el-button>
              <el-button size="small" @click="toggleSortOrder" :title="sortOrder === 'asc' ? '{{ __("panel/file_manager.sort_asc") }}' : '{{ __("panel/file_manager.sort_desc") }}'">
                <i :class="sortOrder === 'asc' ? 'el-icon-sort-up' : 'el-icon-sort-down'"></i>
              </el-button>
            </el-button-group>
            <el-button size="small" @click="refreshAll" :title="'{{ __("panel/file_manager.refresh") }}'">
              <i class="el-icon-refresh"></i>
            </el-button>
            <el-button size="small" @click="storageConfigDialog.visible = true">
              <i class="el-icon-setting"></i>
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
              :current-node-key="currentFolder ? currentFolder.id : '/'" node-key="id" draggable
              :allow-drop="handleAllowDrop" :allow-drag="handleAllowDrag" @node-drag-start="handleDragStart"
              @node-drag-enter="handleDragEnter" @node-drag-leave="handleDragLeave" @node-drag-end="handleNodeDragEnd"
              @node-drop="handleNodeDrop" class="folder-tree-container">
              <div class="el-tree-node__wrapper" slot-scope="{ node, data }"
                @contextmenu.prevent="showFolderContextMenu($event, data, node)" @dragover.prevent
                @dragenter.prevent="handleTreeDragEnter($event, node, data)"
                @dragleave.prevent="handleTreeDragLeave($event, node)" @drop.prevent="handleTreeDrop($event, node, data)">
                <span class="custom-tree-node">
                  <i :class="[
                      data.isRoot ? 'el-icon-folder' : 'el-icon-folder',
                      node.expanded ? 'el-icon-folder-opened' : 'el-icon-folder'
                  ]"
                    ></i>
                  <span>@{{ node.label }}</span>
                </span>
              </div>
            </el-tree>
          </div>
        </el-col>
        <!-- 右侧文件列表 -->
        <el-col :xs="16" :sm="18" :md="19" :lg="20"
          :xl="21">
          <div class="file-list">
            <div class="file-list-content">
              <div v-loading="loading" element-loading-text="{{ __('panel/file_manager.loading') }}">
                <el-row :gutter="20">
                  <el-col :xs="12" :sm="8" :md="6" :lg="4"
                    :xl="4" :xl="3" v-for="file in files" :key="file.id || file.path">
                    <div :class="['file-card', selectedFiles.includes(file.id || file.path) ? 'selected' : '']"
                      @click="handleFileClick($event, file)" @dblclick="handleFileDoubleClick(file)"
                      @contextmenu.prevent="showContextMenu($event, file)" :data-is-dir="file.is_dir" draggable="true"
                      @dragstart="handleFileDragStart($event, file)" @dragend="handleFileDragEnd($event)"
                      @dragenter.prevent="handleFileDragEnter($event, file)" @dragover.prevent
                      @dragleave.prevent="handleFileDragLeave($event)" @drop.prevent="handleFileDrop($event, file)">
                      <div v-if="isMultiSelectMode" class="file-checkbox">
                        <el-checkbox :value="selectedFiles.includes(file.id || file.path)"
                          @click.native.stop="toggleFileSelect(file)">
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
                                @click.stop="$refs['image-' + file.id][0].clickHandler()">
                                <i class="el-icon-zoom-in"></i>
                                <span>{{ __('panel/file_manager.preview') }}</span>
                              </div>
                              <el-image :ref="'image-' + file.id" :src="file.url" :alt="file.name"
                                fit="contain" :preview-src-list="[file.origin_url || file.url]">
                              </el-image>
                            </template>
                            <template v-else-if="file.mime && file.mime.startsWith('video/')">
                              <div class="preview-button"
                                @click.stop="playVideo(file)">
                                <i class="el-icon-video-play"></i>
                                <span>{{ __('panel/file_manager.play') }}</span>
                              </div>
                              <div class="video-thumb">
                                <i class="el-icon-video-camera"></i>
                              </div>
                            </template>
                            <template v-else>
                              <div class="file-icon-default">
                                <i class="el-icon-document"></i>
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
                    :current-page="pagination.page" :page-sizes="[18, 36, 54, 72]" :page-size="pagination.per_page"
                    prev-text="{{ __('panel/file_manager.prev') }}" next-text="{{ __('panel/file_manager.next') }}"
                    layout="total, sizes, prev, pager, next, jumper" :total="pagination.total">
                  </el-pagination>
                </div>
                <!-- 添加空状态 -->
                <el-empty v-else :description="'{{ __('panel/file_manager.empty') }}'" :image-size="120"></el-empty>
              </div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>

    <!-- 新建文件夹对话框 -->
    <el-dialog title="{{ __('panel/file_manager.new_folder_dialog') }}" :visible.sync="folderDialog.visible" width="400px">
      <el-form :model="folderDialog.form" label-width="80px">
        <el-form-item label="{{ __('panel/file_manager.folder_name') }}">
          <el-input v-model="folderDialog.form.name" placeholder="{{ __('panel/file_manager.prompt_enter_folder_name') }}"></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button @click="folderDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitCreateFolder">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 上传文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.upload_dialog') }}" :visible.sync="uploadDialog.visible" width="500px" @open="onUploadDialogOpen">
      <el-upload class="file-uploader" drag multiple :action="uploadUrl" :headers="uploadHeaders"
        :data="uploadData" :before-upload="beforeUpload" :on-success="handleUploadSuccess"
        :on-error="handleUploadError" :on-progress="handleUploadProgress">
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">{{ __('panel/file_manager.drag_here') }}<em>{{ __('panel/file_manager.click_upload') }}</em></div>
        <div class="el-upload__tip" slot="tip">{{ __('panel/file_manager.upload_tip') }}</div>
        <div class="el-upload__tip" slot="tip">{{ __('panel/file_manager.upload_max_size') }}: {{ $uploadMaxFileSize ?? '-' }}</div>
        <div class="el-upload__tip" slot="tip">{{ __('panel/file_manager.post_max_size') }}: {{ $postMaxSize ?? '-' }}</div>
      </el-upload>
    </el-dialog>

    <!-- 修改重命名对话框 -->
    <el-dialog title="{{ __('panel/file_manager.rename') }}" :visible.sync="renameDialog.visible" custom-class="rename-dialog" width="500px">
      <el-form :model="renameDialog.form" label-width="100px">
        <el-form-item :label="'{{ __('panel/file_manager.rename') }}'">
          <el-input v-model="renameDialog.form.newName" :placeholder="'{{ __('panel/file_manager.enter_new_name') }}'">
            <template slot="append">.@{{ renameDialog.form.extension }}</template>
          </el-input>
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button @click="renameDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitRename">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 移动文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.move_to') }}" :visible.sync="moveDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleMoveTargetSelect"
        :highlight-current="true" node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder"></i>
          <span>@{{ node.label }}</span>
        </span>
      </el-tree>
      <span slot="footer">
        <el-button @click="moveDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitMove">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 在文件卡片上添加右键菜单 -->
    <div class="file-card-context-menu" v-show="contextMenu.visible" :style="contextMenu.style">
      <ul>
        <li @click="renameFile"><i class="el-icon-edit"></i> {{ __('panel/file_manager.rename') }}</li>
        <li @click="deleteFile"><i class="el-icon-delete"></i> {{ __('panel/file_manager.delete') }}</li>
        <li @click="moveFile"><i class="el-icon-folder"></i> {{ __('panel/file_manager.move_to') }}</li>
        <li @click="copyFile"><i class="el-icon-document-copy"></i> {{ __('panel/file_manager.copy_to') }}</li>
      </ul>
    </div>

    <!-- 复制文件对话框 -->
    <el-dialog title="{{ __('panel/file_manager.copy_to') }}" :visible.sync="copyDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleCopyTargetSelect"
        :highlight-current="true" node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder"></i>
          <span>@{{ node.label }}</span>
        </span>
      </el-tree>
      <span slot="footer">
        <el-button @click="copyDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitCopy">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 文件夹右键菜单 -->
    <div v-if="folderContextMenu.visible" class="file-card-context-menu"
      :style="{
          top: folderContextMenu.style.top,
          left: folderContextMenu.style.left
      }">
      <ul>
        <li @click="renameFolder">
          <i class="el-icon-edit"></i> {{ __('panel/file_manager.rename') }}
        </li>
        <li @click="moveFolder">
          <i class="el-icon-position"></i> {{ __('panel/file_manager.move_to') }}
        </li>
        <li @click="deleteFolder">
          <i class="el-icon-delete"></i> {{ __('panel/file_manager.delete') }}
        </li>
      </ul>
    </div>

    <!-- 文件夹重命名对话框 -->
    <el-dialog title="{{ __('panel/file_manager.rename_folder') }}" :visible.sync="folderRenameDialog.visible" width="400px">
      <el-form :model="folderRenameDialog.form" label-width="80px">
        <el-form-item :label="'{{ __('panel/file_manager.folder_name') }}'">
          <el-input v-model="folderRenameDialog.form.newName" :placeholder="'{{ __('panel/file_manager.enter_new_name') }}'"></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button @click="folderRenameDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitFolderRename">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 文件夹移动对话框 -->
    <el-dialog title="{{ __('panel/file_manager.move_folder') }}" :visible.sync="folderMoveDialog.visible" width="400px">
      <el-tree :data="folders" :props="defaultProps" @node-click="handleFolderMoveTargetSelect"
        :highlight-current="true" node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder"></i>
          <span>@{{ node.label }}</span>
        </span>
      </el-tree>
      <span slot="footer">
        <el-button @click="folderMoveDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="submitFolderMove">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>

    <!-- 视频播放对话框 -->
    <el-dialog :visible.sync="videoDialog.visible" width="800px" :show-close="true" @close="onVideoDialogClose"
      custom-class="video-dialog" append-to-body>
      <video v-if="videoDialog.visible" :src="videoDialog.url" controls autoplay
        class="video-player"></video>
    </el-dialog>

    <!-- 存储配置对话框 -->
    <el-dialog :visible.sync="storageConfigDialog.visible" width="560px" custom-class="storage-config-dialog" :close-on-click-modal="false">
      <div slot="title" class="storage-dialog-header">
        <div class="storage-dialog-title">
          <i class="el-icon-setting"></i>
          <span>{{ __('panel/file_manager.storage_config') }}</span>
        </div>
        <p class="storage-dialog-desc">{{ __('panel/file_manager.storage_config_desc') }}</p>
      </div>
      <div class="storage-card-grid">
        <div
          v-for="option in storageOptions"
          :key="option.value"
          :class="['storage-card', { 'is-selected': storageConfigDialog.driver === option.value }]"
          @click="storageConfigDialog.driver = option.value"
        >
          <div class="storage-card-icon">
            <i :class="option.icon"></i>
          </div>
          <div class="storage-card-body">
            <div class="storage-card-name">@{{ option.label }}</div>
            <div class="storage-card-desc">@{{ option.desc }}</div>
          </div>
          <div v-if="storageConfigDialog.driver === option.value" class="storage-card-check">
            <i class="el-icon-check"></i>
          </div>
          <div class="storage-card-current" v-if="option.value === storageConfigDialog.currentDriver && storageConfigDialog.driver !== option.value">
            {{ __('panel/file_manager.storage_current') }}
          </div>
        </div>
      </div>
      <span slot="footer" class="storage-dialog-footer">
        <a href="{{ route(panel_name().'.settings.index') }}?tab=tab-setting-storage" target="_blank" class="storage-settings-link">
          <i class="el-icon-link"></i> {{ __('panel/file_manager.storage_settings_link') }}
        </a>
        <el-button @click="storageConfigDialog.visible = false">{{ __('panel/file_manager.cancel_btn') }}</el-button>
        <el-button type="primary" @click="saveStorageConfig">{{ __('panel/file_manager.ok_btn') }}</el-button>
      </span>
    </el-dialog>
  </div>
@endsection
