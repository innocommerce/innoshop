<script>
  // 创建 Vue 实例
  const app = new Vue({
    el: '#app',
    data: {
      form: {
        modules: []
      },
      source: {
        locale: $locale || 'zh_cn',
        modules: []
      },
      lang: lang, // 挂载全局 lang 对象
      design: {
        type: 'pc',
        editType: 'add',
        sidebar: true,
        editingModuleIndex: 0,
        ready: false,
        moduleLoadCount: 0,
        editorInitialized: false, // 新增：跟踪编辑器是否已真正初始化
      },
      showPropertyPanel: false,
      saveStatus: 'saved', // saved, unsaved, saving
      saveStatusText: '已保存',
      lastSavedTime: null,
      moduleSearch: '',
      selectedCategory: null,

    },

    computed: {
      editingModuleComponent() {
        if (!this.form.modules ||
          !this.form.modules.length ||
          this.design.editingModuleIndex < 0 ||
          !this.form.modules[this.design.editingModuleIndex] ||
          !this.form.modules[this.design.editingModuleIndex].code) {
          return null;
        }

        const module = this.form.modules[this.design.editingModuleIndex];
        return 'module-editor-' + module.code.replace('_', '-');
      },
      
      moduleCategories() {
        return [
          { value: 'product', label: lang.product_module },
          { value: 'media', label: lang.media_module },
          { value: 'content', label: lang.content_module },
          { value: 'layout', label: lang.layout_module }
        ];
      },
      
      filteredModules() {
        let modules = this.source.modules;
        
        // 按分类过滤
        if (this.selectedCategory) {
          modules = modules.filter(module => {
            const category = this.getModuleCategory(module.code);
            return category === this.selectedCategory;
          });
        }
        
        // 按搜索关键词过滤
        if (this.moduleSearch) {
          const search = this.moduleSearch.toLowerCase();
          modules = modules.filter(module => {
            const title = (module.title || module.name || '').toLowerCase();
            const code = (module.code || '').toLowerCase();
            return title.includes(search) || code.includes(search);
          });
        }
        
        return modules;
      }
    },

    watch: {
      'design.editingModuleIndex': function(newVal) {
        if (newVal >= 0) {
          this.showPropertyPanel = true;
        }
      },

      'form.modules': {
        handler: function(newVal) {
          if (newVal.length === 0) {
            this.showPropertyPanel = false;
            this.design.editingModuleIndex = -1;
          }
        },
        deep: true
      }
    },

    methods: {
      // 使用 inno.debounce 保持 this 上下文
      moduleUpdated: inno.debounce(function(val) {
        // 防止编辑器初始化时触发 AJAX
        if (!this.design || !this.design.editorInitialized) {
          if (this.design) {
            this.design.moduleLoadCount = 1;
            this.design.editorInitialized = true; // 标记编辑器已初始化
          }
          return;
        }
        
        this.form.modules[this.design.editingModuleIndex].content = val;
        const data = this.form.modules[this.design.editingModuleIndex];
        
        // 更新保存状态
        this.saveStatus = 'unsaved';
        this.saveStatusText = '未保存';
        
        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ panel_route('pbuilder.modules.preview', ['page' => 'home']) }}' : '{{ panel_route('pbuilder.modules.preview', ['page' => ':page']) }}'.replace(':page', page);
        axios.post(url + '?design=1', data).then((res) => {
          $(previewWindow.document).find('#module-' + data.module_id).replaceWith(res);
          $(previewWindow.document).find('.tooltip').remove();
          const tooltipTriggerList = previewWindow.document.querySelectorAll('[data-bs-toggle="tooltip"]')
          const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new previewWindow.bootstrap.Tooltip(tooltipTriggerEl))
        }).catch((error) => {
          // 处理模块更新错误
          let errorMessage = '更新模块失败';
          
          if (error.response) {
            // 服务器返回了错误状态码
            const status = error.response.status;
            const data = error.response.data;
            
            if (status === 404) {
              errorMessage = '模块模板文件不存在，请联系管理员';
            } else if (status === 500) {
              errorMessage = '服务器内部错误，请稍后重试';
            } else if (status === 422) {
              errorMessage = '模块数据格式错误：' + (data.message || '未知错误');
            } else if (data && data.message) {
              errorMessage = data.message;
            } else {
              errorMessage = `请求失败 (${status})`;
            }
          } else if (error.request) {
            // 请求已发出但没有收到响应
            errorMessage = '网络连接失败，请检查网络连接';
          } else {
            // 其他错误
            errorMessage = error.message || '未知错误';
          }
          
          // 使用layer弹窗显示错误信息
          layer.msg(errorMessage, {
            icon: 2,
            time: 3000,
            shade: [0.3, '#000']
          });
          
          console.error('更新模块失败:', error);
        })
      }, 300),

      addModuleButtonClicked(code, moduleItemIndex = null, callback = null) {
        const sourceModule = this.source.modules.find(e => e.code == code)
        const module_id = randomString(16)
        const _data = {
          code: code,
          content: sourceModule.make || sourceModule.content,
          module_id: module_id,
          name: sourceModule.title || sourceModule.name,
          view_path: sourceModule.view_path || '',
        }

        // 更新保存状态
        this.saveStatus = 'unsaved';
        this.saveStatusText = '未保存';

        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ panel_route('pbuilder.modules.preview', ['page' => 'home']) }}' : '{{ panel_route('pbuilder.modules.preview', ['page' => ':page']) }}'.replace(':page', page);
        axios.post(url + '?design=1', _data).then((res) => {
          if (moduleItemIndex === null) {
            $(previewWindow.document).find('.modules-box').append(res);
            this.form.modules.push(_data);
            this.design.editingModuleIndex = this.form.modules.length - 1;
            this.design.editType = 'module';
          } else {
            $(previewWindow.document).find('.modules-box').children().eq(moduleItemIndex).before(res);
            this.form.modules.splice(moduleItemIndex, 0, _data);
            this.design.editingModuleIndex = moduleItemIndex;
            this.design.editType = 'module';
          }

          setTimeout(() => {
            const moduleElement = $(previewWindow.document).find('#module-' + module_id);
            if (moduleElement.length > 0 && moduleElement.offset()) {
              $(previewWindow.document).find("html, body").animate({
                scrollTop: moduleElement.offset().top - 96
              }, 50);
            }
          }, 200)
        }).catch((error) => {
          // 处理AJAX错误
          let errorMessage = '添加模块失败';
          
          if (error.response) {
            // 服务器返回了错误状态码
            const status = error.response.status;
            const data = error.response.data;
            
            if (status === 404) {
              errorMessage = '模块模板文件不存在，请联系管理员';
            } else if (status === 500) {
              errorMessage = '服务器内部错误，请稍后重试';
            } else if (status === 422) {
              errorMessage = '模块数据格式错误：' + (data.message || '未知错误');
            } else if (data && data.message) {
              errorMessage = data.message;
            } else {
              errorMessage = `请求失败 (${status})`;
            }
          } else if (error.request) {
            // 请求已发出但没有收到响应
            errorMessage = '网络连接失败，请检查网络连接';
          } else {
            // 其他错误
            errorMessage = error.message || '未知错误';
          }
          
          // 使用layer弹窗显示错误信息
          layer.msg(errorMessage, {
            icon: 2,
            time: 3000,
            shade: [0.3, '#000']
          });
          
          console.error('添加模块失败:', error);
        }).finally(() => {
          if (callback) {
            callback();
          }
        })
      },

      editModuleButtonClicked(index) {
        if (this.design) {
          // 如果已经是当前编辑的模块，不重复处理
          if (this.design.editingModuleIndex === index && this.design.editType === 'module') {
            console.log('已经是当前编辑的模块，跳过重复处理', index);
            return;
          }
          
          this.design.moduleLoadCount = 0;
          this.design.editingModuleIndex = index;
          this.design.editType = 'module';
          this.design.editorInitialized = false; // 重置编辑器初始化状态
        }
      },

      saveButtonClicked() {
        this.saveStatus = 'saving';
        this.saveStatusText = '保存中...';
        
        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ panel_route('pbuilder.modules.update', ['page' => 'home']) }}' : '{{ panel_route('pbuilder.modules.update', ['page' => ':page']) }}'.replace(':page', page);
        
        axios.put(url, this.form).then((res) => {
          this.saveStatus = 'saved';
          this.saveStatusText = '已保存';
          this.lastSavedTime = new Date();
          layer.msg(res.message, {icon: 1});
        }).catch((error) => {
          this.saveStatus = 'unsaved';
          this.saveStatusText = '保存失败';
          layer.msg('保存失败：' + (error.response?.data?.message || error.message), {icon: 2});
        });
      },

      importDemoData() {
        const page = '{{ $page ?? "home" }}';
        if (page !== 'home') {
          layer.msg('演示数据仅支持首页');
          return;
        }
        
        if (confirm('确定要导入演示数据吗？这将覆盖当前的页面设计。')) {
          const url = '{{ panel_route('pbuilder.demo.import', ['page' => 'home']) }}';
          axios.post(url).then((res) => {
            layer.msg(res.message);
            // 重新加载页面以显示演示数据
            setTimeout(() => {
              location.reload();
            }, 1000);
          }).catch((error) => {
            layer.msg('导入失败：' + (error.response?.data?.message || error.message));
          });
        }
      },

      viewHome() {
        location = '{{ front_route('home.index') }}';
      },

      isIcon(code) {
        // 判断是否为 HTML 标签格式的图标
        return typeof code === 'string' && (code.indexOf('<i') === 0 || code.indexOf('&#') === 0);
      },
      
      getCurrentModuleIcon() {
        if (!this.form.modules || 
            !this.form.modules.length || 
            this.design.editingModuleIndex < 0 || 
            !this.form.modules[this.design.editingModuleIndex]) {
          return null;
        }
        
        const currentModule = this.form.modules[this.design.editingModuleIndex];
        const sourceModule = this.source.modules.find(module => module.code === currentModule.code);
        
        return sourceModule ? sourceModule.icon : null;
      },
      
      getModuleCategory(code) {
        // 定义模块的单分类映射
        const moduleCategories = {
          // 媒体模块 - 图片、视频等媒体内容
          'slideshow': 'media',
          'single-image': 'media',
          'four-image': 'media',
          'four-image-plus': 'media',
          'multi-row-images': 'media',
          'video': 'media',
          
          // 商品模块 - 与商品相关的模块
          'custom-products': 'product',
          'category-products': 'product',
          'latest-products': 'product',
          'brand-products': 'product',
          'card-slider': 'product',
          
          // 内容模块 - 文字、文章等内容
          'rich-text': 'content',
          'article': 'content',
          'brands': 'content',
          
          // 布局模块 - 布局和结构相关的模块
          'left-image-right-text': 'layout',
          'image-text-list': 'layout'
        };
        
        // 返回模块的分类，如果没有找到则返回 'layout'
        return moduleCategories[code] || 'layout';
      },

      showAllModuleButtonClicked() {
        if (this.design) {
          this.design.editType = 'add';
          this.design.editingModuleIndex = 0;
        }
      },

      switchDevice(type) {
        if (this.design) {
          this.design.type = type;
        }
        const iframe = document.getElementById('preview-iframe');
        const previewContainer = document.querySelector('.preview-iframe');
        
        // 检查 previewContainer 是否存在
        if (!previewContainer) {
          console.warn('Preview container not found');
          return;
        }
        
        // 移除所有设备类
        previewContainer.classList.remove('device-pc', 'device-mobile');
        
        if (type === 'mobile') {
          previewContainer.classList.add('device-mobile');
          if (iframe) {
            iframe.style.width = '375px';
            iframe.style.height = '667px';
            iframe.style.maxWidth = '375px';
            iframe.style.maxHeight = '667px';
          }
        } else {
          // PC 设备
          previewContainer.classList.add('device-pc');
          if (iframe) {
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.maxWidth = 'none';
            iframe.style.maxHeight = 'none';
          }
        }
      },

      // 初始化键盘快捷键
      initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
          // 只在非输入框中生效
          if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
          }
          
          // Ctrl+S 保存
          if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            this.saveButtonClicked();
          }
          
          // Delete 删除选中的模块
          if (e.key === 'Delete' && this.design.editingModuleIndex >= 0) {
            e.preventDefault();
            this.deleteCurrentModule();
          }
          
          // Ctrl+Z 撤销（预留）
          if (e.ctrlKey && e.key === 'z') {
            e.preventDefault();
            // this.undo();
          }
          
          // Ctrl+Y 重做（预留）
          if (e.ctrlKey && e.key === 'y') {
            e.preventDefault();
            // this.redo();
          }
          
          // Esc 退出编辑模式
          if (e.key === 'Escape') {
            e.preventDefault();
            this.showAllModuleButtonClicked();
          }
        });
      },
      
      // 删除当前模块
      deleteCurrentModule() {
        if (this.design.editingModuleIndex >= 0 && this.form.modules[this.design.editingModuleIndex]) {
          if (confirm('确定要删除该模块吗？')) {
            this.design.editType = 'add';
            this.design.editingModuleIndex = 0;
            this.form.modules.splice(this.design.editingModuleIndex, 1);
            this.saveStatus = 'unsaved';
            this.saveStatusText = '未保存';
          }
        }
      }
    },
    
    created () {
      this.form = @json($design_settings ?: ['modules' => []]);
      this.source.modules = @json($source['modules'] ?? []);
    },
    
    mounted () {
      // 初始化设备类型
      this.switchDevice(this.design.type);
      
      // 确保 iframe 加载完成后设置 ready 状态
      setTimeout(() => {
        if (this.design) {
          this.design.ready = true;
        }
      }, 1000);
      
      // 添加键盘快捷键
      this.initKeyboardShortcuts();
    },
  })
</script>