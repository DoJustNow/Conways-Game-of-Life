<?php

use Illuminate\Foundation\Inspiring;
use App\Field;
use App\Classes\FieldProcess;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('conway:start {start} {finish?}', function ($start, $finish = null) {
    //
    if(is_null($finish)){
        $finish = $start;
    }
    if ( ! (FieldProcess::validatePackedValue($start)
            && FieldProcess::validatePackedValue($finish) &&($start<=$finish))) {
        $this->info("Результат:\n\tНеверные аргументы");
        return false;
    }
    $mes = ['', 'DIE', 'LOOP', 'STATIC'];
    $after = 'Количество записей в БД до выполнение цикла: ' . Field::count();
    for ($decValue = $start; $decValue <= $finish; $decValue++) {
        if ($find = Field::find($decValue)) {
            $this->info("#$decValue Известено: Steps: " . $find->steps . "\t Result: ". $mes[$find->result]);
        } else {
            $result = FieldProcess::run($decValue);
            /* Фигура
            * 1 - разрушилась
            * 2 - мигалка
            * 3 - стабильная
            */
            $this->info("@$decValue Game Over. Steps: " . $result[0] . "\t Result: "
                    . $mes[$result[1]] ."\t". $result[2] . " record(s) added to DB");
        }
    }
    $this->info("Результат:\n\t$after");
    $this->info("\tКоличество записей в БД после выполнение цикла: " . Field::count() . "\n");
});
