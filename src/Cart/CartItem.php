<?php

namespace App\Cart;

use App\Entity\Product;

class CartItem
{
    public $product;
    public $quantity;

    public function __construct(Product $product, int $qty)
    {
        $this->product = $product;
        $this->quantity = $qty;
    }

    public function getTotal(): int
    {
        return $this->product->getPrice() * $this->quantity;
    }
}
