<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostViewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'viewer_id',
        'ip_address',
        'user_agent',
        'viewed_at',
        'counted_for_earnings',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'counted_for_earnings' => 'boolean',
    ];

    public $timestamps = false;

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public static function logView(int $statusId, ?int $viewerId, string $ipAddress, ?string $userAgent): bool
    {
        try {
            // Check if this view already exists today
            $existingView = self::where('status_id', $statusId)
                ->where('ip_address', $ipAddress)
                ->where('viewer_id', $viewerId)
                ->where('viewed_at', '>=', now()->startOfDay())
                ->first();

            if ($existingView) {
                return false; // Already counted today
            }

            // Create new view log
            $viewLog = self::create([
                'status_id' => $statusId,
                'viewer_id' => $viewerId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'viewed_at' => now(),
                'counted_for_earnings' => false,
            ]);

            // Update earnings for post owner
            self::updateEarningsForView($statusId);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to log post view: ' . $e->getMessage());
            return false;
        }
    }

    private static function updateEarningsForView(int $statusId): void
    {
        $status = Status::find($statusId);
        if (!$status || !$status->profile) {
            return;
        }

        $userId = $status->profile->user_id;
        if (!$userId) {
            return;
        }

        // Get or create user earnings record
        $earnings = UserEarnings::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_views' => 0,
                'total_earnings' => 0.00,
                'pending_earnings' => 0.00,
                'paid_earnings' => 0.00,
            ]
        );

        $earnings->addViews(1);
    }

    public static function getUniqueViewsForStatus(int $statusId): int
    {
        return self::where('status_id', $statusId)->count();
    }

    public static function getTotalViewsForUser(int $userId): int
    {
        return self::join('statuses', 'post_view_logs.status_id', '=', 'statuses.id')
            ->join('profiles', 'statuses.profile_id', '=', 'profiles.id')
            ->where('profiles.user_id', $userId)
            ->count();
    }
}