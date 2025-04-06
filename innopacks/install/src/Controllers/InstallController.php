<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use InnoShop\Install\Libraries\Checker;
use InnoShop\Install\Libraries\Creator;
use InnoShop\Install\Libraries\Environment\EnvironmentChecker;
use InnoShop\Install\Libraries\Installer;
use InnoShop\Install\Requests\CompleteRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class InstallController extends Controller
{
    private Installer $installer;

    private EnvironmentChecker $environmentChecker;

    public function __construct()
    {
        $this->installer          = new Installer;
        $this->environmentChecker = new EnvironmentChecker;
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        if (installed()) {
            return redirect(front_route('home.index'));
        }

        $locale = current_install_locale_code();
        App::setLocale($locale);

        $data = Checker::getInstance()->getEnvironment();

        $data['locale'] = $locale;

        return view('install::installer.index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function driverDetect(Request $request): mixed
    {
        $data           = Checker::getInstance()->getEnvironment();
        $locale         = current_install_locale_code();
        $data['locale'] = $locale;

        App::setLocale($locale);

        $dbCode = $request->get('db_code');
        if ($dbCode == 'mysql') {
            unset($data['extensions']['pdo_sqlite']);
            unset($data['extensions']['sqlite3']);
        } elseif ($dbCode == 'sqlite') {
            unset($data['extensions']['pdo_mysql']);
        }

        return view('install::installer._env_check', $data);
    }

    /**
     * @param  Request  $request
     * @return array
     */
    public function checkConnected(Request $request): array
    {
        $locale = current_install_locale_code();
        app()->setLocale($locale);

        return (new Checker)->checkConnection($request->all());
    }

    /**
     * @param  CompleteRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function complete(CompleteRequest $request): mixed
    {
        try {
            $locale = current_install_locale_code();
            app()->setLocale($locale);

            $data      = $request->all();
            $outputLog = Creator::getInstance()->setup($data)->getOutputLog();

            return json_success($outputLog->fetch());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function checkDatabase(Request $request)
    {
        $data = $request->validate([
            'db_type'     => 'required|in:mysql,sqlite',
            'db_hostname' => 'required_if:db_type,mysql',
            'db_port'     => 'required_if:db_type,mysql',
            'db_name'     => 'required_if:db_type,mysql',
            'db_username' => 'required_if:db_type,mysql',
            'db_password' => 'required_if:db_type,mysql',
            'db_path'     => 'required_if:db_type,sqlite',
        ]);

        $result = $this->installer->install($data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'errors'  => $result['errors'],
        ], 422);
    }
}
