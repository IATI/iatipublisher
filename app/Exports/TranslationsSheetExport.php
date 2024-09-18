<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Class TranslationsSheetExport.
 */
class TranslationsSheetExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * TranslationsSheetExport constructor.
     *
     * @param  string  $folderName
     * @param  array  $translations
     */
    public function __construct(protected string $folderName, protected array $translations)
    {
        //
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return collect($this->translations);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $languageDirectories = array_map(function ($directory) {
            return basename($directory);
        }, File::directories(lang_path('')));

        $headings = ['Key', 'Filename'];

        return array_merge($headings, $languageDirectories);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->folderName;
    }
}
