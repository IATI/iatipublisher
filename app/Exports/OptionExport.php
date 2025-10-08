<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Contracts\View\View;
use JsonException;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * Class OptionExport.
 */
class OptionExport implements FromView, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * Filename for selecting the json file.
     *
     * @var string
     */
    protected string $fileName;

    /**
     * Sheet name to export.
     *
     * @var string
     */
    protected string $sheetName;

    /**
     * Configuration for color code and cell merge for different xls files.
     *
     * @var array|array[]
     */
    protected array $instruction_properties = [
        'period_instructions'    => [
            'merge_cells' => [
                'A1:Z1',
                'A2:Z5',
            ],
        ],
        'activity_instructions'  => [
            'merge_cells' => [
                'A1:Z1',
                'A2:Z5',
            ],
        ],
        'result_instructions'    => [
            'merge_cells' => [
                'A1:Z1',
                'A2:Z5',
            ],
        ],
        'indicator_instructions' => [
            'merge_cells' => [
                'A1:Z1',
                'A2:Z5',
            ],
        ],
    ];

    /**
     * @param $fileName
     * @param $sheetName
     */
    public function __construct($fileName, $sheetName)
    {
        $this->fileName = $fileName;
        $this->sheetName = $sheetName;
    }

    /**
     * To define the name of a sheet.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->sheetName;
    }

    /**
     * To export data using blade file.
     *
     * @return View
     * @throws JsonException
     */
    public function view(): View
    {
        return view(
            'Export.optionExport',
            ['data' => readJsonFile('Exports/XlsExportTemplate/' . $this->fileName . '.json')]
        );
    }

    /**
     * To manipulate sheets after sheet being created like color coding, merging cell or size.
     *
     * @return mixed
     */
    public function registerEvents(): array
    {
        $mergeCells = $this->instruction_properties[$this->fileName]['merge_cells'] ?? [];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($mergeCells) {
                if ($this->sheetName === 'Instructions') {
                    $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(100);

                    foreach ($mergeCells as $merge_cell) {
                        $event->sheet->mergeCells($merge_cell);
                    }

                    $jsonData = readJsonFile('Exports/XlsExportTemplate/' . $this->fileName . '.json');

                    if (is_array($jsonData) && count($jsonData) >= 2) {
                        $firstRow = $jsonData[0];
                        $secondRow = $jsonData[1];

                        $instructionsText = $firstRow['1'] ?? '';
                        if (!empty($instructionsText)) {
                            $event->sheet->setCellValue('A1', $instructionsText);
                            $event->sheet->getDelegate()->getStyle('A1')
                                ->getFont()
                                ->setBold(true)
                                ->setSize(18);
                        }

                        $url = $secondRow['1'] ?? '';
                        if (!empty($url)) {
                            $event->sheet->setCellValue('A2', $url);
                            $event->sheet->getDelegate()->getStyle('A2')
                                ->getFont()
                                ->setSize(12)
                                ->setUnderline(Font::UNDERLINE_SINGLE)
                                ->getColor()->setARGB('FF0000FF');
                            $event->sheet->getCell('A2')->getHyperlink()->setUrl($url);
                        }

                        // Center align both rows
                        $event->sheet->getDelegate()->getStyle('A1:Z2')
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }
            },
        ];
    }
}
