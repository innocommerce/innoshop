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
use InnoShop\Common\Models\Customer\Transaction;
use InnoShop\Common\Repositories\Customer\TransactionRepo;
use InnoShop\Panel\Requests\TransactionRequest;
use Throwable;

class TransactionController extends BaseController
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
            'criteria'     => TransactionRepo::getCriteria(),
            'transactions' => TransactionRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::transactions.index', $data);
    }

    /**
     * Transaction creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Transaction);
    }

    /**
     * @param  TransactionRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        try {
            $data = $request->all();

            $transaction = TransactionRepo::getInstance()->create($data);

            return redirect(panel_route('transactions.index'))
                ->with('instance', $transaction)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('transactions.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Transaction  $transaction
     * @return mixed
     * @throws Exception
     */
    public function edit(Transaction $transaction): mixed
    {
        return $this->form($transaction);
    }

    /**
     * @param  Transaction  $transaction
     * @return mixed
     */
    public function form(Transaction $transaction): mixed
    {
        $data = [
            'types'       => TransactionRepo::getTypeOptions(),
            'transaction' => $transaction,
        ];

        return inno_view('panel::transactions.form', $data);
    }

    /**
     * @param  Transaction  $transaction
     * @return mixed
     */
    public function show(Transaction $transaction): mixed
    {
        $data = [
            'transaction' => $transaction,
        ];

        return inno_view('panel::transactions.show', $data);
    }

    /**
     * @param  TransactionRequest  $request
     * @param  Transaction  $transaction
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        try {
            $data = $request->all();
            TransactionRepo::getInstance()->update($transaction, $data);

            return redirect(panel_route('transactions.index'))
                ->with('instance', $transaction)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('transactions.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
