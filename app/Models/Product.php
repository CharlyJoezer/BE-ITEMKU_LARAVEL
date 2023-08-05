<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $guarded = ['id_product'];

    public function sub_categories(){
        return $this->hasOne(Sub_Categories::class, 'id_sub_category', 'sub_category_id');
    }
}
