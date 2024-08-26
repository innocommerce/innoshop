<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\State;
use InnoShop\Common\Repositories\StateRepo;
use InnoShop\Panel\Requests\StateRequest;
use Throwable;

class StateController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'states' => StateRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::states.index', $data);
    }

    /**
     * @param  State  $state
     * @return State
     */
    public function show(State $state)
    {
        return $state;
    }

    /**
     * State creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new State);
    }

    /**
     * @param  StateRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StateRequest $request): RedirectResponse
    {
        try {
            $data  = $request->all();
            $state = StateRepo::getInstance()->create($data);

            return redirect(panel_route('states.index'))
                ->with('instance', $state)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('states.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  State  $state
     * @return mixed
     * @throws Exception
     */
    public function edit(State $state): mixed
    {
        return $this->form($state);
    }

    /**
     * @param  State  $state
     * @return mixed
     */
    public function form(State $state): mixed
    {
        $data = [
            'state' => $state,
        ];

        return inno_view('panel::states.form', $data);
    }

    /**
     * @param  StateRequest  $request
     * @param  State  $state
     * @return RedirectResponse
     */
    public function update(StateRequest $request, State $state): RedirectResponse
    {
        try {
            $data = $request->all();
            StateRepo::getInstance()->update($state, $data);

            return redirect(panel_route('states.index'))
                ->with('instance', $state)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('states.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  State  $state
     * @return RedirectResponse
     */
    public function destroy(State $state): RedirectResponse
    {
        try {
            StateRepo::getInstance()->destroy($state);

            return redirect(panel_route('states.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('states.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
