<li class="{{ equal_route_name(['front.account.quotes.index', 'front.account.quotes.show', 'front.account.quotes.number_show']) ? 'active' : '' }}">
  <a href="{{ account_route('quotes.index') }}"><i class="bi bi-coin"></i>{{ __('InquiryQuote::quote.quotes') }}</a>
</li>