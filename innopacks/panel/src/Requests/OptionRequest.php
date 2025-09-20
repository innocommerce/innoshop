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

class OptionRequest extends FormRequest
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
            'type'                => 'required|in:select,radio,checkbox,text,textarea',
            'position'            => 'integer|min:0',
            'active'              => 'boolean',
            'translations'        => 'required|array',
            'translations.*.name' => 'required|string|max:255',
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
            'type.required'                => '选项类型不能为空',
            'type.in'                      => '选项类型必须是：select, radio, checkbox 中的一种',
            'position.integer'             => '排序必须是整数',
            'position.min'                 => '排序不能小于0',
            'active.boolean'               => '状态必须是布尔值',
            'translations.required'        => '翻译信息不能为空',
            'translations.array'           => '翻译信息必须是数组',
            'translations.*.name.required' => '选项组名称不能为空',
            'translations.*.name.string'   => '选项组名称必须是字符串',
            'translations.*.name.max'      => '选项组名称不能超过255个字符',
        ];
    }
}
