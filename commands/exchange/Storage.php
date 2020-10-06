<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 18:33
 */

namespace app\commands\exchange;

use yii2mod\ftp\FtpClient;

class Storage
{
    const STORAGE_PATH = 'storage';

    private $inputJsonFile;
    /**
     * @var string
     */
    private $outputXlsxFileName;
    /**
     * @var FtpProperties
     */
    private $ftpProperties;

    /**
     * @var resource
     */
    public $tempFileResource;

    /**
     * @return string
     */

    public function __construct($ftpProperties = null)
    {
        $this->ftpProperties = $ftpProperties;
    }

    /**
     * @param string $filePath
     */
    public function setInputJsonFile(string $filePath)
    {

        $absFilePath = $this->getPath() . '/' . $filePath;

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


    /**
     * @param string $fileName
     */
    public function setOutputXlsxFile(string $fileName)
    {
        $this->outputXlsxFileName = $fileName;
    }

    public function getInputJsonFile()
    {
        return $this->inputJsonFile;
    }

    /**
     * @return string
     */
    public function getTempFilePath() : string
    {
        $this->tempFileResource = tmpfile();
        return stream_get_meta_data($this->tempFileResource)['uri'];
    }

    /**
     * @param string $tempFilePath
     */
    public function save(string $tempFilePath)
    {
        if ($this->ftpProperties) {
            $this->saveToFTP($tempFilePath);
        } else {
            $this->saveToStorage($tempFilePath);
        }

        fclose($this->tempFileResource);
    }

    private function getPath() : string
    {
        return \Yii::getAlias('@app') . '/' . self::STORAGE_PATH;
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
        $ftp->put($this->outputXlsxFileName, $tempFilePath);

    }

    /**
     * @param string $tempFilePath
     */
    private function saveToStorage(string $tempFilePath)
    {
        rename($tempFilePath, $this->getPath() . '/' . $this->outputXlsxFileName);
    }

}