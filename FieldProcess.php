<?php

namespace field;

use mysqli_sql_exception;

mysqli_report(MYSQLI_REPORT_STRICT);

class FieldProcess
{

    //Размернасть поля
    private static $size = 5;
    //Буфер для хранения всех промежуточных состояний поля в запакованном виде
    private static $stepBuffer = [];
    //Номер текущего поколение в игре
    private static $generation = 0;
    //Минимальное значение упакованного в десятичное число представления массива.
    private static $minPackedValue = 0;
    //Максимальное значение упакованного в десятичное число представления массива.
    private static $maxPackedValue = 2 ** 25 - 1;
    //Текущее состояние поля
    private static $fieldCurrent = [];
    //Состояние поля на следующем шагу
    private static $fieldNextStep = [];
    public static $mysqli;

    //Распаковка десятичного числа в массив
    private static function arrayUnpacking(int $decValue): array
    {
        for ($i = self::$size - 1; $i >= 0; $i--) {
            for ($j = self::$size - 1; $j >= 0; $j--) {
                $remainder   = $decValue % 2;
                $decValue    = ($decValue - $remainder) / 2;
                $arr[$i][$j] = $remainder;
            }
        }

        return $arr;
    }

    //Упаковка Массива в десятичное число
    private static function arrayPacking(array $arr): int
    {
        $dec = '';
        for ($i = 0; $i < self::$size; $i++) {
            for ($j = 0; $j < self::$size; $j++) {
                $dec .= $arr[$i][$j];
            }
        }

        return bindec($dec);
    }

    public static function validatePackedValue($decValue): bool
    {
        return is_numeric($decValue) && (self::$minPackedValue <= $decValue)
                && ($decValue <= self::$maxPackedValue);

    }

    private static function calculateNextGeneration(int $decValue): int
    {
        $result             = 0;
        self::$fieldCurrent = self::arrayUnpacking($decValue);
        self::addBuffer($decValue);
        if ($result = self::checkGameOver()) {
            return $result;
        }

        for ($i = 0; $i < self::$size; $i++) {
            for ($j = 0; $j < self::$size; $j++) {
                $neighborsCount = 0;
                if (self::checkIndex($i, $j + 1)) {
                    $neighborsCount += self::$fieldCurrent[$i][$j + 1];
                }
                if (self::checkIndex($i, $j - 1)) {
                    $neighborsCount += self::$fieldCurrent[$i][$j - 1];
                }

                if (self::checkIndex($i - 1, $j + 1)) {
                    $neighborsCount += self::$fieldCurrent[$i - 1][$j + 1];
                }
                if (self::checkIndex($i - 1, $j)) {
                    $neighborsCount += self::$fieldCurrent[$i - 1][$j];
                }
                if (self::checkIndex($i - 1, $j - 1)) {
                    $neighborsCount += self::$fieldCurrent[$i - 1][$j - 1];
                }

                if (self::checkIndex($i + 1, $j + 1)) {
                    $neighborsCount += self::$fieldCurrent[$i + 1][$j + 1];
                }
                if (self::checkIndex($i + 1, $j)) {
                    $neighborsCount += self::$fieldCurrent[$i + 1][$j];
                }
                if (self::checkIndex($i + 1, $j - 1)) {
                    $neighborsCount += self::$fieldCurrent[$i + 1][$j - 1];
                }

                if ($neighborsCount === 3 || ($neighborsCount === 2
                                && self::$fieldCurrent[$i][$j] === 1)) {
                    self::$fieldNextStep[$i][$j] = 1;
                } else {
                    self::$fieldNextStep[$i][$j] = 0;
                }
            }
        }

        self::$fieldCurrent = self::$fieldNextStep;
        self::$generation++;
        if ($decValue == self::arrayPacking(self::$fieldCurrent)) {
            $result = 3;
        }

        return $result;
    }

    private static function checkIndex(int $i, int $j): bool
    {
        return ($i >= 0 && $i <= self::$size - 1) && ($j >= 0 && $j <= self::$size - 1);
    }

    private static function addBuffer(int $decValue)
    {
        self::$stepBuffer[self::$generation] = $decValue;
    }

    private static function checkGameOver(): int
    {
        for ($i = 0; $i < count(self::$stepBuffer); $i++) {
            if ((self::$stepBuffer[self::$generation] == self::$stepBuffer[$i])
                    && (self::$generation != $i)) {
                return 2;
            }
            if (self::$stepBuffer[self::$generation] == 0) {
                return 1;
            }
        }

        return 0;
    }

    public static function run(int $decValue)
    {

        if ( ! self::validatePackedValue($decValue)) {
            return false;
        }
        while (true) {
            if (($result = self::calculateNextGeneration($decValue)) !== 0) {
                break;
            }
            $decValue = self::arrayPacking(self::$fieldCurrent);
        }
        $count            = self::add2Db($result);
        $gen              = self::$generation;
        self::$generation = 0;
        self::$stepBuffer = [];

        return [$gen, $result, $count];
    }

    private static function symmetry($value)
    {
        $n   = self::$size - 1;
        $arr = self::arrayUnpacking($value);
        //Первая симметрия
        for ($i = 0; $i < self::$size; $i++) {
            for ($j = 0; $j < self::$size; $j++) {
                $sym[0][$i][$j] = $arr[$i][$j];
                $sym[1][$i][$j] = $arr[$n - $i][$j];
                $sym[2][$i][$j] = $arr[$i][$n - $j];
                $sym[3][$i][$j] = $arr[$n - $i][$n - $j];
                $sym[4][$i][$j] = $arr[$j][$i];
                $sym[5][$i][$j] = $arr[$n - $j][$i];
                $sym[6][$i][$j] = $arr[$j][$n - $i];
                $sym[7][$i][$j] = $arr[$n - $j][$n - $i];
            }
        }
        foreach ($sym as $symArr) {
            $values[] = self::arrayPacking($symArr);
        }

        return $values;
    }

    private static function add2Db(int $result)
    {
        foreach (self::$stepBuffer as $key => $value) {
            $steps = self::$generation - $key;
            self::$mysqli->query("INSERT INTO `fields_result` (`id`, `steps`, `result`) VALUES ('$value', '$steps', '$result')");
        }

        return $key;
    }

    public static function getStepBuffer(): array
    {
        return self::$stepBuffer;
    }

    public static function getGeneration(): int
    {
        return self::$generation;
    }
}