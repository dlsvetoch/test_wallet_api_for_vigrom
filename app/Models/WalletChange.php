<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletChange extends Model
{
    use HasFactory;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function wallet()
    {
      return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    protected $fillable = [
      'mutable_property',
      'is_transaction',
      'successfully',
      'action',
      'description',
      'wallet_id',
      'transaction_id',
    ];
    
    protected $attributes = [
      'is_transaction' => false,
      'description'    => null,
  ];
}
