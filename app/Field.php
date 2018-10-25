<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Field
 *
 * @property int $id
 * @property int $steps
 * @property int $result
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Field whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Field whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Field whereSteps($value)
 * @mixin \Eloquent
 */
class Field extends Model
{
    //
    public $timestamps = false;

}
