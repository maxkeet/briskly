<?php
/**
 * Created by PhpStorm.
 * User: Keet
 * Date: 06.10.2020
 * Time: 18:12
 */

namespace app\commands\exchange;


class ValidateBarcodeHelper
{
    const BARCODE_LENGTH = 13;

    /**
     * @param string $barcode
     *
     * @return bool
     */
    public static function checkEAN13(string $barcode) : bool
    {

        if (strlen($barcode) != self::BARCODE_LENGTH) {
            return false;
        }

        if (!ctype_digit($barcode)) {
            return false;
        }

        return self::barcodeControlDigitIsValid($barcode);

    }

    /**
     * @param string $barcode
     *
     * @return bool
     */
    public static function barcodeControlDigitIsValid(string $barcode) : bool
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