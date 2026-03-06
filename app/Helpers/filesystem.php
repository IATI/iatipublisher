<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

if (!function_exists('awsHasFile')) {
    /**
     * Checks if file with $filepath is present in aws.
     *
     * @param $filePath
     *
     * @return bool
     */
    function awsHasFile($filePath): bool
    {
        return Storage::disk('s3')->exists($filePath);
    }
}

if (!function_exists('awsGetFile')) {
    /**
     * Gets the content of file from aws.
     *
     * @param $filePath
     *
     * @return string|null
     */
    function awsGetFile($filePath): ?string
    {
        if (awsHasFile($filePath)) {
            return Storage::disk('s3')->get($filePath);
        }

        return null;
    }
}

if (!function_exists('awsUploadFile')) {
    /**
     * Uploads file with $content to aws path $path.
     *
     * @param $path
     * @param $content
     *
     * @return bool
     */
    function awsUploadFile($path, $content): bool
    {
        return Storage::disk('s3')->put($path, $content);
    }
}

if (!function_exists('awsUploadFileAs')) {
    /**
     * Upload file with $content to aws path $path and name it as $filename.
     *
     * @param $path
     * @param $content
     * @param $filename
     *
     * @return bool
     */
    function awsUploadFileAs($path, $content, $filename): bool
    {
        return (bool) Storage::disk('s3')->putFileAs($path, $content, $filename);
    }
}

if (!function_exists('awsDeleteFile')) {
    /**
     * Deletes file at aws.
     *
     * @param $filePath
     *
     * @return bool
     */
    function awsDeleteFile($filePath): bool
    {
        if (Storage::disk('s3')->exists($filePath)) {
            return Storage::disk('s3')->delete($filePath);
        }

        return false;
    }
}

if (!function_exists('awsDeleteDirectory')) {
    /**
     * Deletes aws directory.
     *
     * @param $folderName
     *
     * @return bool
     */
    function awsDeleteDirectory($folderName): bool
    {
        if (Storage::disk('s3')->exists($folderName)) {
            return Storage::disk('s3')->deleteDirectory($folderName);
        }

        return false;
    }
}

if (!function_exists('awsUrl')) {
    /**
     * Creates aws url out of file path.
     *
     * @param $filePath
     *
     * @return string
     */
    function awsUrl($filePath): string
    {
        return Storage::disk('s3')->url($filePath);
    }
}

if (!function_exists('awsFilePath')) {
    /**
     * Returns full path for the file at aws $path.
     *
     * @param $path
     *
     * @return string
     */
    function awsFilePath($path): string
    {
        return Storage::disk('s3')->path($path);
    }
}

if (!function_exists('awsMoveFile')) {
    /**
     * Moves file.
     *
     * @param $from
     * @param $to
     *
     * @return bool
     */
    function awsMoveFile($from, $to): bool
    {
        return Storage::disk('s3')->move($from, $to);
    }
}

if (!function_exists('localHasFile')) {
    /**
     * Checks if $filePath exists for local disk.
     *
     * @param $filePath
     *
     * @return bool
     */
    function localHasFile($filePath): bool
    {
        return Storage::disk('local')->exists($filePath);
    }
}

if (!function_exists('localUploadFile')) {
    /**
     * Uploads file to local disk.
     *
     * @param $path
     * @param $content
     *
     * @return bool
     */
    function localUploadFile($path, $content): bool
    {
        return Storage::disk('local')->put($path, $content);
    }
}

if (!function_exists('localFilePath')) {
    /**
     * Gets full path for file at local disk.
     *
     * @param $path
     *
     * @return string
     */
    function localFilePath($path): string
    {
        return Storage::disk('local')->path($path);
    }
}

if (!function_exists('localDeleteFile')) {
    /**
     * Deletes file from local disk.
     *
     * @param $filePath
     *
     * @return bool
     */
    function localDeleteFile($filePath): bool
    {
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->delete($filePath);
        }

        return false;
    }
}

if (!function_exists('getFileNameExtension')) {
    /**
     * Get extension from filename.
     *
     * @param $fileName
     *
     * @return string|null
     */
    function getFileNameExtension($fileName): ?string
    {
        if (empty($fileName)) {
            return null;
        }

        $explodedFileName = explode('.', $fileName);

        return $explodedFileName[1] ?? null;
    }
}

if (!function_exists('awsDeleteOtherCasings')) {
    /**
     * Deletes any files with the same name but different casing on S3.
     * Useful for cleaning up legacy uppercase files when publishing lowercase ones.
     *
     * @param string $path The exact path being published.
     * @return void
     */
    function awsDeleteOtherCasings(string $path): void
    {
        $dir = dirname($path);
        $basename = basename($path);
        $files = Storage::disk('s3')->files($dir === '.' ? '' : $dir);

        foreach ($files as $file) {
            $fileBasename = basename($file);
            // If it's the same name case-insensitively but NOT the exact same casing
            if ($fileBasename !== strtolower($fileBasename)) {
                awsDeleteFile($file);
            }
        }
    }
}
