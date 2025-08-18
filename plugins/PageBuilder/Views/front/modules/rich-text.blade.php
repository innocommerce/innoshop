@if(!empty($content))
<section class="module-line">
    <div class="module-rich-text">
        <div class="{{ $content['width_class'] ?? 'container' }}">
            @if(!empty($content['title']))
                <div class="module-title-wrap text-center">
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
            @elseif(request()->get('design'))
                {{-- 设计模式下的空内容提示 --}}
                <div class="rich-text-empty">
                    <div class="empty-content">
                        <div class="empty-icon">
                            <i class="el-icon-edit-outline"></i>
                        </div>
                        <div class="empty-text">
                            <h4>暂无内容</h4>
                            <p>请在后台上传富文本内容</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@elseif(request()->get('design'))
{{-- 设计模式下的模块未配置提示 --}}
<div class="module-not-configured">
    <div class="not-configured-content">
        <div class="not-configured-icon">
            <i class="el-icon-warning-outline"></i>
        </div>
        <div class="not-configured-text">
            <h4>模块未配置</h4>
            <p>请在后台配置此富文本模块</p>
        </div>
    </div>
</div>
@endif
