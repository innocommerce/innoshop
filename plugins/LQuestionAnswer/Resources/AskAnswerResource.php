<?php

namespace Plugin\LQuestionAnswer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Plugin\LQuestionAnswer\Models\AskAnswerAgree;

class AskAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request): array
    {

        $productKey   = 'ask_answer_product_name_'.$this->product_id;
        $product_name = Cache::get($productKey);
        $product_img  = Cache::get($productKey.'_img');
        if (! $product_name) {
            $product = $this->product;
            if (! empty($product)) {
                $product_name = $product->translation->name;
                Cache::put($productKey, $product_name, Carbon::now()->addSeconds(5));

                $masterSku   = $product->masterSku;
                $product_img = $product->image ?: $masterSku->image;
                Cache::put($productKey.'_img', $product_img, Carbon::now()->addSeconds(5));
            } else {
                $product_name = '商品已被删除';
            }
        }

        $avatar = $this->avatar;
        if (! empty($this->customer_id) && ! empty($this->customer->avatar)) {
            $avatar = image_resize($this->customer->avatar, 50, 50);
            //Log::debug($avatar);
        } elseif (empty($avatar)) {
            $avatar = image_resize('', 50, 50);
        } else {
            $avatar = image_resize($avatar);
        }

        $agreeMap         = [];
        $current_customer = current_customer();
        $customer_id      = 0;
        if ($current_customer) {
            $customer_id = current_customer()->id;
            $key         = 'ask_answer_agree_logs_'.$this->product_id.'_'.$customer_id;
            $agreeMap    = Cache::get($key);
            if (empty($agreeMap)) {
                $agrees = AskAnswerAgree::query()->where('product_id', $this->product_id)->where('customer_id', $customer_id)->get([
                    'ask_answer_id',
                    'type',
                ]);
                $agreeMap = [];
                foreach ($agrees as $agree) {
                    $agreeMap[$customer_id.'-'.$agree->ask_answer_id] = $agree->type;
                }
                Cache::put($key, json_encode($agreeMap, true), Carbon::now()->addSeconds(3));
            } else {
                $agreeMap = json_decode($agreeMap, true);
            }
        }

        $data = [
            'type'              => $this->parent_id == 0 ? '问题' : '回复',
            'id'                => $this->id,
            'product_id'        => $this->product_id,
            'product_name'      => $product_name,
            'product_img'       => $product_img,
            'customer_id'       => $this->customer_id,
            'reply_id'          => $this->reply_id,
            'reply_user_name'   => empty($this->reply_id) ? '' : AskAnswerResource::make($this->reply)->user_name,
            'is_verified'       => empty($this->customer_id) ? 0 : 1,
            'user_name'         => empty($this->customer_id) ? $this->user_name : $this->customer->name,
            'user_avatar'       => $avatar,
            'content'           => $this->content,
            'agree'             => $this->agree,
            'not_agree'         => $this->not_agree,
            'current_agree'     => isset($agreeMap[$customer_id.'-'.$this->id]) && $agreeMap[$customer_id.'-'.$this->id] == 1 ? 1 : 0,
            'current_not_agree' => isset($agreeMap[$customer_id.'-'.$this->id]) && $agreeMap[$customer_id.'-'.$this->id] == 2 ? 1 : 0,
            'status'            => $this->status,
            'parent_id'         => $this->parent_id,
            'created_at'        => substr($this->created_at, 0, 19),
            'updated_at'        => substr($this->updated_at, 0, 19),
            'children'          => AskAnswerResource::collection($this->children),
        ];

        return $data;
    }
}
