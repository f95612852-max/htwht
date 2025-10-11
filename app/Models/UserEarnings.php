<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEarnings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_views',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addViews(int $viewCount): void
    {
        $this->increment('total_views', $viewCount);
        $this->calculateEarnings();
    }

    public function calculateEarnings(): void
    {
        // $0.3 per 1000 views
        $earningsRate = 0.3;
        $viewsPerEarning = 1000;
        
        $totalEarnings = ($this->total_views / $viewsPerEarning) * $earningsRate;
        
        $this->update([
            'total_earnings' => $totalEarnings,
            'pending_earnings' => $totalEarnings - $this->paid_earnings,
            'last_calculated_at' => now(),
        ]);
    }

    public function markAsPaid(float $amount): void
    {
        $this->increment('paid_earnings', $amount);
        $this->decrement('pending_earnings', $amount);
    }

    public function getEarningsPerThousandViews(): float
    {
        return 0.3;
    }

    public function getViewsNeededForNextEarning(): int
    {
        $currentThousands = floor($this->total_views / 1000);
        $nextThousand = ($currentThousands + 1) * 1000;
        return $nextThousand - $this->total_views;
    }
}