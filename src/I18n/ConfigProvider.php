<?php

declare(strict_types=1);

namespace Chopin\I18n;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

class ConfigProvider
{
    /**
     * Returns the configuration array.
     *
     * @return array
     */
    public function __invoke()
    {
        $configAffregator = new ConfigAggregator([
            new PhpFileProvider(\dirname(__DIR__).'/config/*.php'),
            new PhpFileProvider(\dirname(__DIR__).'/config/**/*.php'),
        ]);

        return $configAffregator->getMergedConfig();
    }
}
