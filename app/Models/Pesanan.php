<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'user_id',
        'menu_id',
        'jumlah',
        'total_harga',
        'metode_pembayaran',
        'status_pembayaran',
        'status',
    ];

    protected $attributes = [
        'metode_pembayaran' => 'Cash',
        'status_pembayaran' => 'Belum Dibayar',
        'status' => 'Menunggu Konfirmasi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
