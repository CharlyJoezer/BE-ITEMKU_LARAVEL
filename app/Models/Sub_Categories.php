<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sub_Categories extends Model
{
    use HasFactory;

    protected $table = 'sub_categories';
    protected $guarded = ['id_sub_categories'];
}
