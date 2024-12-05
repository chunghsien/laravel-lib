<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use LaravelLang\NativeLocaleNames\LocaleNames;

if (! function_exists('validLang')) {
    function validLang(string $lang, $ifFailThenDefault = false)
    {
        if (empty(LocaleNames::get()[$lang])) {
            if (!$ifFailThenDefault) {
                abort(404);
            }
            return env('APP_LOCALE');
        }
        return $lang;
    }
}

if (! function_exists('tryCatchTransToLog')) {

    /**
     * @param \Throwable $e
     * @param bool $die
     */
    function tryCatchTransToLog($e): void
    {
        $message = iconv('UTF-8', 'BIG5', $e->getMessage());
        $errorMessage = <<< ERROR_MESSAGE
        Code:            {$e->getCode()}
        File:            {$e->getFile()}
        Line:            {$e->getLine()}
        Message:         {$message}
        Previous:        {$e->getPrevious()}
        Trace as string:
        {$e->getTraceAsString()}
        ERROR_MESSAGE;
        Log::error($errorMessage);
    }
}
