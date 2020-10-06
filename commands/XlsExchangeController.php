<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\commands\exchange\XlsExchange;
use app\commands\exchange\Storage;
use app\commands\exchange\FtpProperties;


class XlsExchangeController extends Controller
{

    public function actionIndex()
    {

        $storage = new Storage(new FtpProperties(
            'localhost',
            'briskly',
            'ftp',
            'ftp'
        ));
//        $storage = new Storage();

        $storage->setInputJsonFile('order1.json');
        $storage->setOutputXlsxFile('items.xlsx');

        $xlsExchange = new XlsExchange();
        $xlsExchange->export($storage);

        echo $xlsExchange->getInvalidItemsInfo();

        return ExitCode::OK;
    }
}

