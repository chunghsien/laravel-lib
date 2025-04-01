<?php

declare(strict_types=1);

namespace Chopin\I18n;

use App\Models\LanguageLocale as ModelsLanguageLocale;

abstract class LanguageLocale
{
    /**
     * @return ModelsLanguageLocale
     */
    static public function currentUse()
    {
        if (!request('lang')) {
            throw new \ErrorException('極有可能不是在web-server 狀態');
        }
        $defaultHtmlLang = str_replace('_', '-', env('APP_LOCALE'));
        $languageLocale = ModelsLanguageLocale::where('html_code', '=', request('lang'))
            ->where('is_use', '=', 1)->first();
        if (!$languageLocale) {
            $languageLocale =  ModelsLanguageLocale::where('html_code', '=', $defaultHtmlLang)->first();
        }
        return $languageLocale;
    }
}
