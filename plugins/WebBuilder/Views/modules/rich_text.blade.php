@if(!empty($content))
<section class="module-line">
    <div class="module-rich-text">
        <div class="container">
            @if(!empty($content['title']))
                <div class="module-title-wrap">
                    <div class="module-title">{{ $content['title'][front_locale_code()] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title">{{ $content['subtitle'][front_locale_code()] ?? '' }}</div>
                    @endif
                </div>
            @endif

            @if(!empty($content['content']) && !empty($content['content'][front_locale_code()]))
                <div class="rich-text-content">
                    {!! $content['content'][front_locale_code()] !!}
                </div>
            @else
                <!-- Debug info -->
                <div class="rich-text-content" style="color: #999;">
                    @if(empty($content['content']))
                        Content array is empty
                    @elseif(empty($content['content'][front_locale_code()]))
                        No content for locale: {{ front_locale_code() }}
                        Available locales: {{ implode(', ', array_keys($content['content'])) }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
@else
<!-- Debug info -->
<div style="color: #999;">
    Content is empty
</div>
@endif
