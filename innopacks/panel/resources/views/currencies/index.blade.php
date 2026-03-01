@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.currencies'))

@push('header')
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
@endpush

@section('page-title-right')
  <button type="button" class="btn btn-info currency-calculator-btn me-2">
    <i class="bi bi-calculator"></i> {{ __('panel/currency.calculator') }}
  </button>
  <button type="button" class="btn btn-primary btn-add me-2" onclick="app.create()">
    <i class="bi bi-plus-square"></i> {{ __('common/base.create') }}
  </button>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-panel-data-data-search
        :action="panel_route('currencies.index')"
        :searchFields="$searchFields ?? []"
        :filters="$filterButtons ?? []"
        :enableDateRange="false"
      />

      @if ($currencies->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('common/base.id') }}</th>
              <th>{{ __('panel/currency.name') }}</th>
              <th>{{ __('panel/currency.code') }}</th>
              <th>{{ __('panel/currency.symbol_left') }}</th>
              <th>{{ __('panel/currency.symbol_right') }}</th>
              <th>{{ __('panel/currency.decimal_place') }}</th>
              <th>{{ __('panel/currency.value') }}</th>
              <th>{{ __('panel/common.active') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($currencies as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}
                  @if($item->code === system_setting('currency'))
                    <span class="badge bg-success">{{ __('common/base.default') }}</span>
                  @endif
                </td>
                <td>{{ strtoupper($item->code) }}</td>
                <td>{{ $item->symbol_left }}</td>
                <td>{{ $item->symbol_right }}</td>
                <td>{{ $item->decimal_place }}</td>
                <td>{{ $item->value }}</td>
                <td>
                  @include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('currencies.active',
                  $item->id)])
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <el-button size="small" plain type="primary"
                               @click="edit({{ $item->id }})">{{ __('common/base.edit')}}
                    </el-button>
                    <form ref="deleteForm" action="{{ panel_route('currencies.destroy', [$item->id]) }}" method="POST"
                          class="d-inline">
                      @csrf
                      @method('DELETE')
                      <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{
                    __('common/base.delete')}}</el-button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $currencies->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>

    <el-dialog v-model="dialogVisible" :title="form.id ? '{{ __("common/base.edit") }}' : '{{ __("common/base.create") }}'" width="600px">
      <el-form ref="formRef" label-position="top" :model="form" :rules="rules" label-width="auto" status-icon>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.name') }}" prop="name">
              <el-input size="large" v-model="form.name" placeholder="{{ __('panel/currency.name') }}"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.code') }}" prop="code">
              <el-input size="large" v-model="form.code" placeholder="{{ __('panel/currency.code') }}" @input="form.code = form.code.toUpperCase()"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.value') }}" prop="value">
              <el-input size="large" v-model="form.value" placeholder="{{ __('panel/currency.value') }}"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.decimal_place') }}" prop="decimal_place">
              <el-input size="large" v-model="form.decimal_place" placeholder="{{ __('panel/currency.decimal_place') }}">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.symbol_left') }}" prop="symbol_left">
              <el-input size="large" v-model="form.symbol_left" placeholder="{{ __('panel/currency.symbol_left') }}">
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="{{ __('panel/currency.symbol_right') }}" prop="symbol_right">
              <el-input size="large" v-model="form.symbol_right" placeholder="{{ __('panel/currency.symbol_right') }}">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="{{ __('panel/currency.active') }}" prop="active">
              <el-switch v-model="form.active" :active-value="1" :inactive-value="0"></el-switch>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">{{ __('common/base.close') }}</el-button>
          <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
        </div>
      </template>
    </el-dialog>

    @hookinsert('panel.currencies.index.after')

    @include('panel::currencies.calculator_modal')
  </div>
@endsection

@push('footer')
  <script>
    const {createApp, ref, reactive, onMounted, getCurrentInstance, watch} = Vue;
    const {ElMessageBox, ElMessage} = ElementPlus;
    const api = @json(panel_route('currencies.index'));
    const enabledCurrenciesData = @json($enabledCurrencies ?? []);
    const listApp = createApp({
      setup() {
        const dialogVisible = ref(false)
        const {proxy} = getCurrentInstance();
        const form = reactive({
          id: 0,
          name: '',
          code: '',
          symbol_left: '',
          symbol_right: '',
          decimal_place: '',
          value: '',
          active: 1,
        })

        const rules = {}

        // 汇率计算器相关
        const calculatorDialogVisible = ref(false)
        const enabledCurrencies = ref(enabledCurrenciesData.map(currency => ({
          id: currency.id,
          name: currency.name,
          code: currency.code,
          symbol_left: currency.symbol_left || '',
          symbol_right: currency.symbol_right || '',
          decimal_place: parseInt(currency.decimal_place) || 2,
          value: parseFloat(currency.value) || 1,
        })))
        
        const calculatorForm = reactive({
          fromCurrency: enabledCurrencies.value.length > 0 ? enabledCurrencies.value[0].code : '',
          amount: null,
        })
        
        const calculatedResults = ref([])

        const getCurrencySymbol = (code, position) => {
          const currency = enabledCurrencies.value.find(c => c.code === code)
          if (!currency) return ''
          return position === 'left' ? currency.symbol_left : currency.symbol_right
        }

        const formatAmount = (amount, decimalPlace) => {
          if (amount === null || amount === undefined || amount === '') return '0'
          return parseFloat(amount).toFixed(decimalPlace)
        }

        const calculateRates = () => {
          if (!calculatorForm.fromCurrency || calculatorForm.amount === null || calculatorForm.amount === '') {
            calculatedResults.value = []
            return
          }

          const fromCurrency = enabledCurrencies.value.find(c => c.code === calculatorForm.fromCurrency)
          if (!fromCurrency) {
            calculatedResults.value = []
            return
          }

          const amount = parseFloat(calculatorForm.amount)
          if (isNaN(amount) || amount <= 0) {
            calculatedResults.value = []
            return
          }

          // 计算其他货币的金额
          // 汇率计算：目标货币金额 = 源货币金额 * (目标货币汇率 / 源货币汇率)
          const results = enabledCurrencies.value
            .filter(c => c.code !== calculatorForm.fromCurrency)
            .map(currency => {
              const rate = currency.value / fromCurrency.value
              const convertedAmount = amount * rate
              return {
                code: currency.code,
                name: currency.name,
                symbol_left: currency.symbol_left,
                symbol_right: currency.symbol_right,
                decimal_place: currency.decimal_place,
                amount: convertedAmount,
                rate: rate.toFixed(6),
              }
            })
            .sort((a, b) => b.amount - a.amount) // 按金额降序排列

          calculatedResults.value = results
        }

        // 监听货币和金额变化
        watch(() => calculatorForm.fromCurrency, () => {
          calculateRates()
        })

        watch(() => calculatorForm.amount, () => {
          calculateRates()
        })

        const openCalculator = () => {
          calculatorDialogVisible.value = true
          // 设置默认货币为第一个启用的货币
          if (enabledCurrencies.value.length > 0 && !calculatorForm.fromCurrency) {
            calculatorForm.fromCurrency = enabledCurrencies.value[0].code
          }
        }

        const edit = (id) => {
          dialogVisible.value = true
          axios.get(`${api}/${id}`).then((res) => {
            Object.keys(res).forEach(key => form.hasOwnProperty(key) && (form[key] = res[key]));
            if (form.code) {
              form.code = form.code.toUpperCase()
            }
          })
        }

        const submit = () => {
          const url = form.id ? `${api}/${form.id}` : api
          const method = form.id ? 'put' : 'post'
          axios[method](url, form).then((res) => {
            dialogVisible.value = false
            inno.msg(res.message)
            window.location.reload()
          })
        }

        const close = () => {
          proxy.$refs.formRef.resetFields()
        }

        const create = () => {
          form.id = 0
          form.name = ''
          form.code = ''
          form.symbol_left = ''
          form.symbol_right = ''
          form.decimal_place = 2
          form.value = ''
          form.active = 1
          dialogVisible.value = true
        }
        const deleteForm = ref(null);
        const open = (code) => {
          ElMessageBox.confirm(
            '{{ __("common/base.hint_delete") }}',
            '{{ __("common/base.cancel") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
            }
          ).then(() => {
            axios.delete(`{{ panel_name() }}/currencies/${code}`).then((res) => {
              window.location.reload();
            }).catch(function (err) {
              inno.msg(err.response.data.message)
            });
          }).catch(function (err) {
            console.log(err)
          });
        };

        const exportFuns = {
          dialogVisible,
          form,
          edit,
          rules,
          close,
          submit,
          create,
          deleteForm,
          open,
          calculatorDialogVisible,
          enabledCurrencies,
          calculatorForm,
          calculatedResults,
          getCurrencySymbol,
          formatAmount,
          calculateRates,
          openCalculator,
        }

        window.app = exportFuns
        return exportFuns;
      }
    })

    listApp.use(ElementPlus);
    listApp.mount('#app');

    $(function () {
      $('.btn-add').click(function () {
        app.dialogVisible.value = true
      })
      
      // 汇率计算器按钮点击事件
      $('.currency-calculator-btn').click(function () {
        app.openCalculator()
      })
    })
  </script>
@endpush
