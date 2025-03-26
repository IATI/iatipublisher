<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Class TranslationsPerFolderExport.
 */
class TranslationsPerFolderExport implements WithMultipleSheets
{
    /**
     * TranslationsPerFolderExport constructor.
     *
     * @param  array  $translations
     */
    public function __construct(protected array $translations)
    {
        //
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->translations as $folder => $translationData) {
            $sheets[] = new TranslationsSheetExport($folder, $translationData);
        }

        return $sheets;
    }
}
