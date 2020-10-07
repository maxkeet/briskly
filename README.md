<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">XlsExchange console command</h1>
    <br>
</p>


INSTALLATION
------------

For get depencies of project:

        composer update

For start processing:

        yii xls-exchange

USE
------------
Modify commands/XlsExchangeController::actionIndex() for different scenaries and data

For saving on FTP:
        
        $storage = new Storage(new FtpProperties(
            'ftpHost,
            'ftpLogin',
            'ftpPassword',
            'ftpDir'
        ));

Or for saving on local storage:
        
        $storage = new Storage();
        
Set input and output files(app using directory /storage for this files)        

        $storage->setInputJsonFile('order.json');
        $storage->setOutputXlsxFile('items.html');

Call xlsExchange->export with param for run exchange:
        
        $xlsExchange = new XlsExchange();
        $xlsExchange->export($storage);

Call XlsExchange::getInvalidItemsInfo() for return items with invalid barcodes:
        
        echo $xlsExchange->getInvalidItemsInfo();
