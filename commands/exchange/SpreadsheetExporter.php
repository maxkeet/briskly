<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 20:42
 */

namespace app\commands\exchange;

use yii2tech\spreadsheet\Spreadsheet;
use yii\data\ArrayDataProvider;

/**
 * This class generate 'Xls','Xlsx','Ods','Csv','Html' spreadsheet file from inputed array
 */
class SpreadsheetExporter
{
    const SUPPORTED_OUTPUT_FILE_TYPES = [
        'Xls',
        'Xlsx',
        'Ods',
        'Csv',
        'Html'
    ];

    private $writerType;

    public function __construct($outputFileName)
    {
        $ext = end(explode('.', $outputFileName));
        $this->writerType = ucfirst($ext);

        if(!in_array($this->writerType, self::SUPPORTED_OUTPUT_FILE_TYPES)){
            throw new \Exception('Output file format(' . $outputFileName . ') not supported');
        }

    }

    /**
     * @param array $items
     *
     * @return string
     */
    public function exportToTempFile(array $items, string $tempFilePath)
    {

        $exporter = $this->getSpreadsheet($items);
        $exporter->writerType = $this->writerType;
        $exporter->save($tempFilePath);

    }

    /**
     * @param array $items
     *
     * @return \yii2tech\spreadsheet\Spreadsheet
     */
    private function getSpreadsheet(array $items) : Spreadsheet
    {

        return new Spreadsheet([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $items
            ]),
            'columns'      => [
                [
                    'attribute' => 'id'
                ],
                [
                    'attribute' => 'item.barcode',
                    'header'    => 'ШК'
                ],
                [
                    'attribute' => 'item.name',
                    'header'    => 'Название'
                ],
                [
                    'attribute' => 'amount',
                    'header'    => 'Кол-во'
                ],
                [
                    'attribute' => 'price',
                    'header'    => 'Сумма'
                ],
            ],
        ]);

    }
}