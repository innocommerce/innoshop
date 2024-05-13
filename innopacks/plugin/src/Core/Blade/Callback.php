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

use Closure;

class Callback
{
    protected Closure $function;

    protected array $parameters = [];

    protected bool $run = true;

    /**
     * @param  Closure  $function
     * @param  array  $parameters
     */
    public function __construct(Closure $function, array $parameters = [])
    {
        $this->setCallback($function, $parameters);
    }

    /**
     * @param  $function
     * @param  $parameters
     * @return void
     */
    public function setCallback($function, $parameters): void
    {
        $this->function   = $function;
        $this->parameters = $parameters;
    }

    /**
     * @param  $parameters
     * @return mixed|void
     */
    public function call($parameters = null)
    {
        if ($this->run) {
            $this->run = false;

            return call_user_func_array($this->function, ($parameters ?: $this->parameters));
        }
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->run = true;
    }
}
