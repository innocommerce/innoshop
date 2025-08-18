@if(!empty($content['images']))
<section class="module-line">
    <div class="module-image-200">
        <div class="{{ $content['width_class'] ?? 'container' }}">
            @if(!empty($content['title']))
                <div class="module-title-wrap text-center">
                    <div class="module-title">{{ $content['title'][front_locale_code()] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title">{{ $content['subtitle'][front_locale_code()] ?? '' }}</div>
                    @endif
                </div>
            @endif

            <div class="row gx-3">
                @foreach($content['images'] as $image)
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="image-item">
                            <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}">
                                <img src="{{ $image['image'][front_locale_code()] ?? '' }}" class="img-fluid" alt="">
                                @if(!empty($image['text']))
                                    <div class="image-text">{{ $image['text'][front_locale_code()] ?? '' }}</div>
                                @endif
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
