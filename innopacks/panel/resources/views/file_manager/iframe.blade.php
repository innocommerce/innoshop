@extends('panel::layouts.blank')

@section('title', __('panel/menu.file_manager'))

@push('footer')
<script>
    // 创建底部按钮的 Vue 实例
    new Vue({
        el: '#bottom-btns',
        methods: {
            handleConfirm() {
                // 获取主 Vue 实例并调用其方法
                const mainApp = document.querySelector('#app').__vue__;
                if (mainApp && typeof mainApp.confirmSelection === 'function') {
                    mainApp.confirmSelection();
                }
            }
        }
    });
</script>
@endpush

@include('panel::file_manager.main')

@section('page-bottom-btns')
    <div class="text-center" id="bottom-btns">
        <button class="btn btn-primary" @click="handleConfirm">选择提交</button>
    </div>
@endsection
