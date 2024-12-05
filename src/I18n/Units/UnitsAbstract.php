<?php

declare(strict_types=1);

namespace Chopin\I18n\Units;

use Laminas\Filter\Word\CamelCaseToUnderscore;

abstract class UnitsAbstract
{
    /**
     * @var int 四捨五入
     */
    public const ROUND_UP = 1;

    /**
     * @var int 無條件捨去
     */
    public const ROUND_DOWN = 2;
    protected $shortNames;
    protected $longNames;
    protected $defult;

    public function __construct(string $defaultUnit = '')
    {
        if ($defaultUnit) {
            $this->setDefault($defaultUnit);
        }
    }

    public function __call($name, $argments)
    {
        if (\count($argments) >= 1 && \count($argments) <= 4) {
            $name = str_replace('To', ',', $name);
            $nameArr = explode(',', $name);
            $use = $nameArr[0];
            $use = strtolower($use);
            $convertToName = (new CamelCaseToUnderscore())->filter($nameArr[1]);
            $convertToName = strtolower($convertToName);

            if (isset($this->{$use})) {
                // echo $convertToName.PHP_EOL;
                $values = $this->{$use};
                // $convertToName = str_replace('_', ' ', $convertToName);
                // var_export([$convertToName, $values]);

                if (isset($values[$convertToName])) {
                    if (\extension_loaded('bcmath')) {
                        $_decimal = $argments[2] ?? 0;
                        $value = bcdiv((float) $argments[0], (float) $values[$convertToName], $_decimal);
                    } else {
                        $value = $argments[0] / $values[$convertToName];
                    }

                    $unit = '';

                    if (\count($argments) >= 2 && ('short' === strtolower($argments[1]) || 'long' === strtolower($argments[1]))) {
                        $type = $argments[1];
                        $var = $type.'Names';
                        $showUnits = $this->{$var} ?? [];

                        if ($showUnits) {
                            $_convertToName = str_replace('_', ' ', $convertToName);
                            $index = array_search($_convertToName, $this->longNames, true);
                            if (false !== $index) {
                                $unit = $showUnits[$index];
                            }
                        }
                    }
                    if (\count($argments) >= 3) {
                        if (4 === \count($argments) && (int) $argments[3] > 0) {
                            $value = round($value, (int) $argments[2], (int) $argments[3]);
                        } else {
                            if (isset($argments[2]) && (int) $argments[2]) {
                                $value = number_format($value, (int) $argments[2]);
                            }
                        }
                    }

                    // $value = preg_replace('/0*$/', '', $value);
                    // $value = preg_replace('/\.$/', '', $value);
                    return $value.$unit;
                }
            }
        }

        return false;
    }

    public function getShortNameOptions()
    {
        $options = [];
        foreach ($this->shortNames as $shortName) {
            $options[] = ['label' => $shortName, 'value' => $shortName];
        }

        return $options;
    }

    public function getMixedNames($valueName = 'longNames', $labelName = 'shortNames')
    {
        $values = [];
        foreach ($this->shortNames as $k => $label) {
            $values[] = [
                'value' => 'longNames' === $valueName ? $this->longNames[$k] : $label,
                'label' => 'longNames' === $labelName ? $this->longNames[$k] : $label,
            ];
        }

        return $values;
    }

    public function getShortNames()
    {
        return $this->shortNames;
    }

    public function getLongNames()
    {
        return $this->longNames;
    }

    public function setDefault(string $unitName): void
    {
        $index = $this->getIndex($unitName);
        if (false !== $index) {
            $this->default = $this->longNames[$index];
        }
    }

    public function shortToLong(string $shortName): string
    {
        $shortName = strtolower($shortName);
        $index = array_search($shortName, $this->shortNames, true);
        if (false === $index) {
            return '';
        }

        return $this->longNames[$index];
    }

    public function longToShort(string $longName): string
    {
        $longName = strtolower($longName);
        $index = array_search($longName, $this->longNames, true);
        if (false === $index) {
            return '';
        }

        return $this->shortNames[$index];
    }

    /**
     * @param string $ConvertUnit 單位可寫長名稱或短名稱(ex.mm or millimeter)
     * @param string $type        是否要顯示單位名稱(short or long or '')
     * @param int    $decimals    顯示幾位數的小數點
     */
    public function defautlConvertToAny(string $ConvertUnit, int $value, string $type = '', int $decimals = 0, int $round = 0)
    {
        $index = array_search($ConvertUnit, $this->longNames, true);
        if (false === $index) {
            $index = array_search($ConvertUnit, $this->shortNames, true);
        }

        if (false === $index) {
            // 找不到要轉換的單位名稱
            return '';
        }

        $ConvertUnit = $this->longNames[$index];
        $methodName = $this->default.'To'.$ConvertUnit;
        $this->{$methodName}($value, $type, $decimals, $round);
    }

    protected function getIndex(string $unit): int
    {
        $index = array_search($unit, $this->shortNames, true);

        if (false === $index) {
            $index = array_search($unit, $this->longNames, true);
        }

        return $index;
    }
}
