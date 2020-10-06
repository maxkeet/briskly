<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\commands\exchange\XlsExchange;
use app\commands\exchange\FtpProperties;


class XlsExchangeController extends Controller
{

    public function actionIndex()
    {

        $xlsExchange = new XlsExchange();
        $xlsExchange->setFTPProperties(new FtpProperties(
            'localhost',
            'briskly',
            'ftp',
            'ftp'
        ));

        $xlsExchange->setInputJsonFile('order.json');
        $xlsExchange->setOutputXlsxFile('items.xlsx');
        $xlsExchange->export();

        echo $xlsExchange->getInvalidItemsInfo();

        return ExitCode::OK;
    }
}

