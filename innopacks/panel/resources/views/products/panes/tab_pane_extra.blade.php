<div class="tab-pane fade mt-3" id="extra-tab-pane" role="tabpanel" aria-labelledby="extra-tab"
     tabindex="3">
  <div class="row">
    <div class="col-12 col-md-6" id="product-category">
      {{-- 分类选择 --}}
      <x-panel::form.row title="{{ __('panel/product.category') }}">
        <div class="category-select">
          <el-cascader
            :options="source.categories"
            size="medium"
            ref="refCascader"
            placeholder="请选择/搜索分类"
            :props="{ label: 'label', value: 'value', children: 'children', checkStrictly: true}"
            @change="categoriesChange"
            filterable
            class="category-cascader"
            :class="!categoryFormat.length ? 'no-data' : ''"
            style="width: 100%;">
          </el-cascader>

          <div class="category-data" v-if="categoryFormat.length">
            <div class="d-flex flex-wrap gap-2 mt-2">
              <span v-for="item, index in categoryFormat" :key="index" 
                    class="badge bg-light text-dark border d-flex align-items-center px-2 py-1" 
                    style="font-size: 0.875rem; border-radius: 6px; font-weight: normal;">
                <span class="me-2">@{{ item.fullPath }}</span>
                <i class="bi bi-x cursor-pointer" 
                   @click="removeCategory(index)" 
                   style="font-size: 0.75rem; opacity: 0.7;"
                   @mouseover="$event.target.style.opacity='1'" 
                   @mouseout="$event.target.style.opacity='0.7'"></i>
                <input type="hidden" name="categories[]" :value="item.value">
              </span>
            </div>
          </div>
        </div>
      </x-panel::form.row>
    </div>
    <div class="col-12 col-md-6">
      <x-common-form-select :title="__('panel/product.brand')" name="brand_id"
                            :value="old('brand_id', $product->brand_id ?? 0)" :options="$brands"
                            key="id" label="name"/>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6">
      <div class="row">
        <div class="col-md-6">
          <x-common-form-input :title="__('panel/product.weight')" name="weight"
                           :value="old('weight', $product->weight ?? '')" :placeholder="__('panel/product.weight')"/>
        </div>
        <div class="col-md-6">
          <x-common-form-select :title="__('panel/product.weight_class')" name="weight_class"
                             :value="old('weight_class', $product->weight_class ?? '')" :options="$weightClasses"
                             key="code" label="name" />
        </div>
      </div>
      <x-common-form-select :title="__('panel/product.tax_class')" name="tax_class_id"
                            :value="old('tax_class_id', $product->tax_class_id ?? 0)" :options="$tax_classes"
                            key="id" label="name"/>
    </div>
    <div class="col-12 col-md-6">
      <x-common-form-input :title="__('panel/product.spu_code')" name="spu_code"
                           :value="old('spu_code', $product->spu_code ?? '')"
                           :placeholder="__('panel/product.spu_code')"/>
      <x-common-form-input :title="__('panel/common.position')" name="position"
                           :value="old('position', $product->position ?? '')"
                           :placeholder="__('panel/common.position')"/>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6">
      <x-common-form-input :title="__('panel/product.sales')" name="sales" :value="old('sales', $product->sales ?? '')"
                           :placeholder="__('panel/product.sales')"/>
      <x-common-form-input :title="__('panel/product.viewed')" name="viewed"
                           :value="old('viewed', $product->viewed ?? '')" :placeholder="__('panel/product.viewed')"/>
    </div>
    <div class="col-12 col-md-6">
      <x-common-form-switch-radio :title="__('panel/product.is_virtual')" name="is_virtual"
                              :value="old('is_virtual', $product->is_virtual ?? false)"/>
    </div>
  </div>
</div>

@push('footer')
<script src="https://unpkg.com/element-plus/dist/index.full.js"></script>
<link rel="stylesheet" href="https://unpkg.com/element-plus/dist/index.css">
<script>
  const productApp = Vue.createApp({
    data() {
      return {
        form: {
          categories: @json(old('categories', $product->categories->pluck('id')->toArray()) ?? []),
        },
        source: {
          categories: @json($categories),
        }
      };
    },
    mounted() {
      console.log('Vue instance mounted');
      console.log('Categories data:', this.source.categories);
      console.log('Categories data structure:', JSON.stringify(this.source.categories, null, 2));
      console.log('Vue version:', Vue.version);
      console.log('Element Plus available:', typeof ElCascader !== 'undefined');
      // 检查组件是否已渲染
      this.$nextTick(() => {
        console.log('Cascader component:', this.$refs.refCascader);
      });
    },


    computed: {
      // 格式化已选分类显示
      categoryFormat() {
        const categories = JSON.parse(JSON.stringify(this.source.categories));
        const categoryIds = this.form.categories;
        const categoryFormat = [];

        // 递归查找分类并构建完整路径
        const findCategoryWithPath = (cats, id, parentPath = []) => {
          for (let cat of cats) {
            const currentPath = [...parentPath, cat.label];
            if (cat.value == id) {
              return {
                value: cat.value,
                label: cat.label,
                fullPath: currentPath.join(' > ')
              };
            }
            if (cat.children && cat.children.length) {
              const found = findCategoryWithPath(cat.children, id, currentPath);
              if (found) return found;
            }
          }
          return null;
        };

        categoryIds.forEach((categoryId) => {
          const category = findCategoryWithPath(categories, categoryId);
          if (category) {
            categoryFormat.push(category);
          }
        });

        return categoryFormat;
      },
    },

    methods: {
      // 分类选择变化处理
      categoriesChange(e) {
        console.log('Category changed:', e);
        const last = e[e.length - 1];

        // 检查是否已经选择过该分类
        if (last && this.form.categories.includes(last)) {
          this.$message.warning('该分类已经选择过了');
          return;
        }

        if (last) {
          this.form.categories.push(last);
        }
      },

      // 移除分类
      removeCategory(index) {
        this.form.categories.splice(index, 1);
      },
    }
  });
  productApp.use(ElementPlus);
  productApp.mount('#product-category');
  </script>
  @endpush
