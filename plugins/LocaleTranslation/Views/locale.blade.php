@extends('panel::layouts.app')

@section('title', __('LocaleTranslation::common.locale_translation'))

@push('header')
  <script src="{{ asset('vendor/vue/2.7/vue.min.js') }}"></script>
@endpush

@section('content')
  <div class="card" id="app" v-cloak>

    <div class="card-body h-min-600" id="main-card">
      <div class="d-flex align-items-center mb-2">
        <span>{{ __('LocaleTranslation::common.select_module') }}</span>
        <select class="form-select wp-200 plugins-select">
          <option value="">InnoShop</option>
          @foreach ($plugins as $item)
            <option value="{{ $item['code'] }}" {{ $plugin_code == $item['code'] ? 'selected' : '' }}>{{ $item['name'] ?? '' }}</option>
          @endforeach
        </select>
      </div>

      <div class="locales-box">
        <div class="left">
          <div class="accordion folders-wrap">
            <div class="accordion-item" v-for="tree, index in treeData" :key="index">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapseOne-' + index" aria-expanded="true" aria-controls="collapseOne">
                  <i class="bi-folder"></i>&nbsp; @{{ tree.name }}
                </button>
              </h2>
              <div :id="'collapseOne-' + index" class="accordion-collapse collapse">
                <div class="">
                  {{-- 有 folders 先循环 folders--}}
                  <div class="accordion-item" v-for="folder_2, index_2 in tree.folders" :key="index + '-' + index_2" v-if="tree.folders.length">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapseOne-' + index + '-' + index_2" aria-expanded="true" aria-controls="collapseOne">
                        <i class="bi-folder"></i>&nbsp; @{{ folder_2.name }}
                      </button>
                    </h2>
                    <div :id="'collapseOne-' + index + '-' + index_2" class="accordion-collapse collapse">
                      <div class="">
                        {{-- 在 folders 后再循环 files--}}
                        <ul class="files-wrap list-group list-group-flush" v-if="folder_2.files.length">
                          <li :class="['list-group-item cursor-pointer', activeLocale == file ? 'active' : '']" @click="getLanguageEntry(file)" v-for="file, file_index in folder_2.files" :key="index + '-' + file_index"><i class="bi-file-earmark"></i> @{{ file }}</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  {{-- 在 folders 后再循环 files--}}
                  <ul class="files-wrap list-group list-group-flush" v-if="tree.files.length">
                    <li :class="['list-group-item cursor-pointer', activeLocale == file ? 'active' : '']" @click="getLanguageEntry(file)" v-for="file, file_index in tree.files" :key="index + '-' + file_index"><i class="bi-file-earmark"></i> @{{ file }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <ul class="files-wrap list-group mt-2" v-if="files.length">
            <li :class="['list-group-item cursor-pointer', activeLocale == file ? 'active' : '']" @click="getLanguageEntry(file)" v-for="file, index in files" :key="index"><i class="bi-file-earmark"></i> @{{ file }}</li>
          </ul>
        </div>
        <div class="right ms-3" v-if="localesValues.locale_codes">
          <div class="table-responsive" id="table-responsive">
            <table class="table table-bordered">
              <thead>
                <th style="background-color: #eaf6ff" ><div>{{ __('LocaleTranslation::common.column') }}</div><button class="btn btn-sm opacity-0">1</button></th>
                <th class="text-center locale-th" style="background-color: #D6BFF1" >
                  <div class="mb-1">@{{ localesValues.base.code }}</div>
                  <button type="button" @click="format(localesValues.base.code)" class="btn btn-outline-primary btn-sm">
                    {{ __('LocaleTranslation::common.format') }}
                  </button>
                </th>
                <th v-for="value, index in localesValues.locale_codes"  :key="index" class="text-center locale-th">
                  <div class="mb-1">@{{ value }}</div>
                  <button type="button" @click="translateCol(value)" v-if="isShowTranslateBtn(value)" class="btn btn-outline-primary btn-sm">
                    {{ __('LocaleTranslation::common.batch_translate') }}
                  </button>
                  <button type="button" @click="format(value)" class="btn btn-outline-primary btn-sm">
                    {{ __('LocaleTranslation::common.format') }}
                  </button>
                  <button v-else class="btn btn-sm opacity-0">1</button>
                </th>
              </thead>
              <tbody>
                <tr v-for="item, index in localesValues.base.keys" :key="index">
                  <td style="background-color: #eaf6ff">(@{{ index }})@{{ item }}</td>
                  <td style="background-color: #D6BFF1">
                    <el-tooltip v-if="isString(localesValues.base.values[item])" class="item" effect="dark" :content="localesValues.base.values[item]" placement="top-start">
                      <span class="value-item">@{{ localesValues.base.values[item] }}</span>
                    </el-tooltip>
                    <span v-else class="value-item">@{{ localesValues.base.values[item] }}</span>
                  </td>
                  <td v-for="value, index_2 in localesValues.extra" :key="index + '-' + index_2">
                    <el-tooltip class="item" effect="dark" :content="value[item]" placement="top-start" v-if="value[item]">
                      <span class="value-item">@{{ value[item] }}</span>
                    </el-tooltip>
                    <button v-else type="button" @click="translate(item, index_2)" class="btn btn-outline-primary btn-sm">
                      {{ __('LocaleTranslation::common.translate') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="w-100 h-min-300 border ms-3 fs-2 text-muted d-flex align-items-center justify-content-center">
          <span style="margin-top: -40px">{{ __('LocaleTranslation::common.please_select_file') }}</span>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    var app = new Vue({
      el: '#app',
      components: {},
      data: {
        treeData: @json($locales['folders'] ?? []),
        files: @json($locales['files']),
        plugin_code: @json($plugin_code ?? ''),
        localesValues: [],
        activeLocale: @json($file_path ?? ''),
      },
      created() {
        if (this.activeLocale) {
          this.getLanguageEntry(this.activeLocale)
        }
      },
      methods: {
        isString(value) {
          return typeof value === 'string'
        },

        getLanguageEntry(file) {
          this.activeLocale = file
          axios.get('{{ panel_route('locale_translations.values') }}', {params:{file_path: file, plugin_code: this.plugin_code}}).then((res) => {
            this.localesValues = res
            var url = new URL(window.location.href)
            url.searchParams.set('file_path', file)
            window.history.pushState({}, 0, url.href)

            setTimeout(() => {
              setTableWidth()
            }, 0);
          })
        },

        translate(locale, code) {
          axios.post('{{ panel_route('locale_translations.translate') }}', {file_path: this.activeLocale, targets: [code], keys: [locale], plugin_code: this.plugin_code}).then((res) => {

            setTimeout(() => {
              this.getLanguageEntry(this.activeLocale)
            }, 1000);
          })
        },

        isShowTranslateBtn(value) {
          var keys = this.localesValues.base.keys
          return keys.some(item => !this.localesValues.extra[value][item])
        },

        translateCol(value) {
          // 选择筛选出 this.localesValues.extra[value] 中的key
          var keys = Object.keys(this.localesValues.extra[value])

          // 选择筛选出 this.localesValues.base.keys 中没有的key
          var keys_2 = this.localesValues.base.keys.filter(item => !keys.includes(item))

          if (!keys_2.length) {
            layer.msg('没有需要翻译的词条')
            return
          }

          axios.post('{{ panel_route('locale_translations.translate') }}', {file_path: this.activeLocale, targets: [value], keys: keys_2, plugin_code: this.plugin_code}).then((res) => {
            setTimeout(() => {
              this.getLanguageEntry(this.activeLocale)
            }, 1000);
          })
        },

        format(value) {
          axios.post('{{ panel_route('locale_translations.format') }}', {file_path: this.activeLocale, targets: [value], plugin_code: this.plugin_code}).then((res) => {
            setTimeout(() => {
              this.getLanguageEntry(this.activeLocale)
            }, 1000);
          }).catch(function (error) {
            inno.msg(error.response.data.message)
          });
        }
      }
    })

    $('.plugins-select').on('change', function() {
      var path = $(this).val()
      if (path) {
        window.location.href = 'panel/locales?plugin_code=' + path
      } else {
        window.location.href = 'panel/locales'
      }
    })

    function setTableWidth() {
      const mainCardWidth = $('#main-card').width()
      $('#table-responsive').width(mainCardWidth - 260 - 20)
    }

    // 展开 .list-group-item class 为 active ，的 accordion
    $('.list-group-item.active').parents('.accordion-collapse').addClass('show')
    $('.list-group-item.active').parents('.accordion-collapse').siblings('.accordion-header').find('.accordion-button').removeClass('collapsed')
  </script>

  <style>
    .locales-box {
      display: flex;
    }

    .locales-box .left {
      flex: 0 0 260px;
    }

    .locales-box .right {
      flex: 1;
    }

    .btn-sm {
      font-size: 12px;
      padding: 2px 6px;
    }

    .value-item {
      display: block;
      width: 80px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .locale-th {
      position: relative;
    }

    .locale-th button {
      background: #fff;
    }
  </style>
@endpush
