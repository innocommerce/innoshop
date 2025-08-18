@if(!empty($content['images']))
<section class="module-line">
    <div class="module-single-image">
        <div class="{{ $content['width_class'] ?? 'container' }}">
            @if(!empty($content['title']))
                <div class="module-title-wrap text-center">
                    <div class="module-title">{{ $content['title'] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title">{{ $content['subtitle'] ?? '' }}</div>
                    @endif
                </div>
            @endif

            <div class="image-wrap">
                @foreach($content['images'] as $image)
                    <a href="{{ $image['link']['link'] ?? 'javascript:void(0)' }}">
                        <img src="{{ $image['image'] ?? '' }}" class="img-fluid w-100" alt="">
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
