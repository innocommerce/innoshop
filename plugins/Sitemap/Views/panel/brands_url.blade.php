<el-form-item label="SEO URL 别名">
  <el-input class="mb-0" type="text" v-model="dialog.form.seo_url_name" placeholder="SEO URL 别名" id=""></el-input>
  <div class="help-text col-auto">
    为了安全起见，别名中仅支持：'-_这三种符号，其他符号均会自动替换成-(搜索引擎对-友好)。不填写时，保存后自动使用英文名字生成别名。
    <br>
    <span v-if="dialog.form.seo_url_name">
    URL:{{env('APP_URL')}}/@{{dialog.form.seo_url_name }}{{$ext}}
    </span>
  </div>
</el-form-item>

@push("footer")
  <script>


  </script>
@endpush
