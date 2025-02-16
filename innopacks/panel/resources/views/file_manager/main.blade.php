@push('header')
    <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.24.3/vuedraggable.umd.min.js"></script>

    <script>
        // 从 URL 参数获取配置
        const urlParams = new URLSearchParams(window.location.search);
        window.fileManagerConfig = {
            multiple: urlParams.get('multiple') === '1',
            type: urlParams.get('type') || 'all',
            callback: window.parent.fileManagerCallback
        };
    </script>

    <script>
        // http 请求封装
        (function(window) {
            'use strict';

            window.getApiToken = () => {
                const currentToken = document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                const parentToken = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
                return currentToken || parentToken;
            };

            // 创建 axios 实例
            const http = axios.create({
                baseURL: '/api/panel/',
                timeout: 30000,
                headers: {
                    'Authorization': 'Bearer ' + window.getApiToken()
                }
            });

            // 添加请求拦截器，确保每次请求都使用最新的 token
            http.interceptors.request.use(config => {
                // 每次请求前重新获取 token
                config.headers.Authorization = 'Bearer ' + window.getApiToken();

                // 添加 loading
                if (window.layer) {
                    layer.load(2, { shade: [0.3, '#fff'] });
                }
                return config;
            });

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

                    // 错误处理
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
            window.http = http;  // 确保 http 也被添加到 window 对象上
        })(window);
    </script>

    <style>
        .file-manager {
            background: #fff;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* 左侧文件夹树样式 */
        .folder-tree {
            border-right: 1px solid #EBEEF5;
            overflow-y: auto;
            padding: 20px;
            background: #fff;
            height: 100%;
        }

        /* 右侧文件列表样式 */
        .file-list {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* 顶部工具栏 */
        .file-toolbar {
            padding: 15px 20px;
            border-bottom: 1px solid #EBEEF5;
            background: #fff;
            border-radius: 4px 4px 0 0;
            flex-shrink: 0;
        }

        /* 文件卡片样式 */
        .file-card {
            border: 1px solid #EBEEF5;
            border-radius: 4px;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            margin-bottom: 15px;
            background: #fff;
            overflow: hidden;
        }

        /* 文件缩略图容器 */
        .file-thumb {
            padding: 16px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            border-bottom: 1px solid #EBEEF5;
            user-select: none;
        }

        /* 禁用图片拖拽 */
        .file-thumb img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            pointer-events: none;
            user-select: none;
            -webkit-user-drag: none;
        }

        /* 文件信息区域 */
        .file-info {
            padding: 12px 16px;
        }

        /* 文件名样式 */
        .file-name {
            font-weight: 500;
            color: #303133;
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.4;
            word-break: break-all;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 文件类型样式 */
        .file-type {
            font-size: 12px;
            color: #909399;
            line-height: 1.4;
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
            padding: 20px;
            text-align: right;
            background: #fff;
            border-top: 1px solid #EBEEF5;
            flex-shrink: 0;
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

        /* 文件夹树图标样式 */
        .el-tree-node__content .el-icon-folder,
        .el-tree-node__content .el-icon-folder-opened {
            font-size: 16px;
        }

        /* 选中状态的文件夹样式 */
        .el-tree-node.is-current > .el-tree-node__content {
            background-color: #f0e6fc !important;
            color: #8446df;
        }

        /* 鼠标悬停样式 */
        .el-tree-node__content:hover {
            background-color: #f5f7fa;
        }

        /* 展开的文件夹图标颜色 */
        .el-tree-node.is-expanded > .el-tree-node__content .el-icon-folder {
            color: #8446df;
        }

        /* 树节点内容间距 */
        .custom-tree-node {
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        /* 添加右键菜单样式 */
        .file-card-context-menu {
            position: fixed;
            background: white;
            border: 1px solid #EBEEF5;
            border-radius: 4px;
            box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
            z-index: 3000;
        }

        .file-card-context-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-card-context-menu li {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .file-card-context-menu li:hover {
            background-color: #f5f7fa;
            color: #8446df;
        }

        .file-card-context-menu li i {
            margin-right: 8px;
        }

        .file-card {
            position: relative;
        }

        .file-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }

        .file-checkbox .el-checkbox {
            margin-right: 0;
        }

        /* 选中状态的样式 */
        .file-card.selected {
            border-color: #8446df;
            background: rgba(132, 70, 223, 0.05);
        }

        /* 多选模式下的悬停效果 */
        .file-card:hover .file-checkbox {
            opacity: 1;
        }

        .file-list-toolbar {
            padding: 10px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #EBEEF5;
        }

        .file-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 3px;
            padding: 2px;
        }

        .file-checkbox .el-checkbox {
            margin-right: 0;
        }

        .file-card.selected .file-checkbox {
            opacity: 1;
        }

        /* 文件列表工具栏样式 */
        .file-list-toolbar {
            padding: 10px 20px;
            margin-bottom: 15px;
            border-bottom: 1px solid #EBEEF5;
            background: #fafafa;
            border-radius: 4px;
        }

        /* 文件列表容器样式 */
        .file-list {
            padding: 20px;
            height: calc(100vh - 300px);
            overflow-y: auto;
        }

        /* 多选框样式 */
        .file-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 3px;
            padding: 2px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .file-card:hover .file-checkbox,
        .file-card.selected .file-checkbox {
            opacity: 1;
        }

        /* 分页容器样式 */
        .pagination-container {
            padding: 20px 0;
            text-align: right;
            background: #fff;
            border-top: 1px solid #EBEEF5;
            flex-shrink: 0;
        }

        /* 空状态样式 */
        .el-empty {
            padding: 40px 0;
            background: #fff;
            border-radius: 4px;
        }

        /* 调整网格布局间距 */
        .el-row {
            margin-right: -7.5px !important;
            margin-left: -7.5px !important;
        }

        .el-col {
            padding-right: 7.5px !important;
            padding-left: 7.5px !important;
        }

        /* 文件管理器整体容器 */
        .file-manager {
            background: #fff;
            border-radius: 4px;
            min-height: calc(100vh - 300px);
            display: flex;
            flex-direction: column;
        }

        /* 主工具栏样式 */
        .file-toolbar {
            padding: 15px 20px;
            border-bottom: 1px solid #EBEEF5;
            background: #fff;
            border-radius: 4px 4px 0 0;
        }

        /* 按钮组样式优化 */
        .el-button-group {
            margin-right: 10px;
        }

        .el-button-group:last-child {
            margin-right: 0;
        }

        /* 确保复选框正确显示 */
        .el-checkbox__inner {
            border-color: #DCDFE6;
        }

        .el-checkbox__input.is-checked .el-checkbox__inner {
            background-color: #8446df;
            border-color: #8446df;
        }

        /* 左侧文件夹树样式 */
        .folder-tree {
            border-right: 1px solid #EBEEF5;
            height: calc(100vh - 300px);
            overflow-y: auto;
            padding: 20px;
            background: #fff;
        }

        /* 树节点样式 */
        .el-tree {
            background: none;
        }

        .el-tree-node {
            white-space: nowrap;
        }

        .el-tree-node__content {
            height: 40px;
            display: flex;
            align-items: center;
            padding-right: 8px;
        }

        .custom-tree-node {
            flex: 1;
            display: flex;
            align-items: center;
            font-size: 14px;
            padding: 0 8px;
        }

        .el-tree-node__expand-icon {
            color: #909399;
        }

        .el-tree-node__content .el-icon-folder,
        .el-tree-node__content .el-icon-folder-opened {
            font-size: 16px;
            margin-right: 8px;
        }

        /* 选中状态 */
        .el-tree-node.is-current > .el-tree-node__content {
            background-color: #f0e6fc !important;
            color: #8446df;
        }

        /* 鼠标悬停状态 */
        .el-tree-node__content:hover {
            background-color: #f5f7fa;
        }

        /* 展开的文件夹图标颜色 */
        .el-tree-node.is-expanded > .el-tree-node__content .el-icon-folder {
            color: #8446df;
        }

        /* 右键菜单样式 */
        .file-card-context-menu {
            position: fixed;
            background: white;
            border: 1px solid #EBEEF5;
            border-radius: 4px;
            box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
            z-index: 3000;
            min-width: 120px;
        }

        .file-card-context-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-card-context-menu li {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .file-card-context-menu li:hover {
            background-color: #f5f7fa;
            color: #8446df;
        }

        .file-card-context-menu li i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* 重命名对话框样式 */
        .rename-dialog .el-form-item {
            margin-bottom: 0;
        }

        .rename-dialog .el-input {
            width: 100%;
        }

        .rename-dialog .el-input-group__append {
            min-width: 60px;
            text-align: center;
            background-color: #f5f7fa;
            color: #909399;
        }

        /* 调整对话框宽度 */
        .rename-dialog.el-dialog {
            width: 500px !important;
        }

        /* 调整表单布局 */
        .rename-dialog .el-form-item__label {
            padding-right: 20px;
        }

        .rename-dialog .el-form-item__content {
            margin-right: 20px;
        }

        /* 文件夹右键菜单样式 */
        .folder-context-menu {
            position: fixed;
            background: white;
            border: 1px solid #EBEEF5;
            border-radius: 4px;
            box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
            z-index: 3000;
            min-width: 120px;
        }

        .folder-context-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .folder-context-menu li {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .folder-context-menu li:hover {
            background-color: #f5f7fa;
            color: #8446df;
        }

        .folder-context-menu li i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* 文件夹图标样式 */
        .folder-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
        }

        .folder-icon i {
            transition: all 0.3s;
        }

        /* 文件卡片悬停时的文件夹图标效果 */
        .file-card:hover .folder-icon i {
            transform: scale(1.1);
            color: #9969e5;
        }

        /* 双击提示 */
        .file-card[data-is-dir="true"] {
            cursor: pointer;
        }

        .file-card[data-is-dir="true"]:hover::after {
            content: "双击进入";
            position: absolute;
            bottom: 5px;
            right: 5px;
            font-size: 12px;
            color: #8446df;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* 文件夹图标样式 */
        .folder-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
        }

        .folder-image {
            width: 64px;
            height: 64px;
            object-fit: contain;
            transition: all 0.3s;
        }

        /* 文件卡片悬停时的文件夹图标效果 */
        .file-card:hover .folder-image {
            transform: scale(1.1);
        }

        /* 文件夹卡片特殊样式 */
        .file-card[data-is-dir="true"] .file-thumb {
            background: #f8f9fc;
        }

        /* 拖拽时的样式 */
        .ghost {
            opacity: 0.5;
            background: #c8ebfb;
        }

        .file-card.dragging {
            cursor: move;
        }

        /* 可放置目标的样式 */
        .el-tree-node__content.is-drop-inner {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
        }

        /* 拖拽目标高亮样式 */
        .el-tree-node__content.is-drop-inner {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
        }

        /* 被拖拽节点样式 */
        .el-tree-node.is-dragging .el-tree-node__content {
            opacity: 0.5;
            background-color: #f0e6fc;
        }

        /* 无效放置目标样式 */
        .el-tree-node.is-drop-not-allow .el-tree-node__content {
            background-color: #fef0f0 !important;
        }

        /* 拖拽目标高亮样式 */
        .el-tree-node__content.is-drop-inner {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
            border-radius: 4px;
        }

        /* 被拖拽节点样式 */
        .el-tree-node.is-dragging .el-tree-node__content {
            opacity: 0.7;
            background-color: #f0e6fc !important;
            border: 1px solid #8446df;
            border-radius: 4px;
        }

        /* 有效放置目标的样式 */
        .el-tree-node.is-drop-inner > .el-tree-node__content {
            background-color: #f0e6fc !important;
            box-shadow: 0 0 5px rgba(132, 70, 223, 0.3);
        }

        /* 无效放置目标样式 */
        .el-tree-node.is-drop-not-allow .el-tree-node__content {
            background-color: #fef0f0 !important;
            border: 2px dashed #f56c6c;
        }

        /* 拖拽过程中的文件夹图标样式 */
        .el-tree-node.is-dragging .el-icon-folder,
        .el-tree-node.is-dragging .el-icon-folder-opened {
            color: #8446df;
        }

        /* 可放置目标的动画效果 */
        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .el-tree-node__content.is-drop-inner {
            animation: dropTarget 1s ease infinite;
        }

        /* 拖拽提示文本 */
        .el-tree-node.is-drop-inner::after {
            content: "放置到此处";
            position: absolute;
            right: 10px;
            color: #8446df;
            font-size: 12px;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* 拖拽相关样式 */
        .file-card.dragging {
            opacity: 0.5;
            border: 2px dashed #8446df;
        }

        .el-tree-node.is-drop-inner {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
        }

        /* 拖拽提示 */
        .el-tree-node.is-drop-inner::after {
            content: "放置到此处";
            position: absolute;
            right: 10px;
            color: #8446df;
            font-size: 12px;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* 拖拽相关样式 */
        .file-card.dragging {
            opacity: 0.5;
            border: 2px dashed #8446df;
            transform: scale(0.95);
        }

        .el-tree-node.is-drop-inner {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
        }

        /* 拖拽时的文件夹高亮效果 */
        .el-tree-node.can-drop > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
            animation: dropTarget 1s ease infinite;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 拖拽相关样式 */
        .file-card.dragging {
            opacity: 0.6;
            transform: scale(1.05);
            cursor: move;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }

        /* 可放置目标的高亮样式 */
        .el-tree-node.drag-over > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
            animation: dropTarget 1s ease infinite;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 拖拽提示 */
        .el-tree-node.drag-over > .el-tree-node__content::after {
            content: "放置到此处";
            position: absolute;
            right: 10px;
            color: #8446df;
            font-size: 12px;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* 拖拽相关样式 */
        .file-card.dragging {
            opacity: 0.6;
            transform: scale(1.05);
            cursor: move;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* 文件夹树拖拽目标样式 */
        .el-tree-node.drag-over > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
            animation: dropTarget 1s ease infinite;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 拖拽相关样式 */
        .file-card.dragging {
            opacity: 0.6;
            transform: scale(0.95);
            cursor: move;
        }

        .file-card[data-is-dir="true"].drag-over {
            background-color: #f0e6fc;
            border: 2px dashed #8446df;
            animation: dropTarget 1s ease infinite;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 文件夹接收拖拽时的提示 */
        .file-card[data-is-dir="true"].drag-over::after {
            content: "放置到此处";
            position: absolute;
            bottom: 5px;
            right: 5px;
            font-size: 12px;
            color: #8446df;
            background: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* 文件夹树节点样式 */
        .el-tree-node__wrapper {
            width: 100%;
            padding: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .custom-tree-node {
            flex: 1;
            display: flex;
            align-items: center;
        }

        /* 树节点悬停效果 */
        .el-tree-node__wrapper:hover {
            background-color: #f5f7fa;
        }

        /* 选中状态的树节点 */
        .el-tree-node.is-current > .el-tree-node__content > .el-tree-node__wrapper {
            background-color: #f0e6fc !important;
            color: #8446df;
        }

        /* 右键菜单样式优化 */
        .file-card-context-menu {
            position: fixed;
            background: white;
            border: 1px solid #EBEEF5;
            border-radius: 4px;
            box-shadow: 0 2px 12px 0 rgba(0,0,0,0.1);
            z-index: 3000;
            min-width: 160px;
            padding: 5px 0;
        }

        .file-card-context-menu li {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .file-card-context-menu li:hover {
            background-color: #f0e6fc;
            color: #8446df;
        }

        /* 拖拽相关样式 */
        .el-tree-node.is-dragging {
            opacity: 0.5;
            cursor: move;
        }

        /* 拖拽目标的高亮样式 */
        .el-tree-node.is-drop-inner > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df !important;
            animation: dropTarget 1s ease infinite;
        }

        /* 确保动画效果持续显示 */
        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 防止其他hover效果覆盖拖拽样式 */
        .el-tree-node.is-drop-inner > .el-tree-node__content:hover {
            background-color: #f0e6fc !important;
        }

        /* 确保拖拽时的样式优先级 */
        .el-tree-node.is-drop-inner {
            z-index: 100;
        }

        /* 文件夹树容器样式 */
        .folder-tree-container {
            height: 100%;
            position: relative;
        }

        /* 根目录拖拽高亮样式 */
        .folder-tree-container.drag-over {
            background-color: #f0e6fc;
            border: 2px dashed #8446df;
            border-radius: 4px;
            animation: dropTarget 1s ease infinite;
        }

        /* 文件夹节点拖拽高亮样式 */
        .el-tree-node.drag-over > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df;
            animation: dropTarget 1s ease infinite;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* 添加或修改相关样式 */
        /* ... 其他样式 ... */

        /* 文件夹树容器样式 */
        .folder-tree-container {
            height: 100%;
            position: relative;
        }

        /* 根目录拖拽高亮样式 */
        .folder-tree-container.is-drop-target {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df !important;
            border-radius: 4px;
            animation: dropTarget 1s ease infinite;
        }

        /* 文件夹节点拖拽高亮样式 */
        .el-tree-node.is-drop-target > .el-tree-node__content {
            background-color: #f0e6fc !important;
            border: 2px dashed #8446df !important;
            animation: dropTarget 1s ease infinite;
        }

        /* 确保高亮样式优先级 */
        .el-tree-node.is-drop-target > .el-tree-node__content:hover {
            background-color: #f0e6fc !important;
        }

        @keyframes dropTarget {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
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
                            <el-button size="small" :disabled="selectedFiles.length !== 1" @click="renameSelectedFile">
                                <i class="el-icon-edit"></i> 重命
                            </el-button>
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
                            @node-drop="handleNodeDrop"
                            class="folder-tree-container">
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
                </el-col>

                <!-- 右侧文件列表 -->
                <el-col :span="18">
                    <div class="file-list">
                        <div class="file-list-toolbar">
                            <el-row type="flex" justify="space-between" align="middle">
                                <el-col :span="12">
                                    <el-button-group>
                                        <el-button
                                            size="small"
                                            :type="isMultiSelectMode ? 'primary' : 'default'"
                                            @click="toggleMultiSelectMode">
                                            <i class="el-icon-check"></i> 多选模式
                                        </el-button>
                                        <el-button
                                            v-if="isMultiSelectMode"
                                            size="small"
                                            @click="selectAll">
                                            <i class="el-icon-finished"></i> 全选
                                        </el-button>
                                    </el-button-group>
                                </el-col>
                                <el-col :span="12" style="text-align: right">
                                    <!-- 预留给排序等功能 -->
                                </el-col>
                            </el-row>
                        </div>

                        <div v-loading="loading" element-loading-text="加载中...">
                            <el-row :gutter="20">
                                <el-col :span="6" v-for="file in files" :key="file.id || file.path">
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
                                        <div v-if="isMultiSelectMode" class="file-checkbox">
                                            <el-checkbox
                                                :value="selectedFiles.includes(file.id || file.path)"
                                                @click.native.stop="toggleFileSelect(file)">
                                            </el-checkbox>
                                        </div>
                                        <div class="file-thumb">
                                            <template v-if="file.is_dir">
                                                <div class="folder-icon">
                                                    <img :src="file.thumb" alt="folder" class="folder-image">
                                                </div>
                                            </template>
                                            <template v-else>
                                                <img :src="file.preview_url" :alt="file.name">
                                            </template>
                                        </div>
                                        <div class="file-info">
                                            <p class="file-name" :title="file.name">@{{ file.name }}</p>
                                            <p class="file-type">@{{ file.is_dir ? '文件夹' : file.mime }}</p>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>

                            <!-- 分页 -->
                            <div class="pagination-container">
                                <el-pagination
                                    @size-change="handleSizeChange"
                                    @current-change="handleCurrentChange"
                                    :current-page="pagination.page"
                                    :page-sizes="[20, 40, 60, 80]"
                                    :page-size="pagination.per_page"
                                    layout="total, sizes, prev, pager, next, jumper"
                                    :total="pagination.total">
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

        <!-- 修改重命名对话框 -->
        <el-dialog
            title="重命名"
            :visible.sync="renameDialog.visible"
            custom-class="rename-dialog"
            width="500px">
            <el-form :model="renameDialog.form" label-width="100px">
                <el-form-item label="文件名称">
                    <el-input v-model="renameDialog.form.newName" placeholder="请输入新名称">
                        <template slot="append">.@{{ renameDialog.form.extension }}</template>
                    </el-input>
                </el-form-item>
            </el-form>
            <span slot="footer">
        <el-button @click="renameDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitRename">确 定</el-button>
      </span>
        </el-dialog>

        <!-- 移动文件对话框 -->
        <el-dialog
            title="移动到"
            :visible.sync="moveDialog.visible"
            width="400px">
            <el-tree
                :data="folders"
                :props="defaultProps"
                @node-click="handleMoveTargetSelect"
                :highlight-current="true"
                node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder" style="margin-right: 4px; color: #8446df;"></i>
          <span>@{{ node.label }}</span>
        </span>
            </el-tree>
            <span slot="footer">
        <el-button @click="moveDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitMove">确 定</el-button>
      </span>
        </el-dialog>

        <!-- 在文件卡片上添加右键菜单 -->
        <div class="file-card-context-menu" v-show="contextMenu.visible" :style="contextMenu.style">
            <ul>
                <li @click="renameFile"><i class="el-icon-edit"></i> 重命名</li>
                <li @click="deleteFile"><i class="el-icon-delete"></i> 删除</li>
                <li @click="moveFile"><i class="el-icon-folder"></i> 移动到</li>
                <li @click="copyFile"><i class="el-icon-document-copy"></i> 复制到</li>
            </ul>
        </div>

        <!-- 复制文件对话框 -->
        <el-dialog
            title="复制到"
            :visible.sync="copyDialog.visible"
            width="400px">
            <el-tree
                :data="folders"
                :props="defaultProps"
                @node-click="handleCopyTargetSelect"
                :highlight-current="true"
                node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder" style="margin-right: 4px; color: #8446df;"></i>
          <span>@{{ node.label }}</span>
        </span>
            </el-tree>
            <span slot="footer">
        <el-button @click="copyDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitCopy">确 定</el-button>
      </span>
        </el-dialog>

        <!-- 文件夹右键菜单 -->
        <div v-if="folderContextMenu.visible"
             class="file-card-context-menu"
             :style="{
           top: folderContextMenu.style.top,
           left: folderContextMenu.style.left
         }">
            <ul>
                <li @click="renameFolder">
                    <i class="el-icon-edit"></i> 重命名
                </li>
                <li @click="moveFolder">
                    <i class="el-icon-position"></i> 移动到
                </li>
                <li @click="deleteFolder">
                    <i class="el-icon-delete"></i> 删除
                </li>
            </ul>
        </div>

        <!-- 文件夹重命名对话框 -->
        <el-dialog
            title="重命名文件夹"
            :visible.sync="folderRenameDialog.visible"
            width="400px">
            <el-form :model="folderRenameDialog.form" label-width="80px">
                <el-form-item label="文件夹名">
                    <el-input v-model="folderRenameDialog.form.newName" placeholder="请输入新名称"></el-input>
                </el-form-item>
            </el-form>
            <span slot="footer">
        <el-button @click="folderRenameDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitFolderRename">确 定</el-button>
      </span>
        </el-dialog>

        <!-- 文件夹移动对话框 -->
        <el-dialog
            title="移动文件夹"
            :visible.sync="folderMoveDialog.visible"
            width="400px">
            <el-tree
                :data="folders"
                :props="defaultProps"
                @node-click="handleFolderMoveTargetSelect"
                :highlight-current="true"
                node-key="id">
        <span class="custom-tree-node" slot-scope="{ node, data }">
          <i class="el-icon-folder" style="margin-right: 4px; color: #8446df;"></i>
          <span>@{{ node.label }}</span>
        </span>
            </el-tree>
            <span slot="footer">
        <el-button @click="folderMoveDialog.visible = false">取 消</el-button>
        <el-button type="primary" @click="submitFolderMove">确 定</el-button>
      </span>
        </el-dialog>
    </div>
@endsection

@push('footer')
    <script>
        new Vue({
            el: '#app',
            created() {
            },
            mounted() {
                this.loadFolders();
                this.loadFiles();
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
                    isIframeMode: {{ json_encode($isIframe) }},
                    fileType: '{{ $type }}',
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
                    if (!this.folderDialog.form.name) {
                        this.$message.warning('请输入文件夹名称');
                        return;
                    }

                    http.post('file_manager/directories', {
                        name: this.folderDialog.form.name,
                        parent_id: this.currentFolder ? this.currentFolder.path : '/'
                    }).then(res => {
                        if (res.success) {
                            this.$message.success('创建成功');
                            this.folderDialog.visible = false;
                            this.folderDialog.form.name = '';
                            // 重新加载文件夹树
                            this.loadFolders();
                        } else {
                            this.$message.error(res.message || '创建失败');
                        }
                    }).catch(err => {
                        this.$message.error('创建失败：' + err.message);
                    });
                },
                deleteFiles() {
                    if (!this.selectedFiles.length) return;

                    this.$confirm('确认删除选中的文件?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
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
                                this.$message.success('删除成功');
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
                            // 多选模式：切换选择状态
                            this.toggleFileSelect(file);
                        } else {
                            // 单选模式：直接返回并关闭
                            window.parent.fileManagerCallback(file);
                            parent.layer.closeAll();
                        }
                    } else {
                        this.selectedFiles = [file.id || file.path];
                    }
                },
                handleNodeClick(data) {
                    this.currentFolder = data;
                    this.loadFiles(data.path);
                },
                loadFiles(path) {
                    this.loading = true;
                    const params = {
                        page: this.pagination.page,
                        per_page: this.pagination.per_page,
                        base_folder: path
                    };

                    http.get('file_manager/files', { params })
                        .then(res => {
                            // 处理文件列表数据
                            this.files = res.images.map(file => ({
                                ...file,
                                id: file.id || file.path, // 确保每个文件都有唯一标识
                                selected: false,
                                preview_url: file.url, // 保存预览URL
                                url: file.path ? file.path : file.url // 实际文件路径
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

                                            // 关闭上传话框
                                            this.uploadDialog.visible = false;

                                            // 刷新件列表
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
                },

                // 获取文件夹树
                loadFolders() {
                    http.get('file_manager/directories').then(res => {
                        const folders = Array.isArray(res.data) ? res.data : [];

                        this.folders = [{
                            id: '/',
                            name: '图片空间',
                            path: '/',
                            isRoot: true,
                            children: folders.map(folder => ({
                                id: folder.path,
                                name: folder.name,
                                path: folder.path,
                                children: folder.children?.map(child => ({
                                    id: child.path,
                                    name: child.name,
                                    path: child.path,
                                    children: child.children || []
                                })) || []
                            }))
                        }];

                        // 默认选中根目录
                        this.currentFolder = {
                            id: '/',
                            name: '图片空间',
                            path: '/'
                        };

                        // 设置默认展开的节点
                        this.defaultExpandedKeys = ['/'];

                        // 加载根目录的文件
                        this.loadFiles('/');
                    }).catch(err => {
                        this.$message.error('获取文件夹失败：' + err.message);
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
                        this.$message.warning('请输入新名称');
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
                            this.$message.success('重命名成功');
                            this.renameDialog.visible = false;
                            this.loadFiles(currentPath);
                        }
                    });
                },

                // 删除单个文件
                deleteFile() {
                    const file = this.contextMenu.file;
                    this.$confirm('确认删除该文件?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
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
                                this.$message.success('删除成功');
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
                        this.$message.warning('请选择目标文件夹');
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
                            this.$message.success('移动成功');
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
                        this.$message.warning('请选择目标文件夹');
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
                            this.$message.success('复制成功');
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
                        this.$message.warning('请输入新名称');
                        return;
                    }

                    const folder = this.folderRenameDialog.form.folder;
                    http.post('file_manager/rename', {
                        origin_name: folder.path,
                        new_name: this.folderRenameDialog.form.newName
                    }).then(res => {
                        if (res.success) {
                            this.$message.success('重命名成功');
                            this.folderRenameDialog.visible = false;
                            // 重新加载文件夹树
                            this.loadFolders();
                        }
                    });
                },

                // 删除文件夹
                deleteFolder() {
                    const folder = this.folderContextMenu.folder;
                    this.$confirm('确认删除该文件夹?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        http.delete('file_manager/directories', {
                            data: {
                                name: folder.path
                            }
                        }).then(res => {
                            if (res.success) {
                                this.$message.success('删除成功');
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
                        this.$message.warning('不能移动到自己或自己的子文件夹下');
                        return;
                    }
                    this.folderMoveDialog.targetPath = data.path;
                },

                // 提交文件夹移动
                submitFolderMove() {
                    if (!this.folderMoveDialog.targetPath) {
                        this.$message.warning('请选择目标文件夹');
                        return;
                    }

                    const folder = this.folderMoveDialog.folder;
                    http.post('file_manager/move_directories', {
                        source_path: folder.path,
                        dest_path: this.folderMoveDialog.targetPath
                    }).then(res => {
                        if (res.success) {
                            this.$message.success('移动成功');
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
                            this.$message.success('移动成功');
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
                            this.$message.success('移动成功');
                            this.loadFolders();
                            if (this.currentFolder && this.currentFolder.path === sourcePath) {
                                this.loadFiles(targetPath);
                            }
                        }
                    }).catch(err => {
                        this.loadFolders();
                        this.$message.error(err.message || '移动失败');
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
                                this.$message.success('移动成功');
                                this.loadFiles(currentPath);
                            }
                        }).catch(err => {
                            this.$message.error(err.message || '移动失败');
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
                                this.$message.success('移动成功');
                                this.loadFolders();
                                if (this.currentFolder && this.currentFolder.path === sourcePath) {
                                    this.loadFiles(targetPath);
                                }
                            }
                        }).catch(err => {
                            this.loadFolders();
                            this.$message.error(err.message || '移动失败');
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
                            this.$message.success('移动成功');
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
                            this.$message.warning('请至少选择一个文件');
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
                }
            },
            beforeDestroy() {
                document.removeEventListener('click', this.hideContextMenu);
                document.removeEventListener('click', this.hideFolderContextMenu);
            }
        });
    </script>
@endpush
