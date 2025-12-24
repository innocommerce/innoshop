@php
  $hasImages = !empty($content['images']) && count($content['images']) > 0;
@endphp

@if($hasImages || request('design'))
<section class="module-line">
    <div class="module-four-image-plus">
        <div class="{{ $content['width_class'] ?? 'container' }}">
            @if(!empty($content['title']))
                <div class="module-title-wrap text-center mb-4">
                    <div class="module-title h3">{{ $content['title'][front_locale_code()] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title text-muted">{{ $content['subtitle'][front_locale_code()] ?? '' }}</div>
                    @endif
                </div>
            @endif

            @if($hasImages)
            <div class="row g-3">
                <div class="col-md-6">
                    @if(isset($content['images'][0]))
                        <div class="image-item h-100">
                            <a href="{{ $content['images'][0]['link']['link'] ?? 'javascript:void(0)' }}" class="d-block h-100">
                                <div class="image-wrap">
                                    <img src="{{ image_resize($content['images'][0]['image']) }}"
                                         style="object-fit: {{ $content['images'][0]['object_fit'] ?? 'cover' }}"
                                         alt="">
                                </div>
                                @if(!empty($content['images'][0]['text']))
                                    <div class="image-text">{{ $content['images'][0]['text'][front_locale_code()] ?? '' }}</div>
                                @endif
                                @if(!empty($content['images'][0]['sub_text']))
                                    <div class="image-sub-text">{{ $content['images'][0]['sub_text'][front_locale_code()] ?? '' }}</div>
                                @endif
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="right-images">
                        @for($i = 1; $i < 4; $i++)
                            @if(isset($content['images'][$i]))
                                <div class="image-item {{ $i < 3 ? 'small-image' : 'wide-image' }}">
                                    <a href="{{ $content['images'][$i]['link']['link'] ?? 'javascript:void(0)' }}" class="d-block h-100">
                                        <div class="image-wrap">
                                            <img src="{{ image_resize($content['images'][$i]['image']) }}"
                                                 style="object-fit: {{ $content['images'][$i]['object_fit'] ?? 'cover' }}"
                                                 alt="">
                                        </div>
                                        @if(!empty($content['images'][$i]['text']))
                                            <div class="image-text">{{ $content['images'][$i]['text'][front_locale_code()] ?? '' }}</div>
                                        @endif
                                        @if(!empty($content['images'][$i]['sub_text']))
                                            <div class="image-sub-text">{{ $content['images'][$i]['sub_text'][front_locale_code()] ?? '' }}</div>
                                        @endif
                                    </a>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
            @elseif(request('design'))
                @include('PageBuilder::front.partials.module-empty', [
                    'moduleClass' => 'four-image-plus',
                    'icon' => 'bi-layout-text-window-reverse',
                    'message' => __('PageBuilder::modules.no_images'),
                ])
            @endif
        </div>
    </div>
</section>

<style>
.module-four-image-plus {
    padding: 30px 0;
}
.module-four-image-plus .row {
    height: 420px;
}
.module-four-image-plus .image-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #f8f8f8;
    height: 100%;
}
.module-four-image-plus .image-wrap {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 100%;
}
.module-four-image-plus .image-wrap img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transition: transform 0.3s ease;
}
.module-four-image-plus .image-item:hover img {
    transform: scale(1.05);
}
.module-four-image-plus .image-text {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 30px;
    text-align: center;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 1;
    padding: 0 15px;
}
.module-four-image-plus .image-sub-text {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 10px;
    text-align: center;
    color: #fff;
    font-size: 0.875rem;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    z-index: 1;
    padding: 0 15px;
}
/* 右侧图片布局 */
.module-four-image-plus .right-images {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: calc(50% - 6px) calc(50% - 6px); /* 减去间距的一半 */
    gap: 12px;
    height: 100%;
}
.module-four-image-plus .right-images .small-image {
    height: 100%;
}
.module-four-image-plus .right-images .wide-image {
    grid-column: span 2;
    height: 100%;
}
</style>
@endif
