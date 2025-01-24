<?php

namespace App\Console\Commands;

use App\Exports\TranslationsPerFolderExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class GenerateTranslationExcel.
 */
class GenerateTranslationExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GenerateTranslationExcel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an Excel file with multiple sheets based on folder names, including existing translations.';

    private array $ignorableFilepath = [
        'label.php',
        'name.php',
    ];

    /**
     * @var array
     */
    protected array $languageDirectories = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $langPath = lang_path('');
            $this->languageDirectories = array_map(function ($directory) {
                return basename($directory);
            }, File::directories($langPath));

            // Check hash
            $currentHash = $this->generateTranslationHash($langPath);
            $lastHash = Cache::get('translations_hash');

            if ($currentHash !== $lastHash) {
                $translations = [];

                // Get translation strings
                foreach ($this->languageDirectories as $languageDirectory) {
                    $this->info("Processing language directory: {$languageDirectory}");
                    $this->extractTranslations($languageDirectory, $translations);
                    $this->info("Finished processing language directory: {$languageDirectory}");
                }

                // Generate and store the Excel file in S3
                $this->info('Generating Excel file');
                $excelFile = Excel::raw(new TranslationsPerFolderExport($translations), \Maatwebsite\Excel\Excel::XLSX);
                $timestamp = now()->format('Ymd_His');
                $fileName = "translations_{$timestamp}.xlsx";
                Storage::disk('s3')->put("Translations/{$fileName}", $excelFile);
                $this->manageS3Files('Translations');

                Cache::put('translations_hash', $currentHash);
                $this->info('Excel file generated and stored in AWS.');

                // Generating URL for download
                $url = Storage::disk('s3')->temporaryUrl("Translations/{$fileName}", now()->addHour());
                $this->info("Download URL: {$url}");
            } else {
                $this->info('No changes detected. Excel file not generated.');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            logger()->error($e);
        }
    }

    /**
     * Generate a hash based on the contents of all files in a directory.
     *
     * @param $langPath
     *
     * @return string
     */
    protected function generateTranslationHash($langPath): string
    {
        $files = File::allFiles($langPath);
        $hashes = [];

        foreach ($files as $file) {
            $hashes[] = md5($file->getContents());
        }

        return md5(implode('', $hashes));
    }

    /**
     * Extract translations from language files.
     *
     * @param $lang
     * @param $translations
     *
     * @return void
     */
    protected function extractTranslations($lang, &$translations): void
    {
        $path = lang_path($lang);

        foreach (File::directories($path) as $folder) {
            $folderName = basename($folder);
            $translations[$folderName] = $translations[$folderName] ?? [];

            foreach (File::allFiles($folder) as $file) {
                if ($this->shouldIgnoreThisFile($file)) {
                    continue;
                }

                $this->extractFileTranslations($file, $folderName, $lang, $translations[$folderName]);
            }
        }

        // For files directly under language folder
        foreach (File::files($path) as $file) {
            $translations['general'] = $translations['general'] ?? [];
            $this->extractFileTranslations($file, 'general', $lang, $translations['general']);
        }
    }

    /**
     * Extract translations from a file and add them to the translations array.
     *
     * @param $file
     * @param $folderName
     * @param $lang
     * @param $translations
     *
     * @return void
     */
    protected function extractFileTranslations($file, $folderName, $lang, &$translations): void
    {
        $filePath = $file->getPathname();
        $fileName = $file->getFilenameWithoutExtension();
        $array = include $filePath;

        $this->extractArrayKeys($array, '', $fileName, $folderName, $lang, $translations);
    }

    /**
     * Extract array keys recursively and add them to the translations array.
     *
     * @param  array  $array
     * @param $prefix
     * @param $fileName
     * @param $folderName
     * @param $lang
     * @param $translations
     *
     * @return void
     */
    protected function extractArrayKeys(array $array, $prefix, $fileName, $folderName, $lang, &$translations): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->extractArrayKeys($value, $prefix . $key . '.', $fileName, $folderName, $lang, $translations);
            } else {
                $translationKey = $folderName . '.' . $fileName . '.' . $prefix . $key;

                if (!isset($translations[$translationKey])) {
                    $translations[$translationKey] = [
                        'key'  => $prefix . $key,
                        'file' => $fileName,
                    ];

                    foreach ($this->languageDirectories as $languageDirectory) {
                        $translations[$translationKey][$languageDirectory] = '';
                    }
                }

                $translations[$translationKey][$lang] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
    }

    /**
     * Delete old files in the S3 Translations folder.
     *
     * @param $folder
     *
     * @return void
     */
    protected function manageS3Files($folder): void
    {
        $files = Storage::disk('s3')->files($folder);

        if (count($files) > 10) {
            // Sort files by last modified time, oldest first
            usort($files, static function ($a, $b) {
                return Storage::disk('s3')->lastModified($a) <=> Storage::disk('s3')->lastModified($b);
            });

            $filesToDelete = array_slice($files, 0, count($files) - 3);

            foreach ($filesToDelete as $file) {
                Storage::disk('s3')->delete($file);
            }
        }
    }

    private function shouldIgnoreThisFile(\Symfony\Component\Finder\SplFileInfo $file): bool
    {
        if (in_array($file->getRelativePathname(), $this->ignorableFilepath, true)) {
            return true;
        }

        return false;
    }
}
