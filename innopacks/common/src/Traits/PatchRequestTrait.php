<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Traits;

trait PatchRequestTrait
{
    /**
     * Apply 'sometimes' to all rules for PATCH requests.
     * This allows partial updates where only provided fields are validated.
     *
     * @param  array  $rules
     * @return array
     */
    protected function applySometimesToRules(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            // Rules may be a pipe-delimited string or an array (e.g. when a
            // Closure rule such as rich_text_max_rule is used). Prepend
            // 'sometimes' in whichever form the rule is expressed.
            if (is_array($rule)) {
                array_unshift($rule, 'sometimes');
                $rules[$key] = $rule;
            } else {
                $rules[$key] = 'sometimes|'.$rule;
            }
        }

        return $rules;
    }
}
