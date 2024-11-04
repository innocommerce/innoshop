<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

interface RepoInterface
{
    public function list(array $filters = []);

    public function all(array $filters = []);

    public function detail(int $id);

    public function create($data);

    public function update(mixed $item, $data);

    public function destroy(mixed $item);

    public function builder(array $filters = []);
}
