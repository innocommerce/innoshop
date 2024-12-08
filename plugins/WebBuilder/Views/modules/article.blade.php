@if(!empty($content['items']))
<section class="module-line">
    <div class="module-article">
        <div class="container">
            @if(!empty($content['title']))
                <div class="module-title-wrap">
                    <div class="module-title">{{ $content['title'][front_locale_code()] ?? '' }}</div>
                    @if(!empty($content['subtitle']))
                        <div class="module-sub-title">{{ $content['subtitle'][front_locale_code()] ?? '' }}</div>
                    @endif
                </div>
            @endif

            <div class="row gx-3 gx-lg-4">
                @foreach($content['items'] as $article)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('shared.blog', ['item' => $article])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
