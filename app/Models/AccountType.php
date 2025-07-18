<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'account_group',
        'account_group_id',
        'minimum_deposit',
        'leverage',
        'currency',
        'allow_create_account',
        'type',
        'commission_structure',
        'trade_open_duration',
        'maximum_account_number',
        'account_group_balance',
        'account_group_equity',
        'descriptions',
        'color',
        'edited_by',
    ];

    // Relations
    public function trading_accounts(): HasMany
    {
        return $this->hasMany(TradingAccount::class, 'account_type_id', 'id');
    }
}
