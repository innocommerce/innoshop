<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\OptionValue;

/**
 * 选项值仓库类
 */
class OptionValueRepo extends BaseRepo
{
    /**
     * 构建查询构造器（重写BaseRepo的builder方法）
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $query = OptionValue::query();

        return $this->buildOptionValueFilters($query, $filters);
    }

    /**
     * 获取选项值列表
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getOptionValueList(array $filters = []): LengthAwarePaginator
    {
        $query = OptionValue::query()->with(['option']);

        $query = $this->buildOptionValueFilters($query, $filters);

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('position')->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * 构建选项值查询过滤器
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    private function buildOptionValueFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['option_id'])) {
            $query->where('option_id', $filters['option_id']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (isset($filters['active'])) {
            $query->where('active', (bool) $filters['active']);
        }

        if (! empty($filters['price_type'])) {
            $query->where('price_type', $filters['price_type']);
        }

        return $query;
    }

    /**
     * 创建选项值
     *
     * @param  array  $data
     * @return OptionValue
     * @throws Exception
     */
    public function createOptionValue(array $data): OptionValue
    {
        try {
            $optionValue = new OptionValue;
            $optionValue->fill($data);
            $optionValue->save();

            return $optionValue;
        } catch (Exception $e) {
            throw new Exception('创建选项值失败: '.$e->getMessage());
        }
    }

    /**
     * 更新选项值
     *
     * @param  OptionValue  $optionValue
     * @param  array  $data
     * @return OptionValue
     * @throws Exception
     */
    public function updateOptionValue(OptionValue $optionValue, array $data): OptionValue
    {
        try {
            $optionValue->fill($data);
            $optionValue->save();

            return $optionValue;
        } catch (Exception $e) {
            throw new Exception('更新选项值失败: '.$e->getMessage());
        }
    }

    /**
     * 删除选项值
     *
     * @param  OptionValue  $optionValue
     * @return bool
     * @throws Exception
     */
    public function deleteOptionValue(OptionValue $optionValue): bool
    {
        try {
            return $optionValue->delete();
        } catch (Exception $e) {
            throw new Exception('删除选项值失败: '.$e->getMessage());
        }
    }

    /**
     * 根据选项ID获取选项值
     *
     * @param  int  $optionId
     * @param  bool  $activeOnly
     * @return Collection
     */
    public function getOptionValuesByOption(int $optionId, bool $activeOnly = true): Collection
    {
        $query = OptionValue::where('option_id', $optionId);

        if ($activeOnly) {
            $query->active();
        }

        return $query->ordered()->get();
    }

    /**
     * 批量更新选项值排序
     *
     * @param  array  $sortData  格式: [['id' => 1, 'position' => 10], ...]
     * @return bool
     */
    public function updateOptionValuesSort(array $sortData): bool
    {
        try {
            foreach ($sortData as $item) {
                OptionValue::where('id', $item['id'])
                    ->update(['position' => $item['position']]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 复制选项的选项值到另一个选项
     *
     * @param  int  $fromOptionId
     * @param  int  $toOptionId
     * @return bool
     */
    public function copyOptionValues(int $fromOptionId, int $toOptionId): bool
    {
        try {
            $optionValues = $this->getOptionValuesByOption($fromOptionId, false);

            foreach ($optionValues as $value) {
                $newValue            = $value->replicate();
                $newValue->option_id = $toOptionId;
                $newValue->save();
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取选项值统计信息
     *
     * @param  int  $optionId
     * @return array
     */
    public function getOptionValueStats(int $optionId): array
    {
        $totalValues  = OptionValue::where('option_id', $optionId)->count();
        $activeValues = OptionValue::where('option_id', $optionId)->active()->count();

        return [
            'total_values'  => $totalValues,
            'active_values' => $activeValues,
        ];
    }
}
