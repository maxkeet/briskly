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

        //for saving on FTP
        $storage = new Storage(new FtpProperties(
            'localhost',
            'briskly',
            'ftp',
            'ftp'
        ));

        //for saving on local storage
//        $storage = new Storage();

        $storage->setInputJsonFile('order.json');
        $storage->setOutputXlsxFile('items.html');

        $xlsExchange = new XlsExchange();
        $xlsExchange->export($storage);

        echo $xlsExchange->getInvalidItemsInfo();

        return ExitCode::OK;
    }
}

