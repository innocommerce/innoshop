<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ViewTracker\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Repositories\BaseRepo;
use Plugin\ViewTracker\Libraries\GeoIP;
use Plugin\ViewTracker\Models\ViewLog;
use Throwable;

class ViewLogRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'url', 'type' => 'input', 'label' => '访问地址'],
            ['name' => 'user_agent', 'type' => 'input', 'label' => '浏览器信息'],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => '初次访问'],
            ['name' => 'updated_at', 'type' => 'date_range', 'label' => '最后访问'],
        ];
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public static function parseRequest(Request $request): array
    {
        return [
            'customer_id' => current_customer_id(),
            'client_ip'   => $request->ip() ?: '',
            'referer'     => $request->header('referer', ''),
            'language'    => $request->header('accept-language'),
            'user_agent'  => $request->userAgent(),
            'page_url'    => $request->url(),
            'method'      => $request->method(),
        ];
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = ViewLog::query();

        $clientIP = $filters['client_ip'] ?? '';
        if ($clientIP) {
            $builder->where('client_ip', $clientIP);
        }

        $version = $filters['version'] ?? '';
        if ($version) {
            $builder->where('version', $version);
        }

        $buildDate = $filters['build_date'] ?? '';
        if ($buildDate) {
            $builder->where('build_date', $buildDate);
        }

        $url = $filters['url'] ?? '';
        if ($url) {
            $builder->where('url', 'like', "%$url%");
        }

        $userAgent = $filters['user_agent'] ?? '';
        if ($userAgent) {
            $builder->where('user_agent', 'like', "%$userAgent%");
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<=', $createdEnd);
        }

        $updatedStart = $filters['updated_at_start'] ?? '';
        if ($updatedStart) {
            $builder->where('updated_at', '>', $updatedStart);
        }

        $updatedEnd = $filters['updated_at_end'] ?? '';
        if ($updatedEnd) {
            $builder->where('updated_at', '<=', $updatedEnd);
        }

        return $builder;
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $data = $this->handleData($data);

        $viewLog = new ViewLog;
        $viewLog->fill($data);
        $viewLog->saveOrFail();

        return $viewLog;
    }

    /**
     * @param  $requestData
     * @return array
     * @throws Exception
     */
    private function handleData($requestData): array
    {
        try {
            $geoIP = GeoIP::getInstance($requestData['client_ip']);
            if (empty($requestData['country'] ?? '')) {
                $requestData['country'] = $geoIP->getCountry();
            }

            if (empty($requestData['city'] ?? '')) {
                $requestData['city'] = $geoIP->getCity();
            }
        } catch (Exception $e) {
            $requestData['country'] = '';
            $requestData['city']    = '';
        }

        $data = [
            'customer_id' => $requestData['customer_id'] ?? 0,
            'client_ip'   => $requestData['client_ip']   ?? '',
            'language'    => $requestData['language']    ?? '',
            'country'     => $requestData['country']     ?? '',
            'city'        => $requestData['city']        ?? '',
            'referer'     => $requestData['referer']     ?? '',
            'page_url'    => $requestData['page_url']    ?? '',
            'method'      => $requestData['method']      ?? '',
            'status_code' => $requestData['status_code'] ?? '',
            'user_agent'  => $requestData['user_agent']  ?? '',
        ];
        Log::info('ViewLogRepo::handleData');
        Log::info(json_encode($data));

        return $requestData;
    }
}
