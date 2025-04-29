<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoProduk extends Model
{
    protected $table = 'foto_produk';
    protected $guarded = ['id'];

    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id'); // Relasi balik ke produk
    }
}

