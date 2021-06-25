<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency_from',
        'currency_to',
        'amount',
        'price',
        'selling'
    ];

    public function is_sufficient(): bool {
        return $this->price * $this->amount > 500;
    }

    public function __toString() {
        return ($this->selling ? 'Bid' : 'Ask') . " $this->currency_from $this->currency_to: amount - $this->amount, price - $this->price";
    }
}
