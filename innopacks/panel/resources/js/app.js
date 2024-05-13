import './bootstrap';

import './bootstrap-validation';
import './alert';
import "./autocomplete";

const base = document.querySelector('base').href;
const editor_language = document.querySelector('meta[name="editor_language"]')?.content || 'zh_cn';

$(function () {
  tinymceInit();
})

const tinymceInit = () => {
  if (typeof tinymce == 'undefined') {
    return;
  }

  tinymce.init({
    selector: '.tinymce',
    language: editor_language,
    branding: false,
    height: 500,
    convert_urls: false,
    // document_base_url: 'ssssss',
    inline: false,
    relative_urls: false,
    plugins: "link lists fullscreen table hr wordcount image imagetools code",
    menubar: "",
    toolbar: "undo redo | toolbarImageButton | lineheight | bold italic underline strikethrough | forecolor backcolor | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | formatpainter removeformat | charmap emoticons | preview | template link anchor table toolbarImageUrlButton | fullscreen code",
    // contextmenu: "link image imagetools table",
    toolbar_items_size: 'small',
    image_caption: true,
    imagetools_toolbar: '',
    toolbar_mode: 'wrap',
    font_formats:
      "微软雅黑='Microsoft YaHei';黑体=黑体;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Georgia=georgia,palatino;Helvetica=helvetica;Times New Roman=times new roman,times;Verdana=verdana,geneva",
    fontsize_formats: "10px 12px 14px 18px 24px 36px 48px 56px 72px 96px",
    lineheight_formats: "1 1.1 1.2 1.3 1.4 1.5 1.7 2.4 3 4",
    setup: function(ed) {
      ed.ui.registry.addButton('toolbarImageButton',{
        icon: 'image',
        onAction:function() {
          $('#form-upload').remove();
          $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
          $('#form-upload input[name=\'file\']').trigger('click');
          $('#form-upload input[name=\'file\']').on('change', function() {
            let file = this.files[0];
            let formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'common');
            layer.load(2, {shade: [0.3,'#fff'] })
            axios.post('/upload/images', formData, {}).then(function (res) {
                let url = res.data.data.url;
                ed.insertContent('<img src="' + url + '" class="img-fluid" />');
            }).catch(function (err) {
                layer.msg(err.response.data.message, {icon: 2});
            }).finally(function () {
                layer.closeAll('loading');
            });
          });
        }
      });
    }
  });
}