<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InnoShop\Common\Models\Admin;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Panel - Authentication')]
class AuthController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Admin login')]
    #[Unauthenticated]
    #[BodyParam('email', 'string', required: true, example: 'admin@example.com')]
    #[BodyParam('password', 'string', required: true, example: 'password')]
    public function login(Request $request): mixed
    {
        try {
            if (! Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
                throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
            }
            $admin = Auth::guard('admin')->user();
            if (! $admin instanceof Admin) {
                throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
            }
            $token = $admin->createToken('admin-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    #[Endpoint('Get current admin')]
    public function admin(Request $request): mixed
    {
        $user = $request->user();

        return read_json_success($user);
    }
}
