<?php

namespace Plugin\Cloak\Repositories;

use InnoShop\Common\Repositories\BaseRepo;
use Plugin\Cloak\Models\Cloak;

class CloakRepo extends BaseRepo
{
    protected string $model = Cloak::class;

    private function handleData($data)
    {
        // Convert array form fields to JSON
        $jsonFields = ['ip_filters', 'country_filters', 'user_agent_filters', 'referrer_filters'];

        foreach ($jsonFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                // Filter out empty values
                $data[$field] = array_filter($data[$field], function ($value) {
                    return ! empty($value);
                });
            }
        }

        // Handle boolean fields
        $booleanFields = ['is_active', 'detect_bots', 'one_time_redirect'];
        foreach ($booleanFields as $field) {
            // 确保布尔字段即使未提交也有默认值false
            $data[$field] = isset($data[$field]) ? (bool) $data[$field] : false;
        }

        return $data;
    }

    /**
     * Find cloaks by filters
     *
     * @param  string|null  $status
     * @param  int  $paginate
     * @return mixed
     */
    public function findCloaksByFilters(?string $status, $paginate = 20): mixed
    {
        $query = Cloak::query();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($paginate);
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        $data = $this->handleData($data);

        return parent::create($data);
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $data = $this->handleData($data);

        return parent::update($item, $data);
    }
}
