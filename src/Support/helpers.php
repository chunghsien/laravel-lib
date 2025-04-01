<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use LaravelLang\NativeLocaleNames\LocaleNames;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
if (! function_exists('getArrayResources')) {
    function getArrayResources($path)
    {
        return require_once $path;
    }
}

if (! function_exists('thumbnail')) {
    /**
     * @param string $originailPath
     * @param int $width
     * @param int $height
     * @param int $quality
     *
     * @return string
     *
     * @throws \ErrorException
     */
    function thumbnail($originailPath, $width = 150, $height = null, $quality = 65)
    {
        $originailPath = str_replace(public_path(), '', $originailPath);
        //$originailPath = preg_replace('/^\//', '', $originailPath);
        $systemPath =  public_path($originailPath);
        if (!is_file($systemPath)) {
            throw new \ErrorException('找不到圖片：' . $originailPath);
        }
        $matcher = [];
        preg_match('/(?<ext>\.\w{3,})$/', $originailPath, $matcher);
        $ext = $matcher['ext'];
        $allowExts = ['.jpg', '.jpeg', '.png'];
        if (false === array_search($ext, $allowExts)) {
            throw new \ErrorException('僅支援以下格式：' . implode(', ', $allowExts));
        }
        $size_text = '_w' . ($width ?? 'auto') . '_h_' . ($height ?? 'auto');

        $thumbPath = str_replace($ext, '_' . $size_text . '_thumb' . $ext, $originailPath);
        $thumbSavePath = public_path($thumbPath);
        if (is_file($thumbSavePath)) {
            return $thumbPath;
        }
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($systemPath);
        $resizeRate = (is_numeric($width) && is_numeric($height)) ? ($width / $height) : 0;
        if ($resizeRate > 0) {
            $originailRate = $image->width() / $image->height();
            if ($originailRate === $resizeRate && $image->width() !== $width) {
                $image->resize($width, $height);
            } else {
                $originailRate = $image->width() / $image->height();
                $canvas = $manager->create($width, $height);

                if ($originailRate > 1) {
                    $resizeHeight = (int) ($image->height() / ($image->width() / $width));
                    $image->resize($width, $resizeHeight);
                } else {
                    $resizeWidth = (int) ($image->width() / ($image->height() / $height));
                    $image->resize($resizeWidth, $height);
                }

                $canvas->place($image, 'center');
                $image = $canvas;
            }
        } else {

            if ($image->width() <= $width || ($height && $image->height() < $height)) {

                return $originailPath;
            }
            if (null === $width && $height > 0) {
                // auto width
                $image->resize(null, $height, static function ($constraint): void {
                    $constraint->aspectRatio();
                });
            }
            if (null === $height && $width > 0) {
                // auto width
                $image->resize($width, null, static function ($constraint): void {
                    $constraint->aspectRatio();
                });
            }

            if (is_int($width) && is_int($height)) {
                $image->resize($width, $height, static function ($constraint): void {
                    $constraint->aspectRatio();
                });
            }
        }
        $image->save($thumbSavePath, $quality);
        return $thumbPath;
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
