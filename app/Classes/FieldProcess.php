<?php

namespace App\Classes;

use App\Field;

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

    public static function validatePackedValue(int $decValue): bool
    {
        return is_numeric($decValue) && (self::$minPackedValue <= $decValue)
                && ($decValue <= self::$maxPackedValue);

    }

    /* Фигура
     * 1 - разрушилась
     * 2 - мигалка
     * 3 - стабильная
     *
     */
    private static function calculateNextGeneration(int $decValue): int
    {
        $result       = 0;
        self::$fieldCurrent = self::arrayUnpacking($decValue);
        self::addBuffer($decValue);
        if ($result = self::checkGameOver()) {
            return $result;
        }

        for ($i = 0; $i < self::$size; $i++) {
            for ($j = 0; $j < self::$size; $j++) {
                $neighborsCount = 0;
                if (self::checkIndex($i, $j + 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i][$j + 1] === 0) ? 0 : 1;
                }
                if (self::checkIndex($i, $j - 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i][$j - 1] === 0) ? 0 : 1;
                }

                if (self::checkIndex($i - 1, $j + 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i - 1][$j + 1] === 0) ? 0 : 1;
                }
                if (self::checkIndex($i - 1, $j)) {
                    $neighborsCount += (self::$fieldCurrent[$i - 1][$j] === 0) ? 0 : 1;
                }
                if (self::checkIndex($i - 1, $j - 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i - 1][$j - 1] === 0) ? 0 : 1;
                }

                if (self::checkIndex($i + 1, $j + 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i + 1][$j + 1] === 0) ? 0 : 1;
                }
                if (self::checkIndex($i + 1, $j)) {
                    $neighborsCount += (self::$fieldCurrent[$i + 1][$j] === 0) ? 0 : 1;
                }
                if (self::checkIndex($i + 1, $j - 1)) {
                    $neighborsCount += (self::$fieldCurrent[$i + 1][$j - 1] === 0) ? 0 : 1;
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

    private static function add2Db(int $result)
    {
        foreach (self::getStepBuffer() as $key => $value) {
            if (Field::where('id', '=', $value)->first()) {
                break;
            }
            $field         = new Field();
            $field->id     = $value;
            $field->steps  = self::$generation - $key;
            $field->result = $result;
            $field->save();
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