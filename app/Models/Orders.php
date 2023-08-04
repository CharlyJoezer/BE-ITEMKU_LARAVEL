<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $guarded = ['id_order'];

    public function product(){
        return $this->hasOne(Product::class, 'product_id', 'id_product');
    }

    public function users(){
        return $this->hasOne(User::class, 'buyer_id', 'id_user');
    }

    public function shops(){
        return $this->hasOne(Shops::class, 'shop_id', 'id_shop');
    }
}
