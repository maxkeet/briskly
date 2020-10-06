<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 13:13
 */

namespace app\commands\exchange;

use yii\helpers\Json;


class XlsExchange
{

    /**
     * @var array
     */
    private $storedInvalidItems;


    public function __construct()
    {
        $this->storedInvalidItems = [];
    }

    /**
     * Decode JSON
     * Validate barcodes
     * Export items to to spreadsheet
     * Save spreadsheet to .xlsx
     *
     * @param \app\commands\exchange\Storage $storage
     */
    public function export(Storage $storage)
    {

        try {

            $data = Json::decode($storage->getInputJsonFile());

            if (!$data['items']) {
                throw new \Exception('Not have items in JSON file');
            }

            $validItems = $this->validateItemsBarcodes($data['items']);

            $xlsTempFilePath = $storage->getTempFilePath();

            $exporter = new SpreadsheetExporter($storage->getOutputXlsxFileName());
            $exporter->exportToTempFile($validItems, $xlsTempFilePath);

            $storage->save($xlsTempFilePath);

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * @return string
     */
    public function getInvalidItemsInfo()
    {

        if (!$this->storedInvalidItems) {
            return '';
        }

        $data = 'Not exported items with invalid barcodes:' . PHP_EOL;

        foreach ($this->storedInvalidItems as $item) {
            $data .= $item['item']['id'] . ' '
                . $item['item']['name'] . ' '
                . $item['item']['barcode']
                . PHP_EOL;
        }

        return $data;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function validateItemsBarcodes(array $items) : array
    {

        $validItems = [];

        foreach ($items as $item) {
            if (ValidateBarcodeHelper::checkEAN13($item['item']['barcode'])) {
                $validItems[] = $item;
            } else {
                $this->storedInvalidItems[] = $item;
            }
        }

        return $validItems;
    }

}