<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

// Model OrderItem: 
class OrderItem extends Model 
{ 
    public $timestamps = true; 
    protected $table = "order_item"; 
    protected $fillable = ['order_id', 'produk_id', 'quantity', 'harga']; 
 
    public function order() 
    { 
        return $this->belongsTo(Order::class); 
    } 
 
    public function produk() 
    { 
        return $this->belongsTo(Product::class); 
    } 
 
    public function kategori() 
    { 
        return $this->belongsTo(Kategori::class); 
    } 
}