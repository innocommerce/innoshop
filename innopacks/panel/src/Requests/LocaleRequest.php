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
use InnoShop\Common\Models\Locale;

class LocaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        // The {locale} route parameter may arrive as a bound Locale model or
        // as the raw string — both must resolve to the id being ignored by
        // the unique rule, and neither may fatal on ->id.
        $locale   = $this->route('locale');
        $localeId = $locale instanceof Locale ? $locale->id : $locale;
        if ($localeId) {
            $codeRule = 'required|alpha_dash|unique:locales,code,'.$localeId;
        } else {
            $codeRule = 'required|alpha_dash|unique:locales,code';
        }

        return [
            'name'     => 'required',
            'code'     => $codeRule,
            'image'    => 'required',
            'position' => 'integer',
            'active'   => 'bool',
        ];
    }
}
