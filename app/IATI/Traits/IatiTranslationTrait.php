<?php

declare(strict_types=1);

namespace App\IATI\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait IatiTranslationTrait
{
    public function loadTranslations(): array
    {
        $lang = App::getLocale();
        $cacheData = false;

        if (!$cacheData) {
            $cacheData = [];

            $folderPaths = File::directories(base_path("lang/{$lang}"));

            foreach ($folderPaths as $fl) {
                $folderName = basename($fl);
                $files = File::allFiles($fl);
                $translations = [];

                foreach ($files as $file) {
                    $fileName = pathinfo($file->getRealPath(), PATHINFO_FILENAME);
                    $fileTranslations = require $file->getRealPath();
                    $translations[$fileName] = $fileTranslations;
                }

                $cacheData[$folderName] = $translations;
            }

            $outerFiles = File::allFiles(base_path("lang/{$lang}"));
            $outerFileTranslations = [];

            foreach ($outerFiles as $outerFile) {
                $outerFileName = pathinfo($outerFile->getRealPath(), PATHINFO_FILENAME);
                $obtainedData = require $outerFile->getRealPath();
                $outerFileTranslations[$outerFileName] = $obtainedData;
            }

            $cacheData['general'] = $outerFileTranslations;
            //  TODO: Remove this
            //  Cache::put("translated_data_{$lang}", $cacheData, now()->addHours(24));
        }

        return $cacheData;
    }

    public function filterTranslations(array $cacheData, array $folders): array
    {
        $requiredTranslations = [];

        foreach ($folders as $folder) {
            if (array_key_exists($folder, $cacheData)) {
                $requiredTranslations[$folder] = Arr::get($cacheData, $folder, []);
            }
        }

        return $requiredTranslations;
    }

    public function getPageTranslationDependency(array $folders = ['activity_detail', 'activity_index', 'adminHeader', 'api', 'common', 'elements', 'footer', 'onboarding', 'organisationDetail', 'public', 'register', 'settings', 'user', 'userProfile', 'workflow_backend', 'workflow_frontend']): array
    {
        $cacheData = $this->loadTranslations();
        $translatedData = Arr::dot($this->filterTranslations($cacheData, $folders));
        $currentLanguage = App::getLocale();

        return [$translatedData, $currentLanguage];
    }
}
