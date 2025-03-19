<?php

namespace Plugin\LQuestionAnswer\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\BaseRepo;
use Plugin\LQuestionAnswer\Models\AskAnswer;
use Plugin\LQuestionAnswer\Models\AskAnswerAgree;
use Plugin\LQuestionAnswer\Resources\AskAnswerResource;
use Throwable;

class AskAnswerRepo extends BaseRepo
{
    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function ask_answers($request)
    {
        $ask_answers = AskAnswer::query()->where('product_id', $request->product_id)->where('parent_id', 0)->where(function ($query) {
            $query->where('status', 2)->orWhere('session_id', session()->getId());
        })->orderByDesc('created_at')->paginate();

        AskAnswerResource::collection($ask_answers);

        $ask_answers_count = $this->getAskAnswersCount($request->product_id);

        return [
            'ask_answers'       => $ask_answers,
            'ask_answers_count' => $ask_answers_count,
        ];
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function store($request)
    {
        if (! $request->product_id || $request->product_id <= 0) {
            throw new \Exception('商品ID不正确');
        }

        $product = Product::query()->where('id', $request->product_id)->first();
        if (! $product) {
            throw new \Exception('商品ID不正确');
        }

        $customer = current_customer() ?? token_customer();

        $setting = plugin_setting('l_question_answer');
        if (isset($setting['must_login']) && $setting['must_login'] == 1 && ! $customer) {
            throw new \Exception(trans('LQuestionAnswer::common.not_login'));
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

        return true;
    }

    public function agree(Request $request)
    {
        $customer = current_customer() ?? token_customer();
        if (! $customer) {
            throw new \Exception(trans('LQuestionAnswer::common.not_login'));
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

        return true;
    }

    public function getAskAnswersCount($product_id)
    {
        $key               = 'ask_answers_count_'.$product_id;
        $ask_answers_count = Cache::get($key);
        if ($ask_answers_count == null) {
            $ask_answers_count = AskAnswer::query()->where('product_id', $product_id)->where(function ($query) {
                $query->where('status', 2)->orWhere('session_id', session()->getId());
            })->count();
            Cache::put($key, $ask_answers_count, \Carbon\Carbon::now()->addSeconds(2));
        }

        return $ask_answers_count;
    }
}
