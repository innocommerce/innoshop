@section('page-title-right')
    @php
        $hasDomainToken = (bool) system_setting('domain_token');
    @endphp
    @if($hasDomainToken)
        <button type="button" onclick="marketplaceAuthApp.show()" class="btn btn-outline-primary btn-sm">{{ __('panel/plugin.view_token') }}</button>
    @else
        <span class="text-danger mx-2 align-middle">{{ __('panel/plugin.need_bind_token') }}</span>
        <button type="button" onclick="marketplaceAuthApp.show()" class="btn btn-primary btn-sm">{{ __('panel/plugin.get_token') }}</button>
    @endif

    <div id="marketplaceAuthMount"></div>
@endsection

@push('footer')
<script>
(function() {
    var { createApp, ref, reactive, computed } = Vue;

    var i18n = {
        bind_token:         '{{ __("panel/plugin.bind_token") }}',
        has_token:          '{{ __("panel/plugin.has_token") }}',
        need_bind_token:    '{{ __("panel/plugin.need_bind_token") }}',
        auth_login:         '{{ __("panel/plugin.auth_login") }}',
        auth_register:      '{{ __("panel/plugin.auth_register") }}',
        auth_email:         '{{ __("panel/plugin.auth_email") }}',
        auth_password:      '{{ __("panel/plugin.auth_password") }}',
        auth_password_confirm: '{{ __("panel/plugin.auth_password_confirm") }}',
        auth_login_to_bind: '{{ __("panel/plugin.auth_login_to_bind") }}',
        auth_or_manual:     '{{ __("panel/plugin.auth_or_manual") }}',
        auth_input_token:   '{{ __("panel/plugin.input_token") }}',
        auth_bind:          '{{ __("panel/plugin.bind") }}',
        auth_bind_success:  '{{ __("panel/plugin.auth_bind_success") }}',
        auth_bind_fail:     '{{ __("panel/plugin.auth_bind_fail") }}',
        auth_bind_new_domain:'{{ __("panel/plugin.auth_bind_new_domain") }}',
        auth_switch_account:'{{ __("panel/plugin.auth_switch_account") }}',
        auth_logout:        '{{ __("panel/plugin.auth_logout") }}',
        auth_invalid_credentials: '{{ __("panel/plugin.auth_invalid_credentials") }}',
        auth_password_mismatch: '{{ __("panel/plugin.auth_password_mismatch") }}',
        auth_register_fail: '{{ __("panel/plugin.auth_register_fail") }}',
        auth_token_empty:   '{{ __("panel/plugin.token_empty") }}',
        auth_current_domain:'{{ __("panel/plugin.auth_current_domain") }}',
    };

    var apiUrl = '{{ config("innoshop.api_url") }}';
    var currentDomain = window.location.hostname;
    var initAuthToken = '{{ system_setting("marketplace_auth_token") }}';
    var initDomainToken = '{{ system_setting("domain_token") }}';
    var routeAuthToken = '{{ panel_route("marketplaces.auth_token") }}';
    var routeDomainToken = '{{ panel_route("marketplaces.domain_token") }}';
    var routeLogin = '{{ panel_route("marketplaces.login") }}';
    var routeRegister = '{{ panel_route("marketplaces.register") }}';
    var routeBindDomain = '{{ panel_route("marketplaces.bind_domain") }}';
    var routeAccount = '{{ panel_route("marketplaces.account") }}';

    var storedToken = ref(initAuthToken);

    function authHeaders(token) {
        return { 'Authorization': 'Bearer ' + (token || storedToken.value) };
    }
    function extractMsg(err) {
        return (err.response && err.response.data && err.response.data.message) ? err.response.data.message : (err.message || 'Unknown error');
    }

    var app = createApp({
        setup() {
            var dialogVisible = ref(false);
            var loading = ref(false);
            var authTab = ref('login');
            var errorMsg = ref('');

            var loginForm = reactive({ email: '', password: '' });
            var registerForm = reactive({ email: '', password: '', password_confirmation: '' });

            var loggedIn = ref(!!initAuthToken);
            var customer = reactive({ name: '', email: '' });
            var domainToken = ref(initDomainToken);
            var hasToken = computed(function() { return !!domainToken.value; });
            var manualToken = ref(initDomainToken || '');

            function doLogin() {
                errorMsg.value = '';
                if (!loginForm.email || !loginForm.password) { errorMsg.value = i18n.auth_invalid_credentials; return; }
                loading.value = true;
                axios.post(routeLogin, { email: loginForm.email, password: loginForm.password }).then(function(res) {
                    if (res && res.success && res.data && res.data.token) {
                        doBind(res.data.token);
                    } else {
                        loading.value = false;
                        errorMsg.value = (res && res.message) || i18n.auth_invalid_credentials;
                    }
                }).catch(function(err) { loading.value = false; errorMsg.value = extractMsg(err); });
            }

            function doRegister() {
                errorMsg.value = '';
                if (!registerForm.email || !registerForm.password) { errorMsg.value = i18n.auth_invalid_credentials; return; }
                if (registerForm.password !== registerForm.password_confirmation) { errorMsg.value = i18n.auth_password_mismatch; return; }
                loading.value = true;
                axios.post(routeRegister, { email: registerForm.email, password: registerForm.password, password_confirmation: registerForm.password_confirmation }).then(function(res) {
                    if (res && res.success && res.data && res.data.token) {
                        doBind(res.data.token);
                    } else {
                        loading.value = false;
                        errorMsg.value = (res && res.message) || i18n.auth_register_fail;
                    }
                }).catch(function(err) { loading.value = false; errorMsg.value = extractMsg(err); });
            }

            function doBind(sanctumToken) {
                loading.value = true;
                axios.post(routeBindDomain, { auth_token: sanctumToken, domain: currentDomain }).then(function(res) {
                    var tk = res.data && res.data.domain_token;
                    if (!tk) { loading.value = false; errorMsg.value = res.message || i18n.auth_bind_fail; return; }
                    axios.all([
                        axios.put(routeAuthToken, { auth_token: sanctumToken }),
                        axios.put(routeDomainToken, { domain_token: tk })
                    ]).then(function() {
                        ElementPlus.ElMessage.success(i18n.auth_bind_success);
                        setTimeout(function() { location.reload(); }, 800);
                    });
                }).catch(function(err) { loading.value = false; errorMsg.value = extractMsg(err); });
            }

            function doBindCurrent() {
                loading.value = true;
                errorMsg.value = '';
                axios.post(routeBindDomain, { domain: currentDomain }).then(function(res) {
                    var tk = res.data && res.data.domain_token;
                    if (tk) {
                        axios.put(routeDomainToken, { domain_token: tk }).then(function() {
                            domainToken.value = tk;
                            loading.value = false;
                            ElementPlus.ElMessage.success(i18n.auth_bind_success);
                        });
                    } else {
                        loading.value = false;
                        errorMsg.value = res.message || i18n.auth_bind_fail;
                    }
                }).catch(function(err) { loading.value = false; errorMsg.value = extractMsg(err); });
            }

            function loadMe() {
                axios.get(routeAccount).then(function(res) {
                    if (res && res.success) {
                        customer.name = res.data.name || '';
                        customer.email = res.data.email || '';
                    }
                }).catch(function() { loggedIn.value = false; storedToken.value = ''; });
            }

            function doLogout() {
                axios.put(routeAuthToken, { auth_token: '' }).then(function() { location.reload(); });
            }

            function switchAccount() {
                loggedIn.value = false;
                storedToken.value = '';
                errorMsg.value = '';
            }

            function manualBind() {
                if (!manualToken.value) { ElementPlus.ElMessage.warning(i18n.auth_token_empty); return; }
                axios.put(routeDomainToken, { domain_token: manualToken.value }).then(function() {
                    ElementPlus.ElMessage.success(i18n.auth_bind_success);
                    location.reload();
                }).catch(function(err) { ElementPlus.ElMessage.error(err.message); });
            }

            function show() {
                dialogVisible.value = true;
                errorMsg.value = '';
                if (loggedIn.value && storedToken.value) { loadMe(); }
            }

            return {
                dialogVisible, loading, authTab, errorMsg, i18n,
                loginForm, registerForm,
                loggedIn, customer, domainToken, currentDomain, hasToken, manualToken,
                doLogin, doRegister, doBindCurrent, doLogout, switchAccount, manualBind, show
            };
        },
        template: `
<el-dialog v-model="dialogVisible" :title="i18n.bind_token" width="520px" :close-on-click-modal="false" append-to-body>

    <template v-if="loggedIn">
        <div style="display:flex;align-items:center;padding:12px 16px;margin-bottom:16px;border-radius:8px;background:#f5f7fa;">
            <el-avatar :size="48" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:1.2rem;font-weight:600;flex-shrink:0;">
                @{{ customer.name ? customer.name.charAt(0).toUpperCase() : '?' }}
            </el-avatar>
            <div style="margin-left:12px;">
                <div style="font-weight:600;">@{{ customer.name || '-' }}</div>
                <div style="color:#999;font-size:0.85rem;">@{{ customer.email || '-' }}</div>
            </div>
        </div>

        <el-card shadow="never" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-weight:600;font-size:0.9rem;">@{{ i18n.auth_current_domain }}</span>
                <el-tag v-if="hasToken" type="success" size="small">@{{ i18n.has_token }}</el-tag>
                <el-tag v-else type="warning" size="small">@{{ i18n.need_bind_token }}</el-tag>
            </div>
            <div style="display:flex;align-items:center;">
                <code style="font-size:1rem;">@{{ currentDomain }}</code>
                <span v-if="hasToken" style="margin-left:auto;color:#999;font-size:0.8rem;">Token: @{{ domainToken.substring(0, 8) }}****</span>
            </div>
        </el-card>

        <div style="display:flex;flex-direction:column;gap:8px;">
            <el-button v-if="!hasToken" type="primary" size="small" :loading="loading" @click="doBindCurrent">@{{ i18n.auth_bind_new_domain }}</el-button>
            <el-button size="small" @click="switchAccount">@{{ i18n.auth_switch_account }}</el-button>
        </div>

        <el-divider />
        <div style="text-align:end;">
            <el-button type="danger" text size="small" @click="doLogout">@{{ i18n.auth_logout }}</el-button>
        </div>
    </template>

    <template v-else>
        <p style="color:#999;margin-bottom:12px;">@{{ i18n.auth_login_to_bind }}</p>

        <el-tabs v-model="authTab">
            <el-tab-pane :label="i18n.auth_login" name="login">
                <el-form @submit.prevent="doLogin" label-position="top">
                    <el-form-item :label="i18n.auth_email">
                        <el-input v-model="loginForm.email" autocomplete="email" />
                    </el-form-item>
                    <el-form-item :label="i18n.auth_password">
                        <el-input v-model="loginForm.password" type="password" show-password autocomplete="current-password" />
                    </el-form-item>
                    <el-button type="primary" style="width:100%;" :loading="loading" @click="doLogin">@{{ i18n.auth_login }}</el-button>
                </el-form>
            </el-tab-pane>

            <el-tab-pane :label="i18n.auth_register" name="register">
                <el-form @submit.prevent="doRegister" label-position="top">
                    <el-form-item :label="i18n.auth_email">
                        <el-input v-model="registerForm.email" autocomplete="email" />
                    </el-form-item>
                    <el-form-item :label="i18n.auth_password">
                        <el-input v-model="registerForm.password" type="password" show-password autocomplete="new-password" />
                    </el-form-item>
                    <el-form-item :label="i18n.auth_password_confirm">
                        <el-input v-model="registerForm.password_confirmation" type="password" show-password autocomplete="new-password" />
                    </el-form-item>
                    <el-button type="primary" style="width:100%;" :loading="loading" @click="doRegister">@{{ i18n.auth_register }}</el-button>
                </el-form>
            </el-tab-pane>
        </el-tabs>

        <el-alert v-if="errorMsg" :title="errorMsg" type="error" show-icon :closable="true" style="margin-top:12px;" @close="errorMsg=''" />

        <el-divider>@{{ i18n.auth_or_manual }}</el-divider>
        <div style="display:flex;gap:8px;">
            <el-input v-model="manualToken" :placeholder="i18n.auth_input_token" />
            <el-button @click="manualBind">@{{ i18n.auth_bind }}</el-button>
        </div>
    </template>

</el-dialog>
        `
    });

    app.use(ElementPlus);
    var vm = app.mount('#marketplaceAuthMount');
    window.marketplaceAuthApp = { show: function() { vm.show(); } };
})();
</script>
@endpush
