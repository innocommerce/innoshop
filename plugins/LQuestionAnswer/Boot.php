<?php

namespace Plugin\LQuestionAnswer;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Plugin\LQuestionAnswer\Models\AskAnswer;
use Plugin\LQuestionAnswer\Resources\AskAnswerResource;

class Boot
{
    public function init(): void
    {

        //加入后台管理菜单
        listen_hook_filter('panel.component.sidebar.product.routes', function ($data) {
            $data[] = [
                'route' => 'ask_answer',
                'title' => '问答管理',
            ];

            return $data;
        });

        listen_blade_insert('product.detail.tab.link.after', function ($data) {

            $ask_answers_count         = $this->getAskAnswersCount($data['product']['id']);
            $data['ask_answers_count'] = $ask_answers_count;
            $view                      = view('LQuestionAnswer::front.tab_link', $data)->render();

            return $view;
        }, 1000096);

        listen_blade_insert('product.detail.tab.pane.after', function ($data) {

            $ask_answers = AskAnswer::query()->where('product_id', $data['product']['id'])->where('parent_id', 0)->where(function ($query) {
                $query->where('status', 2)->orWhere('session_id', session()->getId());
            })->orderByDesc('created_at')->paginate();

            AskAnswerResource::collection($ask_answers);
            $data['ask_answers'] = $ask_answers;
            $customer            = current_customer();
            $data['customer']    = $customer;

            $ask_answers_count         = $this->getAskAnswersCount($data['product']['id']);
            $data['ask_answers_count'] = $ask_answers_count;

            $data['can_add_ask_answer'] = true;
            $setting                    = plugin_setting('l_question_answer');
            if (isset($setting['must_login']) && $setting['must_login'] == 1 && ! $customer) {
                $data['can_add_ask_answer'] = false;
            }

            $view = view('LQuestionAnswer::front.tab_pane', $data)->render();

            return $view;
        }, 3000096);

    }

    private function getAskAnswersCount($product_id)
    {
        $key               = 'ask_answers_count_'.$product_id;
        $ask_answers_count = Cache::get($key);
        if ($ask_answers_count == null) {
            $ask_answers_count = AskAnswer::query()->where('product_id', $product_id)->where(function ($query) {
                $query->where('status', 2)->orWhere('session_id', session()->getId());
            })->count();
            Cache::put($key, $ask_answers_count, Carbon::now()->addSeconds(2));
        }

        return $ask_answers_count;
    }
}
