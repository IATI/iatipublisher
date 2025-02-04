<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Bruh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Bruh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $files = [
        'activity',
        'organisation',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->labelPath = base_path('lang/en/elements/label.php');

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
    public function handle()
    {
        foreach ($this->files as $file) {
            $prefix = 'elements/org_json_schema.';

            if ($file == 'activity') {
                $prefix = 'elements/element_json_schema.';
            }

            $labelLang = include $this->labelPath;
            $transFilePath = $this->langFilePathMappedToFileType[$file];
            $jsonFilePath = $this->jsonFilePathMappedToFileType[$file];

            $jsonContentAsString = file_get_contents($jsonFilePath);
            $jsonContentAsAssocArray = json_decode($jsonContentAsString, true);
            $jsonContentAsAssocArray = Arr::dot($jsonContentAsAssocArray);

            foreach ($labelLang as $key => $value) {
                if (str_contains($key, '_label')) {
                    $searchValue = $prefix . $key;
                    $replaceValue = 'elements/label.' . Str::snake(strtolower($value));

                    foreach ($jsonContentAsAssocArray as $jKey => $jValue) {
                        if ($jValue === $searchValue) {
                            $jsonContentAsAssocArray[$jKey] = $replaceValue;
                        }
                    }
                }
            }

            $updatedJsonArray = Arr::undot($jsonContentAsAssocArray);
            $jsonOutput = json_encode($updatedJsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);

            $jsonOutput = str_replace('    ', '  ', $jsonOutput);
            file_put_contents($jsonFilePath, $jsonOutput);
        }
    }
}
