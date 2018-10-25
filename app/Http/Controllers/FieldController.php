<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', 9000);
use App\Classes;
use App\Classes\FieldProcess;
use App\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{

    //
    public function index()
    {
        $mes = ['', 'разрушилась', 'мигалка', 'стабильная'];
        //$field    = new FieldProcess();
        //$decValue = 145536;
        echo 'Количество записей в БД до выполнение цикла: '.Field::count().'<br>';
        for ($decValue = 0; $decValue < 10; $decValue++) {
            if ($find = Field::find($decValue)) {
                /*echo 'Результат уже известен: ' . $find->steps . ' шагов. Результат: ' . $find->result . '<br>';*/
            } else {
                $result = FieldProcess::run($decValue);
                /* Фигура
                * 1 - разрушилась
                * 2 - мигалка
                * 3 - стабильная
                */
                /*echo('. Game Over за ' . $result[0] . 'шагов. Результат: '
                        . $mes[$result[1]] . '. Добавлено: ' . $result[2] . ' записей в БД.<br>');*/
            }
        }
        echo 'Количество записей в БД после выполнение цикла: '.Field::count().'<br>';
    }
}
