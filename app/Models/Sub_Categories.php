<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sub_Categories extends Model
{
    use HasFactory;

    protected $table = 'sub_categories';
    protected $guarded = ['id_sub_categories'];

    public function types_sub_categories(){
        return $this->hasMany(Types_Sub_Categories::class, 'sub_category_id', 'id_sub_category');
    }

    public function product(){
        return $this->hasMany(Product::class, 'sub_category_id', 'id_sub_category');
    }
}
