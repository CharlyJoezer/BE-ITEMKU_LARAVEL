<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carts extends Model
{
    use HasFactory;

    protected $table = 'carts';
    protected $guarded = ['id_cart'];


    public function products(){
        return $this->belongsTo(Product::class, 'product_id', 'id_product');
    }
}
