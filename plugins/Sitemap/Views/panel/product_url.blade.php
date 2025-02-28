<div class="row g-3 mb-3"><label class="wp-200 col-form-label text-end">SEO URL 别名</label>
  <div class="col-auto wp-200-">
    <el-input v-model="form.seo_url_name" name="seo_url_name"/>
  </div>
</div>
<div class="row g-3 mb-3"><label class="wp-200 col-form-label text-end"></label>
  <div class="help-text col-auto wp-200-">
    为了安全起见，别名中仅支持：'-_这三种符号，其他符号均会自动替换成-(搜索引擎对-友好)。不填写时，保存后自动使用英文名字生成别名。
    <br>
    <span v-if="form.seo_url_name">
    URL:{{env('APP_URL')}}/@{{form.seo_url_name }}{{$ext}}
    </span>
  </div>
</div>
@push("footer")
  <script>
    $(function () {
      app.$set(app.form, "seo_url_name", @json($seo_url_name));
    })

  </script>
@endpush
