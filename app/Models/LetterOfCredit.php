<?php

namespace TradeTree\Models\LettersOfCredit;

use Illuminate\Database\Eloquent\Model;
use TradeTree\Company;
use TradeTree\Models\Banks\Bank;
use TradeTree\Models\Contracts\Contract;
use TradeTree\Models\Trades\Trade;
use TradeTree\Models\Traits\BelongsToCompany;
use TradeTree\Models\Traits\PreFill;
use TradeTree\Models\Traits\Trades\AffectsChecklist;

class LetterOfCredit extends Model
{
    use PreFill, BelongsToCompany, AffectsChecklist;

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 2;

    protected $table = 'letters_of_credit';

    protected $casts = ['external' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        $fillable = [
            'company_id',
            'number',
            'bank_id',
            'advising_bank_id',
            'issue_date',
            'lsd',
            'lc_confirmed',
            'status_id',
            'external',
            'note',
        ];

        $this->fillable = array_merge($fillable);
        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($letter) {
            Trade::where('lc_id', $letter->id)->update(['lc_id' => null]);
        });

        static::saved(function (LetterOfCredit $lc) {
            foreach ($lc->trades as $trade) {
                static::updateChecklist($trade);
            }
        });
    }

    public static function getStatuses()
    {
        static $statuses = [];
        if (empty($statuses)) {
            $statuses = [
                self::STATUS_OPEN => 'Open',
                self::STATUS_CLOSED => 'Closed',
            ];
        }

        return $statuses;
    }

    public function trades()
    {
        return $this->hasMany(Trade::class, 'lc_id');
    }

    public function scopeByStatus($query, $status = self::STATUS_OPEN)
    {
        return !is_null($status) ? $query->where('status_id', $status) : $query;
    }

    public function scopeSearch($query, $search = '')
    {
        if (!empty($search)) {
            $banks = Bank::where('name', 'like', '%' . $search . '%')
                ->select('id')
                ->get()
                ->pluck('id');

            $query->where('number', 'like', '%' . $search . '%')
                ->orWhere(function ($q) use ($banks) {
                    $q->whereIn('bank_id', $banks);
                })->orWhere(function ($q) use ($banks) {
                    $q->whereIn('advising_bank_id', $banks);
                });
        }

        return $query;
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function advising_bank()
    {
        return $this->belongsTo(Bank::class, 'advising_bank_id');
    }
}
