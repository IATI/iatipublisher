<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ElementJsonTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ElementJsonTranslation';
    /**
     * @var string
     */
    protected $description = 'Read json and make translation file';

    private array $files = [
        'activity',
        'organisation',
    ];

    private array $langFilePathMappedToFileType = [];
    private array $jsonFilePathMappedToFileType = [];

    public function __construct()
    {
        parent::__construct();

        $this->langFilePathMappedToFileType = [
            'activity'     => base_path('lang/en/elements/element_json_schema.php'),
            'organisation' => base_path('lang/en/elements/org_json_schema.php'),
        ];

        $this->jsonFilePathMappedToFileType = [
            'activity'     => base_path('app/IATI/Data/elementJsonSchema.json'),
            'organisation' => base_path('app/IATI/Data/organizationElementJsonSchema.json'),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        foreach ($this->files as $file) {
            $transFilePath = $this->langFilePathMappedToFileType[$file];
            $jsonFilePath = $this->jsonFilePathMappedToFileType[$file];

            if (!file_exists($jsonFilePath)) {
                $this->error('The file does not exist.');

                return 0;
            }

            $jsonContentAsString = file_get_contents($jsonFilePath);
            $jsonContentAsAssocArray = json_decode($jsonContentAsString, true);

            $formattedContentForLangFile = $this->formatDataForLangFile($jsonContentAsAssocArray);

            $this->makeEmptyLangFile($transFilePath);
            $this->writeToLangFile($transFilePath, $formattedContentForLangFile);

            $this->info("The jsonContent has been successfully written to $transFilePath");

            $this->removeDuplicateValueInTransFile($transFilePath);

            $this->replaceWithKey($jsonFilePath, $transFilePath, $file);
        }

        return 1;
    }

    /**
     * @param $array
     * @param string $path
     *
     * @return string
     */
    public function formatDataForLangFile($array, string $path = ''): string
    {
        $output = '';
        $keys = ['label', 'placeholder', 'hover_text', 'help_text', 'helper_text'];

        foreach ($array as $key => $value) {
            $currentPath = $path === '' ? "$key" : "$path" . '_' . "$key";

            if (is_array($value)) {
                $output .= $this->formatDataForLangFile($value, $currentPath);
            } elseif (array_key_exists($key, array_flip($keys)) && !empty($value)) {
                $value = $this->useProperEscapeCharacters($value);
                $output .= "'$currentPath' => '$value',\n";
            }
        }

        return $output;
    }

    /**
     * Remove use of single inverted commas in value.
     *
     * @param string $value
     * @return string
     */
    public function useProperEscapeCharacters(string $value): string
    {
        $value = trim($value);
        $value = str_replace('\u2019', "'", $value);

        return str_replace("'", "\'", $value);
    }

    private function makeEmptyLangFile($filePath): void
    {
        file_put_contents($filePath, "<?php\n\nreturn [];\n");
    }

    private function writeToLangFile($filePath, $output): void
    {
        $fileContent = "<?php\n\n return \n [\n$output \n];\n";
        file_put_contents($filePath, $fileContent);
    }

    public function removeDuplicateValueInTransFile($transFilePath): void
    {
        $array = include $transFilePath;

        $normalizedArray = array_map(function ($value) {
            return \Normalizer::normalize($value, \Normalizer::FORM_C);
        }, $array);

        $findDuplicate = array_unique($normalizedArray);

        $uniqueArray = [];
        foreach ($findDuplicate as $key => $value) {
            $uniqueArray[$key] = $array[$key];
        }

        $phpCode = "<?php\n\nreturn " . var_export($uniqueArray, true) . ";\n";

        file_put_contents($transFilePath, $phpCode);

        $this->info('Duplicate values removed and data saved back to the file.');
    }

    public function replaceWithKey($jsonFilePath, $transFilePath, $file): void
    {
        $jsonArray = json_decode(file_get_contents($jsonFilePath), true);

        $translationArray = include $transFilePath;

        $flattenElementJsonData = Arr::dot($jsonArray);

        $keys = ['label', 'placeholder', 'hover_text', 'help_text'];

        if (is_array($translationArray)) {
            foreach ($flattenElementJsonData as $key => $value) {
                $lastKeyPart = strrchr($key, '.');
                $lastKeyPart = $lastKeyPart ? ltrim($lastKeyPart, '.') : $key;

                if (in_array($lastKeyPart, $keys)) {
                    $langKey = array_search($value, $translationArray);

                    if ($langKey !== false) {
                        $flattenElementJsonData[$key] = $file === 'activity'
                            ? 'elements/element_json_schema.' . $langKey
                            : 'elements/org_json_schema.' . $langKey;
                    }
                }
            }
        }

        $updatedJsonArray = Arr::undot($flattenElementJsonData);
        $jsonOutput = json_encode($updatedJsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);

        // This is so that I get minimal git changes. If indent = 4, the entire file will be shown as changed.
        // Ideally only help_text and hover_text should have been changed

        $jsonOutput = str_replace('    ', '  ', $jsonOutput);
        file_put_contents($jsonFilePath, $jsonOutput);

        $this->info('The jsonContent value has been successfully overwritten by its trans key.');
    }
}
