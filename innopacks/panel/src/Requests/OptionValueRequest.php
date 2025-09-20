<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptionValueRequest extends FormRequest
{
    /**
     * 确定用户是否有权限进行此请求
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取验证规则
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'option_id' => 'required|exists:options,id',
            'position'  => 'integer|min:0',
            'active'    => 'boolean',
            'image'     => 'nullable|string|max:255',
            'name'      => 'required|array',
            'name.*'    => 'required|string|max:255',
        ];
    }

    /**
     * 获取验证错误消息
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'option_id.required' => '选项不能为空',
            'option_id.exists'   => '选项不存在',
            'position.integer'   => '排序必须是整数',
            'position.min'       => '排序不能小于0',
            'active.boolean'     => '状态必须是布尔值',
            'image.string'       => '图片必须是字符串',
            'image.max'          => '图片路径不能超过255个字符',
            'name.required'      => '名称不能为空',
            'name.array'         => '名称必须是数组',
            'name.*.required'    => '名称不能为空',
            'name.*.string'      => '名称必须是字符串',
            'name.*.max'         => '名称不能超过255个字符',
        ];
    }
}
