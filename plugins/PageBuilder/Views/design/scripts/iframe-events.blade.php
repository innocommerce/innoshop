<script>
  var previewWindow = null;
  var modulesBoxSortable = null;
  
  function initModulesBoxSortable() {
    if (!previewWindow || !previewWindow.document) return;
    
    if (modulesBoxSortable) {
      modulesBoxSortable.destroy();
      modulesBoxSortable = null;
    }
    
    const homeModulesBox = previewWindow.document.getElementById('home-modules-box');
    const pageModulesBox = previewWindow.document.getElementById('page-modules-box');
    const modulesBox = homeModulesBox || pageModulesBox;
    
    if (modulesBox && typeof Sortable !== 'undefined') {
      modulesBoxSortable = new Sortable(modulesBox, {
        group: {
          name: 'shared',
          pull: 'clone',
        },
        animation: 150,
        onUpdate: function (evt) {
          const modules = app.form.modules;
          const module = modules.splice(evt.oldIndex, 1)[0];
          modules.splice(evt.newIndex, 0, module);
          app.form.modules = modules;
        }
      });
    }
  }
  
  $('#preview-iframe').on('load', function(event) {
    previewWindow = document.getElementById("preview-iframe").contentWindow;
    if (typeof app !== 'undefined' && app.design) {
      app.design.ready = true;
    }
    
    setTimeout(function() {
      initModulesBoxSortable();
    }, 500);

    $(previewWindow.document).on('click', '.module-edit .edit', function(event) {
      const module_id = $(this).parents('.module-item').prop('id').replace('module-', '');
      const modules = app.form.modules;
      const editingModuleIndex = modules.findIndex(e => e.module_id == module_id);
      if (editingModuleIndex >= 0) {
        app.editModuleButtonClicked(editingModuleIndex);
      }
    });

    $(previewWindow.document).on('click', '.module-edit .delete', function(event) {
      if (typeof app === 'undefined' || !app.form || !app.form.modules) return;
      const module_id = $(this).parents('.module-item').prop('id').replace('module-', '');
      const editingModuleIndex = app.form.modules.findIndex(e => e.module_id == module_id);
      if (editingModuleIndex >= 0) {
        if (confirm(lang.confirm_delete_module)) {
          app.design.editType = 'add';
          app.design.editingModuleIndex = 0;
          $(previewWindow.document).find('.tooltip').remove();
          $(this).parents('.module-item').remove();
          app.form.modules.splice(editingModuleIndex, 1);
        }
      }
    });

    $(previewWindow.document).on('click', '.module-edit .up', function(event) {
      if (typeof app === 'undefined' || !app.form || !app.form.modules) return;
      const module_id = $(this).parents('.module-item').prop('id').replace('module-', '');
      const modules = app.form.modules;
      const editingModuleIndex = modules.findIndex(e => e.module_id == module_id);
      if (editingModuleIndex > 0) {
        const module = modules[editingModuleIndex];
        modules.splice(editingModuleIndex, 1);
        modules.splice(editingModuleIndex - 1, 0, module);
        $(this).parents('.module-item').insertBefore($(this).parents('.module-item').prev());
        app.form.modules = modules;
      }
    });

    $(previewWindow.document).on('click', '.module-edit .down', function(event) {
      if (typeof app === 'undefined' || !app.form || !app.form.modules) return;
      const module_id = $(this).parents('.module-item').prop('id').replace('module-', '');
      const modules = app.form.modules;
      const editingModuleIndex = modules.findIndex(e => e.module_id == module_id);
      if (editingModuleIndex < modules.length - 1) {
        const module = modules[editingModuleIndex];
        modules.splice(editingModuleIndex, 1);
        modules.splice(editingModuleIndex + 1, 0, module);
        $(this).parents('.module-item').insertAfter($(this).parents('.module-item').next());
        app.form.modules = modules;
      }
    });

    new Sortable(document.getElementById('module-list-wrap'), {
        group: {
          name: 'shared',
          pull: 'clone',
          put: false
        },
        animation: 150,
        sort: false,
        onEnd: function (evt) {
          const validContainers = ['home-modules-box', 'page-modules-box'];
          if (!validContainers.includes(evt.to.id)) {
            return;
          }

          const index = $(previewWindow.document).find('.modules-box').children().index(evt.item);
          const moduleCode = $(evt.item).find('.module-list').data('code');

          app.addModuleButtonClicked(moduleCode, index, () => {
            evt.item.parentNode.removeChild(evt.item);
          });
        }
      });

  });
</script> 