<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class BaseDeletableModel extends BaseModel
{
    use SoftDeletes;
}
