<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Front - Home')]
class HomeController extends BaseController
{
    /**
     * @return string
     */
    #[Endpoint('API base info')]
    #[Unauthenticated]
    public function base(): string
    {
        return 'This is Frontend Restful APIs for '.innoshop_version();
    }
}
