<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'location',
        'device_type',
        'browser',
        'os',
        'session_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeByPeriod($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope pour les connexions récentes (dernières 24h)
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope pour les connexions échouées
     */
    public function scopeFailedLogins($query)
    {
        return $query->where('action', 'failed_login');
    }

    /**
     * Scope pour les connexions réussies
     */
    public function scopeSuccessfulLogins($query)
    {
        return $query->where('action', 'login');
    }

    /**
     * Obtenir la durée de session (si logout existe)
     */
    public function getSessionDuration()
    {
        if ($this->action !== 'login') {
            return null;
        }

        $logout = self::where('user_id', $this->user_id)
            ->where('action', 'logout')
            ->where('created_at', '>', $this->created_at)
            ->orderBy('created_at')
            ->first();

        if (!$logout) {
            return null; // Session toujours active
        }

        return $this->created_at->diffInSeconds($logout->created_at);
    }

    /**
     * Formater la durée de session
     */
    public function getFormattedSessionDuration()
    {
        $duration = $this->getSessionDuration();

        if ($duration === null) {
            return 'Session active';
        }

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        } else {
            return sprintf('%ds', $seconds);
        }
    }

    /**
     * Obtenir le type d'appareil depuis le user agent
     */
    public static function getDeviceType($userAgent)
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
            return 'mobile';
        }

        if (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Obtenir le navigateur depuis le user agent
     */
    public static function getBrowser($userAgent)
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'chrome') !== false) {
            return 'Chrome';
        }

        if (strpos($userAgent, 'firefox') !== false) {
            return 'Firefox';
        }

        if (strpos($userAgent, 'safari') !== false && strpos($userAgent, 'chrome') === false) {
            return 'Safari';
        }

        if (strpos($userAgent, 'edge') !== false) {
            return 'Edge';
        }

        if (strpos($userAgent, 'opera') !== false) {
            return 'Opera';
        }

        return 'Other';
    }

    /**
     * Obtenir l'OS depuis le user agent
     */
    public static function getOS($userAgent)
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'windows') !== false) {
            return 'Windows';
        }

        if (strpos($userAgent, 'mac') !== false) {
            return 'macOS';
        }

        if (strpos($userAgent, 'linux') !== false) {
            return 'Linux';
        }

        if (strpos($userAgent, 'android') !== false) {
            return 'Android';
        }

        if (strpos($userAgent, 'ios') !== false || strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false) {
            return 'iOS';
        }

        return 'Other';
    }
}
