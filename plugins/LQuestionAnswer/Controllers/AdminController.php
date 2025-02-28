<?php

namespace Plugin\LQuestionAnswer\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Product;
use Matrix\Exception;
use Plugin\LQuestionAnswer\Models\AskAnswer;
use Plugin\LQuestionAnswer\Resources\AskAnswerResource;

class AdminController
{
    public function index(Request $request)
    {

        $plugin = app('plugin')->getPlugin('l_question_answer');
        $data   = [
            'name'        => '问答管理',
            'description' => $plugin->getLocaleDescription(),
        ];

        // print_r(json_encode($request->route()));exit;

        return view('LQuestionAnswer::panel.index', $data);
    }

    public function ask_answer(Request $request)
    {
        $ask_answers = AskAnswer::query()->where('parent_id', 0)->with(['children'])->orderByDesc('id');

        $ask_answers = $ask_answers->paginate();
        AskAnswerResource::collection($ask_answers);

        return response()->json([
            'code'        => 0,
            'ask_answers' => $ask_answers,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->product_id || $request->product_id <= 0) {
            return response()->json([
                'code' => -1,
                'msg'  => '商品ID不正确',
            ]);
        }

        $product = Product::query()->where('id', $request->product_id)->first();
        if (! $product) {
            return response()->json([
                'code' => -1,
                'msg'  => '商品ID不正确!',
            ]);
        }

        $id = $request->id;
        if ($id) {
            AskAnswer::query()->where('id', $id)->update([
                'product_id' => $request->product_id,
                'user_name'  => $request->user_name,
                'content'    => $request->content_text,
                'agree'      => $request->agree,
                'not_agree'  => $request->not_agree,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            AskAnswer::query()->insert([
                'product_id' => $request->product_id,
                'user_name'  => $request->user_name,
                'reply_id'   => $request->reply_id,
                'content'    => $request->content_text,
                'agree'      => $request->agree,
                'not_agree'  => $request->not_agree,
                'status'     => 2,
                'parent_id'  => $request->parent_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'code' => 0,
            'msg'  => trans('common.success'),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $productsAskAnswer = AskAnswer::query()->where('id', $request->id)->first();
        if ($productsAskAnswer) {
            if ($productsAskAnswer->status == $request->status) {
                return response()->json([
                    'code' => -1,
                    'msg'  => '状态无改变',
                ]);
            }
            try {
                $productsAskAnswer->status = $request->status;
                $productsAskAnswer->update();

                return response()->json([
                    'code' => 0,
                    'msg'  => trans('common.success'),
                ]);
            } catch (Exception $exception) {
                DB::rollBack();

                return response()->json([
                    'code' => -1,
                    'msg'  => '审批异常',
                ]);
            }
        }

        return response()->json([
            'code' => -1,
            'msg'  => '问答不存在',
        ]);
    }

    public function destory(Request $request)
    {
        AskAnswer::query()->where('id', $request->id)->delete();

        return response()->json([
            'code' => 0,
            'msg'  => trans('common.success'),
        ]);
    }
}
