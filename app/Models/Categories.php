<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $guarde = ['id_category'];

    public function sub_categories(){
        return $this->hasMany(Sub_Categories::class, 'category_id', 'id_category');
    }
}
