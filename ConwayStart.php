<?php

use field\FieldProcess;

include 'FieldProcess.php';

$start  = $argv[1] ?? null;
$finish = $argv[2] ?? $start;

if ( ! (FieldProcess::validatePackedValue($start)
        && FieldProcess::validatePackedValue($finish)
        && ($start <= $finish))) {
    die("\e[1;3;33mРезультаты:\n\t\e[1;3;31mНеверные аргументы\n");


}

$consoleColor         = [
    'light'  => "\033[1;33m",
    'green'  => "\033[0;32m",
    'yellow' => "\033[0;33m",
    'cyan'   => "\033[0;36m",
];
$mysqli               = new mysqli('localhost', 'admin', '123456',
    'conways_game');
FieldProcess::$mysqli = &$mysqli;

$mes     = ['', 'DIE', 'LOOP', 'STATIC'];
$headers = ['источник данных', 'шаги', 'результат', 'записей добавленных в БД'];
echo "\e[1;5;32m" . 'Подсчет количества записей в БД...';
$before = $mysqli->query("SELECT count(*) AS aggregate FROM `fields`")
                 ->fetch_assoc()['aggregate'];
echo "\r                        ";
$time_start = microtime(true);

for ($decValue = $finish; $decValue >= $start; $decValue--) {
    progressBar($decValue, $start);
    if ($find
        = $mysqli->query("SELECT * FROM `fields` WHERE `id` = '$decValue'")
                 ->fetch_assoc()) {

        echo "$consoleColor[green]Значение: $consoleColor[yellow]$decValue\t$consoleColor[light]| $consoleColor[green]$headers[0]: $consoleColor[cyan]database $consoleColor[light]| $consoleColor[green]$headers[1]: $consoleColor[yellow]$find[steps]\t$consoleColor[light]| $consoleColor[green]$headers[2]: $consoleColor[yellow]"
             . $mes[$find['result']] . "\n";

    } else {

        $result = FieldProcess::run($decValue);

        echo "$consoleColor[green]Значение: $consoleColor[yellow]$decValue\t$consoleColor[light]| $consoleColor[green]$headers[0]: $consoleColor[cyan]"
             . ($result[2] ? 'выч.част' : 'вычислен')
             . " $consoleColor[light]| $consoleColor[green]$headers[1]: $consoleColor[yellow]$result[0]\t$consoleColor[light]| $consoleColor[green]$headers[2]: $consoleColor[yellow]{$mes[$result[1]]}\n";

    }
}
$time = microtime(true) - $time_start;
echo "\e[1;5;32m" . 'Подсчет количества записей в БД...';
$after = $mysqli->query("SELECT count(*) AS aggregate FROM `fields`")
                ->fetch_assoc()['aggregate'];
echo "\r                        ";
echo "\e[1;3;33m" . "Результаты:\n\e[1;0;33m";
echo "\e[1;2;37m" . "\tЗатраченное время: $consoleColor[cyan]" . round($time, 2)
     . " $consoleColor[yellow]секунды\n";
echo "\e[1;2;37m"
     . "\tКоличество записей в БД до выполнения цикла:\e[1;36m\t$before\n";
echo "\e[1;2;37m"
     . "\tКоличество записей в БД после выполнения цикла:\e[1;36m\t$after\n";

$mysqli->close();

function progressBar($done, $total)
{

    $percent = "$done/$total";
    echo "$percent\r";
}
