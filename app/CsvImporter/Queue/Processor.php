<?php

declare(strict_types=1);

namespace App\CsvImporter\Queue;

use App\CsvImporter\CsvReader\CsvReader;
use App\CsvImporter\Queue\Jobs\ImportActivity;
use App\Imports\CsvToArrayWithHeaders;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class Processor.
 */
class Processor
{
    use DispatchesJobs;

    /**
     * @var CsvReader
     */
    protected CsvReader $csvReader;

    /**
     * Processor constructor.
     * @param CsvReader $csvReader
     */
    public function __construct(CsvReader $csvReader)
    {
        $this->csvReader = $csvReader;
    }

    /**
     * Push a CSV file's data for processing into Queue.
     *
     * @param $file
     * @param $filename
     * @param $activityIdentifiers
     * @param $organizationReportingOrg
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function pushIntoQueue($file, $filename, $activityIdentifiers, $organizationReportingOrg): void
    {
        $str = mb_convert_encoding(file_get_contents($file->getPathName()), 'UTF-8');
        file_put_contents($file->getPathName(), $str);
        $csv = Excel::toCollection(new CsvToArrayWithHeaders, $file)->first()->toArray();
        $humanitarianScopeVocabularyArray = getCodeList('HumanitarianScopeVocabulary', 'Activity', filterDeprecated: true);
        $humanitarianScopeVocabularyArray = array_flip($humanitarianScopeVocabularyArray);

        foreach ($csv as $index => $csvDatum) {
            $csv[$index]['humanitarian_scope_vocabulary'] = $this->getValidHumanitarianScopeVocabulary($humanitarianScopeVocabularyArray, $csvDatum);
        }

        $this->dispatch(
            new ImportActivity(new CsvProcessor($csv), $filename, $activityIdentifiers, $organizationReportingOrg)
        );
    }

    /**
     * Returns humanitarian_scope_vocabulary code from complete humanitarian_scope_vocabulary string of csv.
     *
     * @param $humanitarianScopeVocabularyArray
     * @param array $csvDatum
     *
     * @return string
     */
    public function getValidHumanitarianScopeVocabulary($humanitarianScopeVocabularyArray, array $csvDatum): string
    {
        $humanitarianScopeVocabulary = Arr::get($csvDatum, 'humanitarian_scope_vocabulary', '');

        if ($humanitarianScopeVocabulary) {
            return (string) Arr::get($humanitarianScopeVocabularyArray, $humanitarianScopeVocabulary, $humanitarianScopeVocabulary);
        }

        return '';
    }
}
