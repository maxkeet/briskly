<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 13:19
 */

namespace app\commands\exchange;


class FtpProperties
{
    public $ftpHost;
    public $ftpLogin;
    public $ftpPassword;
    public $ftpDir;

    public function __construct($ftpHost, $ftpLogin, $ftpPassword, $ftpDir)
    {

        $this->ftpHost = $ftpHost;
        $this->ftpLogin = $ftpLogin;
        $this->ftpPassword = $ftpPassword;
        $this->ftpDir = $ftpDir;

    }
}