<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Cloak\Controllers;

use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\Cloak\Models\Cloak;
use Plugin\Cloak\Services\CloakService;

class CloakController extends BaseController
{
    protected CloakService $cloakService;

    public function __construct(CloakService $cloakService)
    {
        parent::__construct(); // 调用父类构造函数
        $this->cloakService = $cloakService;
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $status   = $request->input('status');
        $paginate = $request->input('paginate', 20);

        // 筛选参数
        $cloaks = $this->cloakService->getCloaksFiltered($status, $paginate);

        return view('Cloak::panel.index', compact('cloaks'));
    }

    /**
     * @return mixed
     */
    public function create(): mixed
    {
        $cloak = new Cloak;

        return view('Cloak::panel.form', compact(['cloak']));
    }

    /**
     * @param  int  $id
     * @return mixed
     */
    public function edit($id): mixed
    {
        $cloak = $this->cloakService->findById($id);

        if (! $cloak) {
            return redirect()->route('cloaks.index')->with('error', __('Cloak::panel.cloak_not_found'));
        }

        return view('Cloak::panel.form', compact(['cloak']));
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        $this->cloakService->createCloak($request);

        return redirect(panel_route('cloaks.index'))->with('success', __('Cloak::common.create_success'));
    }

    /**
     * @param  Cloak $cloak
     * @param  Request  $request
     * @return mixed
     */
    public function update(Cloak $cloak, Request $request): mixed
    {
        $this->cloakService->updateCloak($request, $cloak->id);

        return redirect(panel_route('cloaks.index'))->with('success', __('Cloak::common.update_success'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function destroy($id): mixed
    {
        $cloak = Cloak::findOrFail($id);
        $cloak->delete();

        return redirect(panel_route('cloaks.index'))->with('success', __('Cloak::common.delete_success'));
    }

    /**
     * Process the cloaking request
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function process(Request $request, int $id)
    {
        try {
            $cloak = $this->cloakService->findById($id);

            if (!$cloak || !$cloak->is_active) {
                return view('Cloak::front.error', [
                    'title' => __('Cloak::front.error_404'),
                    'message' => __('Cloak::front.error_404_message')
                ]);
            }

            // 添加测试模式 - 当URL中包含test=cloak参数时显示安全页面
            if ($request->has('test') && $request->input('test') === 'cloak') {
                // 测试模式 - 直接显示安全页面
                if (view()->exists('Cloak::front.safe')) {
                    return view('Cloak::front.safe', ['cloak' => $cloak]);
                } else if (!empty($cloak->safe_url)) {
                    return redirect($cloak->safe_url);
                }
            }

            // 判断是否应该显示安全页面
            if ($this->cloakService->shouldShowSafePage($cloak, $request)) {
                // 如果safe_url为空，则显示内置安全页面
                if (empty($cloak->safe_url) && view()->exists('Cloak::front.safe')) {
                    return view('Cloak::front.safe', ['cloak' => $cloak]);
                }
                // 否则重定向到指定的safe_url
                return redirect($cloak->safe_url);
            }

            // 否则，重定向到目标URL（营销页面）
            $cloak->incrementRedirects();

            // 处理一次性重定向
            if ($cloak->one_time_redirect) {
                $this->cloakService->setVisitedCookie($cloak->id);
            }

            return redirect($cloak->target_url);
        } catch (\Exception $e) {
            return view('Cloak::front.error', [
                'title' => __('Cloak::front.error_generic'),
                'message' => $e->getMessage()
            ]);
        }
    }
}
