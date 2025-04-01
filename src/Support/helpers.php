<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use LaravelLang\NativeLocaleNames\LocaleNames;
use Intervention\Image\ImageManagerStatic as Image;

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
     * @param number $width
     * @param number $height
     * @param number $quality
     *
     * @return string
     *
     * @throws \ErrorException
     */
    function thumbnail($originailPath, $width = 150, $height = null, $quality = 65)
    {
        if (preg_match('/assets\/images/', $originailPath)) {
            return $originailPath;
        }

        if (
            !preg_match('/public\//', $originailPath) && is_dir('./public/storage/uploads')
        ) {
            $originailPath = './public' . $originailPath;
        }
        if (!is_file($originailPath)) {
            if (preg_match('/^\\/assets/', $originailPath)) {
                $originailPath = './public/' . $originailPath;
            } else {
                $originailPath = './' . $originailPath;
            }
            $originailPath = preg_replace('/\\/{2,}/', '/', $originailPath);
            if (!is_file($originailPath)) {
                throw new \ErrorException('找不到圖片：' . $originailPath);
            }
        }
        $originailPath = preg_replace('/^\.\//', '', $originailPath);
        $matcher = [];
        preg_match('/(?<ext>\.\w{3,})$/', $originailPath, $matcher);
        $ext = $matcher['ext'];
        $size_text = '_w' . ($width ?? 'auto') . '_h_' . ($height ?? 'auto');

        $thumbPath = str_replace($ext, '_' . $size_text . '_thumb' . $ext, $originailPath);

        if (is_file($thumbPath)) {
            if (preg_match('/public\\//', $thumbPath)) {
                $thumbPath = str_replace('public/', '', $thumbPath);
            }
            $thumbPath = '/' . $thumbPath;

            return preg_replace('/\\/{2,}/', '/', $thumbPath);
        }
        $image = Image::make($originailPath);

        $resizeRate = ($width && $height) ? ($width / $height) : 0;
        // > 1 (width > height) < 1 (height > width)
        // 5d6135c538f001be9e3d377c32e0daa0.webp
        if (str_contains($originailPath, '5d6135c538f001be9e3d377c32e0daa0.webp')) {
        }
        if ($resizeRate > 0) {
            $originailRate = $image->getWidth() / $image->getHeight();
            if ($originailRate === $resizeRate && $image->getWidth() !== $width) {
                $image->resize($width, $height);
            } else {
                $originailRate = $image->getWidth() / $image->getHeight();
                $canvas = Image::canvas($width, $height);
                if ($originailRate > 1) {
                    $resizeHeight = (int) ($image->getHeight() / ($image->getWidth() / $width));
                    $image->resize($width, $resizeHeight);
                } else {
                    $resizeWidth = (int) ($image->getWidth() / ($image->getHeight() / $height));
                    $image->resize($resizeWidth, $height);
                }
                $canvas->insert($image, 'center');
                $image = $canvas;
            }
        } else {
            if ($image->width() <= $width || ($height && $image->height() < $height)) {
                if (preg_match('/public\\//', $originailPath)) {
                    $thumbPath = str_replace('public/', '', $originailPath);
                }
                $originailPath = '/' . $originailPath;

                return preg_replace('/\\/{2,}/', '/', $originailPath);
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

        $image->save($thumbPath, $quality);

        if (preg_match('/public\\//', $thumbPath)) {
            $thumbPath = str_replace('public/', '', $thumbPath);
        }
        $thumbPath = '/' . $thumbPath;

        return preg_replace('/\\/{2,}/', '/', $thumbPath);
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
