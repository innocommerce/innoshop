<?php

namespace Plugin\LQuestionAnswer\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use InnoShop\Common\Models\Product;
use Plugin\LQuestionAnswer\Models\AskAnswer;
use Plugin\LQuestionAnswer\Models\AskAnswerAgree;
use Plugin\LQuestionAnswer\Resources\AskAnswerResource;

class ShopController
{
    public function ask_answer(Request $request)
    {
        $ask_answers = AskAnswer::query()->where('product_id', $request->product_id)->where('parent_id', 0)->where(function ($query) {
            $query->where('status', 2)->orWhere('session_id', session()->getId());
        })->orderByDesc('created_at')->paginate();

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

        $customer = current_customer();

        $setting = plugin_setting('l_question_answer');
        if (isset($setting['must_login']) && $setting['must_login'] == 1 && ! $customer) {
            return response()->json([
                'code' => -1,
                'msg'  => trans('LQuestionAnswer::common.not_login'),
            ]);
        }

        AskAnswer::query()->insert([
            'customer_id' => $customer ? $customer->id : 0,
            'product_id'  => $request->product_id,
            'user_name'   => $request->user_name,
            'content'     => $request->content_text,
            'agree'       => 0,
            'not_agree'   => 0,
            'parent_id'   => $request->parent_id,
            'reply_id'    => $request->reply_id,
            'session_id'  => session()->getId(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        return response()->json([
            'code' => 0,
            'msg'  => 'Success',
        ]);
    }

    public function agree(Request $request)
    {

        $customer = current_customer();
        if (! $customer) {
            return response()->json([
                'code' => -1,
                'msg'  => trans('LQuestionAnswer::common.not_login'),
            ]);
        }
        $type      = $request->type;
        $askAnswer = AskAnswer::query()->where('id', $request->id)->first();

        if ($type == 1) {//现在是赞
            $agree = AskAnswerAgree::query()->where('customer_id', $customer->id)->where('ask_answer_id', $request->id)->first();
            if ($agree) {
                if ($agree->type == 2) {//之前是踩，那踩要取消数量1，赞要加1,并把记录改成赞
                    AskAnswer::query()->where('id', $request->id)->decrement('not_agree');
                    AskAnswer::query()->where('id', $request->id)->increment('agree');
                    AskAnswerAgree::query()->where('id', $agree->id)->update(['type' => $type]);
                } else {//之前是赞，那现在就是取消操作
                    AskAnswer::query()->where('id', $request->id)->decrement('agree');
                    AskAnswerAgree::query()->where('id', $agree->id)->delete();
                }
            } else {//不存在记录,则加1,并记录
                AskAnswer::query()->where('id', $request->id)->increment('agree');
                AskAnswerAgree::query()->insert([
                    'product_id'    => $askAnswer->product_id,
                    'ask_answer_id' => $request->id,
                    'customer_id'   => $customer->id,
                    'type'          => $type,
                ]);
            }
        } elseif ($type == 2) {//现在是踩
            $agree = AskAnswerAgree::query()->where('customer_id', $customer->id)->where('ask_answer_id', $request->id)->first();
            if ($agree) {
                if ($agree->type == 1) {//之前是赞，那赞要取消数量1，踩要加1,并把记录改成踩
                    AskAnswer::query()->where('id', $request->id)->decrement('agree');
                    AskAnswer::query()->where('id', $request->id)->increment('not_agree');
                    AskAnswerAgree::query()->where('id', $agree->id)->update(['type' => $type]);
                } else {//之前是踩，那现在就是取消操作
                    AskAnswer::query()->where('id', $request->id)->decrement('not_agree');
                    AskAnswerAgree::query()->where('id', $agree->id)->delete();
                }
            } else {//不存在记录,则加1,并记录
                AskAnswer::query()->where('id', $request->id)->increment('not_agree');
                AskAnswerAgree::query()->insert([
                    'product_id'    => $askAnswer->product_id,
                    'ask_answer_id' => $request->id,
                    'customer_id'   => $customer->id,
                    'type'          => $type,
                ]);
            }
        }

        return response()->json([
            'code' => 0,
            'msg'  => 'Success',
        ]);

    }
}
