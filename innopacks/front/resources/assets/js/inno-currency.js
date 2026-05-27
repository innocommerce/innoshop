// Currency formatting with global config
export function formatCurrency(amount, currencyConfig = null) {
  // Use global config if no specific config provided
  const currency = currencyConfig || (config && config.currency) || {
    symbol_left: '$',
    symbol_right: '',
    decimal_place: 2,
    rate: 1
  };

  const price = parseFloat(amount) * currency.rate;
  const formattedAmount = price.toFixed(currency.decimal_place);

  let result = '';
  if (currency.symbol_left) {
    result += currency.symbol_left;
  }
  result += formattedAmount;
  if (currency.symbol_right) {
    result += ' ' + currency.symbol_right;
  }

  return result;
}
