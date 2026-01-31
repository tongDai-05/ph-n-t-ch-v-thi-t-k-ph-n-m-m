<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'price',
        'description',
        'quantity',
        'cover_image',
        'category_id',
        'publisher',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function category() {
    return $this->belongsTo(Category::class);
}
}
