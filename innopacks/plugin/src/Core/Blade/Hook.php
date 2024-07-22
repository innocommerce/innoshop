<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * Inspired by https://github.com/esemve/Hook
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Core\Blade;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Arr;

class Hook
{
    private static ?Hook $instance = null;

    protected array $watch = [];

    protected array $stop = [];

    protected array $mock = [];

    protected bool $isUpdate = false;

    protected bool $withCallback = false;

    protected bool $testing = false;

    /**
     * Get singleton instance.
     *
     * @return Hook
     */
    public static function getSingleton(): Hook
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Return the hook answer.
     *
     * @param  string  $hook  Hook name
     * @param  array  $params
     * @param  callable|null  $callback
     * @param  string  $htmlContent  content wrapped by hook
     *
     * @return null|void
     */
    public function get(string $hook, array $params = [], ?callable $callback = null, string $htmlContent = '')
    {
        $callbackObject = $this->createCallbackObject($callback, $params);

        $output = $this->returnMockIfDebugModeAndMockExists($hook);
        if ($output) {
            return $output;
        }

        $output = $this->run($hook, $params, $callbackObject, $htmlContent);

        if (! $output) {
            $output = $callbackObject->call();
        }

        unset($callbackObject);

        return $output;
    }

    /**
     * @param  string  $hook
     * @param  array  $params
     * @param  callable|null  $callback
     * @param  string  $htmlContent
     * @return string|null
     */
    public function getHook(string $hook, array $params = [], ?callable $callback = null, string $htmlContent = ''): ?string
    {
        if (config('app.debug') && has_debugbar()) {
            Debugbar::log("HOOK === @hookinsert: $hook");
        }

        $this->isUpdate = false;

        return $this->get($hook, $params, $callback, $htmlContent);
    }

    /**
     * @param  string  $hook
     * @param  array  $params
     * @param  callable|null  $callback
     * @param  string  $htmlContent
     * @return string|null
     */
    public function getWrapper(string $hook, array $params = [], ?callable $callback = null, string $htmlContent = ''): ?string
    {
        if (config('app.debug') && has_debugbar()) {
            Debugbar::log("HOOK === @hookupdate: $hook");
        }

        $this->isUpdate = true;

        return $this->get($hook, $params, $callback, $htmlContent);
    }

    /**
     * Stop all another hook running.
     *
     * @param  string  $hook  Hook name
     */
    public function stop(string $hook): void
    {
        $this->stop[$hook] = true;
    }

    /**
     * Subscribe to hook.
     *
     * @param  string  $hook  Hook name
     * @param  $priority
     * @param  $function
     */
    public function listen(string $hook, $function, $priority = null): void
    {
        $caller = debug_backtrace(0, 3)[2];

        if (in_array(Arr::get($caller, 'function'), ['include', 'require'])) {
            $caller = debug_backtrace(0, 4)[3];
        }

        if (empty($this->watch[$hook])) {
            $this->watch[$hook] = [];
        }

        if (! is_numeric($priority)) {
            $priority = null;
        }

        if (isset($this->watch[$hook][$priority])) {
            $priority++;
        }

        $this->watch[$hook][$priority] = [
            'function' => $function,
            'caller'   => [
                'file'  => $caller['file'],
                'line'  => $caller['line'],
                'class' => Arr::get($caller, 'class'),
            ],
        ];

        ksort($this->watch[$hook]);
    }

    /**
     * Return all registered hooks.
     *
     * @return array
     */
    public function getHooks(): array
    {
        $hookNames = (array_keys($this->watch));
        ksort($hookNames);

        return $hookNames;
    }

    /**
     * Return all listeners for hook.
     *
     * @param  string  $hook
     *
     * @return array
     */
    public function getEvents(string $hook): array
    {
        $output = [];

        foreach ($this->watch[$hook] as $key => $value) {
            $output[$key] = $value['caller'];
        }

        return $output;
    }

    /**
     * For testing.
     *
     * @param  string  $name  Hook name
     * @param  mixed  $return  Answer
     */
    public function mock(string $name, mixed $return): void
    {
        $this->testing     = true;
        $this->mock[$name] = ['return' => $return];
    }

    /**
     * Return the mock value.
     *
     * @param  string  $hook  Hook name
     */
    protected function returnMockIfDebugModeAndMockExists(string $hook)
    {
        if ($this->testing) {
            if (array_key_exists($hook, $this->mock)) {
                $output = $this->mock[$hook]['return'];
                unset($this->mock[$hook]);

                return $output;
            }
        }

        return '';
    }

    /**
     * Return a new callback object.
     *
     * @param  callable  $callback  function
     * @param  array  $params  parameters
     *
     * @return Callback
     */
    protected function createCallbackObject(callable $callback, array $params): Callback
    {
        return new Callback($callback, $params);
    }

    /**
     * Run hook events.
     *
     * @param  string  $hook  Hook name
     * @param  array  $params  Parameters
     * @param  Callback  $callback  Callback object
     * @param  string|null  $output  html wrapped by hook
     *
     * @return mixed
     */
    protected function run(string $hook, array $params, Callback $callback, ?string $output = null): mixed
    {
        if ($this->isUpdate) {
            array_unshift($params, $output);
        }

        if ($this->withCallback) {
            array_unshift($params, $callback);
        }

        if (array_key_exists($hook, $this->watch)) {
            if (is_array($this->watch[$hook])) {
                foreach ($this->watch[$hook] as $function) {
                    if (! empty($this->stop[$hook])) {
                        unset($this->stop[$hook]);

                        break;
                    }

                    $output = call_user_func_array($function['function'], $params);

                    $params[1] = $output;
                }
            }
        }

        return $output;
    }

    /**
     * Return the listeners.
     *
     * @return array
     */
    public function getListeners(): array
    {
        return $this->watch;
    }
}
