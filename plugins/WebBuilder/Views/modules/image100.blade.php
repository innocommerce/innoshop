@if(!empty($content['images']))
<section class="module-line">
    <div class="module-image-100">
        <div class="container">
            @if(!empty($content['title']))
                <div class="module-title-wrap">
                    <div class="module-title">{{ $content['title'][front_locale_code()] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title">{{ $content['subtitle'][front_locale_code()] ?? '' }}</div>
                    @endif
                </div>
            @endif

            <div class="image-wrap">
                @foreach($content['images'] as $image)
                    <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}">
                        <img src="{{ $image['image'][front_locale_code()] ?? '' }}" class="img-fluid w-100" alt="">
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
