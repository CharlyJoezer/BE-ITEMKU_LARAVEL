<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Types_Sub_Categories extends Model
{
    use HasFactory;

    protected $table = 'types_sub_categories';
    protected $guarded = ['id_type_sub_category'];
}
