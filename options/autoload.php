<?php

namespace Chopin\Resources\Options;

function getResourcesData($filename): array
{
    return require_once __DIR__ . "/{$filename}";
}
