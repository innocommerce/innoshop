<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use Exception;
use InnoShop\Common\Repositories\WeightClassRepo;

class Weight
{
    private static ?Weight $instance = null;

    private array $weightClasses = [];

    private string $baseUnit;

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): Weight
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor - load all weight classes and set base unit
     */
    public function __construct()
    {
        $this->loadWeightClasses();
        $this->baseUnit = system_setting('weight_class', 'kg');
    }

    /**
     * Load all active weight classes
     *
     * @return void
     */
    private function loadWeightClasses(): void
    {
        $classes = WeightClassRepo::getInstance()->enabledList();
        foreach ($classes as $class) {
            $this->weightClasses[$class->code] = $class;
        }
    }

    /**
     * Convert weight from one unit to another
     *
     * @param  float  $value  Weight value to convert
     * @param  string  $fromCode  Source weight unit code
     * @param  string  $toCode  Target weight unit code
     * @return float
     * @throws Exception
     */
    public function convert(float $value, string $fromCode, string $toCode): float
    {
        // If same unit, return original value
        if ($fromCode === $toCode) {
            return $value;
        }

        // Check if units exist
        if (! isset($this->weightClasses[$fromCode]) || ! isset($this->weightClasses[$toCode])) {
            throw new Exception('Invalid weight unit code');
        }

        // Convert to base unit first
        $baseValue = $value * ($this->weightClasses[$fromCode]->value / $this->weightClasses[$this->baseUnit]->value);

        // Convert from base unit to target unit
        return $baseValue * ($this->weightClasses[$this->baseUnit]->value / $this->weightClasses[$toCode]->value);
    }

    /**
     * Format weight with unit
     *
     * @param  float  $value  Weight value
     * @param  string  $code  Weight unit code
     * @return string
     * @throws Exception
     */
    public function format(float $value, string $code): string
    {
        if (! isset($this->weightClasses[$code])) {
            throw new Exception('Invalid weight unit code');
        }

        return $value.' '.$this->weightClasses[$code]->unit;
    }

    /**
     * Convert weight to system default unit
     *
     * @param  float  $value  Weight value
     * @param  string  $fromCode  Source weight unit code
     * @return float
     * @throws Exception
     */
    public function toDefault(float $value, string $fromCode): float
    {
        return $this->convert($value, $fromCode, $this->baseUnit);
    }

    /**
     * Get base unit code
     *
     * @return string
     */
    public function getBaseUnit(): string
    {
        return $this->baseUnit;
    }

    /**
     * Set base unit code
     *
     * @param  string  $code  Base unit code
     * @return self
     * @throws Exception
     */
    public function setBaseUnit(string $code): self
    {
        if (! isset($this->weightClasses[$code])) {
            throw new Exception('Invalid base unit code');
        }
        $this->baseUnit = $code;

        return $this;
    }
}
