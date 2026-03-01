{{-- 汇率计算器 Modal --}}
<el-dialog v-model="calculatorDialogVisible" :title="'{{ __('panel/currency.calculator') }}'" width="600px">
  <el-form label-position="top">
    <el-form-item :label="'{{ __('panel/currency.select_currency') }}'">
      <el-select v-model="calculatorForm.fromCurrency" placeholder="{{ __('panel/currency.select_currency') }}" style="width: 100%">
        <el-option
          v-for="currency in enabledCurrencies"
          :key="currency.code"
          :label="`${currency.name} (${currency.code.toUpperCase()})`"
          :value="currency.code">
        </el-option>
      </el-select>
    </el-form-item>
    
    <el-form-item :label="'{{ __('panel/currency.input_amount') }}'">
      <el-input
        v-model.number="calculatorForm.amount"
        type="number"
        :placeholder="'{{ __('panel/currency.input_amount') }}'"
        @input="calculateRates">
        <template #prepend>
          <span v-if="getCurrencySymbol(calculatorForm.fromCurrency, 'left')">@{{ getCurrencySymbol(calculatorForm.fromCurrency, 'left') }}</span>
        </template>
        <template #append>
          <span v-if="getCurrencySymbol(calculatorForm.fromCurrency, 'right')">@{{ getCurrencySymbol(calculatorForm.fromCurrency, 'right') }}</span>
        </template>
      </el-input>
    </el-form-item>

    <el-divider>{{ __('panel/currency.converted_results') }}</el-divider>

    <div v-if="calculatedResults.length > 0" class="calculated-results">
      <div
        v-for="result in calculatedResults"
        :key="result.code"
        class="result-item mb-3 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <strong>@{{ result.name }}</strong>
            <span class="text-muted ms-2">(@{{ result.code.toUpperCase() }})</span>
          </div>
          <div class="text-end">
            <div class="fs-5 fw-bold">
              <span v-if="result.symbol_left">@{{ result.symbol_left }}</span>
              @{{ formatAmount(result.amount, result.decimal_place) }}
              <span v-if="result.symbol_right">@{{ result.symbol_right }}</span>
            </div>
            <small class="text-muted">汇率: @{{ result.rate }}</small>
          </div>
        </div>
      </div>
    </div>
    <el-empty v-else :description="'{{ __('panel/currency.no_results') }}'" :image-size="80"></el-empty>
  </el-form>

  <template #footer>
    <div class="dialog-footer">
      <el-button @click="calculatorDialogVisible = false">{{ __('common/base.close') }}</el-button>
    </div>
  </template>
</el-dialog>
