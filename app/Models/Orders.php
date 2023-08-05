<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $guarded = ['id_order'];

    public function products(){
        return $this->hasOne(Product::class, 'id_product', 'product_id');
    }

    public function users(){
        return $this->hasOne(User::class, 'id_user', 'buyer_id');
    }

    public function shops(){
        return $this->hasOne(Shops::class, 'id_shop', 'shop_id');
    }
}
