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

    public function shops(){
        return $this->hasOne(Shops::class, 'id_shop', 'shop_id');
    }

    public function types_sub_categories(){
        return $this->hasOne(Types_Sub_Categories::class, 'id_type_sub_category', 'type_sub_category_id');
    }
}
