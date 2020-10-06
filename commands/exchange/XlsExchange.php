<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 13:13
 */

namespace app\commands\exchange;

use yii\helpers\Json;
use yii2tech\spreadsheet\Spreadsheet;
use yii\data\ArrayDataProvider;
use yii2mod\ftp\FtpClient;


class XlsExchange
{

    const STORAGE_PATH = 'storage';
    const BARCODE_LENGTH = 13;

    /**
     * @var resource
     */
    public $spreadsheetTempFileResource;
    /**
     * @var string
     */
    private $OutputXlsxFileName;
    /**
     * @var FtpConnection
     */
    private $ftpProperties;

    /**
     * @var file
     */
    private $inputJsonFile;

    /**
     * @var array
     */
    private $storedInvalidItems;


    public function __construct()
    {
        $this->storedInvalidItems = [];
    }

    /**
     * @param \app\commands\exchange\FtpProperties $connection
     */
    public function setFTPProperties(FtpProperties $connection)
    {
        $this->ftpProperties = $connection;
    }

    /**
     * @param string $filePath
     */
    public function setInputJsonFile(string $filePath)
    {

        $absFilePath = $this->getStoragePath() . '/' . $filePath;

        try {
            if (file_exists($absFilePath)) {
                $this->inputJsonFile = file_get_contents($absFilePath);
            } else {
                throw new \Exception('File "' . $absFilePath . '" not found');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }

    }

    public function setOutputXlsxFile($fileName)
    {
        $this->OutputXlsxFileName = $fileName;
    }

    /**
     * decode JSON
     * validate barcodes
     * export to spreadsheet
     * save to .xlsx
     */
    public function export()
    {

        try {

            $data = Json::decode($this->inputJsonFile);

            if (!$data['items']) {
                throw new \Exception('Not have items in JSON file');
            }

            $validItems = $this->getOnlyEAN13BarcodeItems($data['items']);

            $this->spreadsheetTempFileResource = tmpfile();
            $xlsxTempFilePath = $this->exportToXlsx($validItems);

            $this->saveFile($xlsxTempFilePath);

            fclose($this->spreadsheetTempFileResource);

        } catch (\Exception $e) {
            echo $e->getMessage();
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
     * @return string
     */
    private function getStoragePath() : string
    {
        return \Yii::getAlias('@app') . '/' . self::STORAGE_PATH;
    }

    /**
     * @param string $tempFilePath
     */
    private function saveFile(string $tempFilePath)
    {
        if ($this->ftpProperties) {
            $this->saveToFTP($tempFilePath);
        } else {
            $this->saveToStorage($tempFilePath);
        }
    }

    /**
     * @param string $tempFilePath
     *
     * @throws \yii2mod\ftp\FtpException
     */
    private function saveToFTP(string $tempFilePath)
    {
        $ftp = new FtpClient();
        $ftp->connect($this->ftpProperties->ftpHost);
        $ftp->login($this->ftpProperties->ftpLogin, $this->ftpProperties->ftpPassword);
        $ftp->chdir($this->ftpProperties->ftpDir);
        $ftp->put($this->OutputXlsxFileName, $tempFilePath);

    }

    /**
     * @param string $tempFilePath
     */
    private function saveToStorage(string $tempFilePath)
    {
        rename($tempFilePath, $this->getStoragePath() . '/' . $this->OutputXlsxFileName);
    }

    /**
     * @param array $items
     *
     * @return \yii2tech\spreadsheet\Spreadsheet
     */
    private function exportToXlsx(array $items)
    {

        $exporter = $this->getSpreadsheet($items);

        $tempFilePath = stream_get_meta_data($this->spreadsheetTempFileResource)['uri'];

        $exporter->writerType = 'Xlsx';
        $exporter->save($tempFilePath);

        return $tempFilePath;

    }

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

    /**
     * @param array $items
     *
     * @return array
     */
    private function getOnlyEAN13BarcodeItems(array $items) : array
    {

        $validItems = [];

        foreach ($items as $item) {
            if ($this->checkEAN13($item['item']['barcode'])) {
                $validItems[] = $item;
            } else {
                $this->storedInvalidItems[] = $item;
            }
        }

        return $validItems;
    }

    /**
     * @param string $barcode
     *
     * @return bool
     */
    private function checkEAN13(string $barcode) : bool
    {

        if (strlen($barcode) != self::BARCODE_LENGTH) {
            //            throw new Exception('Barcode lengh most be 13 digits');
            return false;
        }

        if (!ctype_digit($barcode)) {
            //            throw new Exception('Barcode contain not only digits');
            return false;
        }

        return $this->barcodeControlDigitIsValid($barcode);

    }

    /**
     * @param string $barcode
     *
     * @return bool
     */
    private function barcodeControlDigitIsValid(string $barcode) : bool
    {

        $barcodeArr = str_split($barcode);

        $evenSum = 0;
        $oddSum = 0;
        $i = 0;
        while ($i < self::BARCODE_LENGTH - 2) {
            $oddSum += $barcodeArr[$i];
            $i++;
            $evenSum += $barcodeArr[$i];
            $i++;
        }

        $sum = $evenSum + $oddSum * 3;

        return ($sum + $barcode[12]) % 10 == 0;
    }

}