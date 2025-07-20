<?php

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginActivityService
{
    /**
     * Enregistrer une activité de connexion
     */
    public static function logLogin(User $user, Request $request, $sessionId = null): void
    {
        try {
            LoginActivity::create([
                'user_id' => $user->id,
                'action' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => self::getLocation($request->ip()),
                'device_type' => LoginActivity::getDeviceType($request->userAgent()),
                'browser' => LoginActivity::getBrowser($request->userAgent()),
                'os' => LoginActivity::getOS($request->userAgent()),
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'activité de connexion', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enregistrer une activité de déconnexion
     */
    public static function logLogout(User $user, Request $request, $sessionId = null): void
    {
        try {
            LoginActivity::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => self::getLocation($request->ip()),
                'device_type' => LoginActivity::getDeviceType($request->userAgent()),
                'browser' => LoginActivity::getBrowser($request->userAgent()),
                'os' => LoginActivity::getOS($request->userAgent()),
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'activité de déconnexion', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enregistrer une tentative de connexion échouée
     */
    public static function logFailedLogin(string $email, Request $request): void
    {
        try {
            // Trouver l'utilisateur par email
            $user = User::where('email', $email)->first();

            if ($user) {
                LoginActivity::create([
                    'user_id' => $user->id,
                    'action' => 'failed_login',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'location' => self::getLocation($request->ip()),
                    'device_type' => LoginActivity::getDeviceType($request->userAgent()),
                    'browser' => LoginActivity::getBrowser($request->userAgent()),
                    'os' => LoginActivity::getOS($request->userAgent()),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la tentative de connexion échouée', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enregistrer une réinitialisation de mot de passe
     */
    public static function logPasswordReset(User $user, Request $request): void
    {
        try {
            LoginActivity::create([
                'user_id' => $user->id,
                'action' => 'password_reset',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => self::getLocation($request->ip()),
                'device_type' => LoginActivity::getDeviceType($request->userAgent()),
                'browser' => LoginActivity::getBrowser($request->userAgent()),
                'os' => LoginActivity::getOS($request->userAgent()),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la réinitialisation de mot de passe', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enregistrer une expiration de session
     */
    public static function logSessionExpired(User $user, Request $request, $sessionId = null): void
    {
        try {
            LoginActivity::create([
                'user_id' => $user->id,
                'action' => 'session_expired',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => self::getLocation($request->ip()),
                'device_type' => LoginActivity::getDeviceType($request->userAgent()),
                'browser' => LoginActivity::getBrowser($request->userAgent()),
                'os' => LoginActivity::getOS($request->userAgent()),
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'expiration de session', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir la localisation depuis l'IP (basique)
     */
    private static function getLocation($ip): ?string
    {
        // Pour l'instant, on retourne null
        // Plus tard, on pourrait intégrer un service comme MaxMind ou IP2Location
        return null;
    }

    /**
     * Obtenir les statistiques d'activité pour un utilisateur
     */
    public static function getUserStats($userId, $days = 30): array
    {
        $startDate = now()->subDays($days);

        $activities = LoginActivity::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $stats = [
            'total_logins' => $activities->where('action', 'login')->count(),
            'total_logouts' => $activities->where('action', 'logout')->count(),
            'failed_logins' => $activities->where('action', 'failed_login')->count(),
            'password_resets' => $activities->where('action', 'password_reset')->count(),
            'session_expired' => $activities->where('action', 'session_expired')->count(),
            'unique_ips' => $activities->pluck('ip_address')->unique()->count(),
            'devices' => $activities->pluck('device_type')->unique()->values(),
            'browsers' => $activities->pluck('browser')->unique()->values(),
            'os_list' => $activities->pluck('os')->unique()->values(),
            'last_login' => $activities->where('action', 'login')->max('created_at'),
            'last_logout' => $activities->where('action', 'logout')->max('created_at'),
        ];

        // Calculer la durée moyenne des sessions
        $loginActivities = $activities->where('action', 'login');
        $totalDuration = 0;
        $sessionCount = 0;

        foreach ($loginActivities as $login) {
            $duration = $login->getSessionDuration();
            if ($duration !== null) {
                $totalDuration += $duration;
                $sessionCount++;
            }
        }

        $stats['average_session_duration'] = $sessionCount > 0 ? $totalDuration / $sessionCount : 0;
        $stats['average_session_duration_formatted'] = $sessionCount > 0
            ? self::formatDuration($totalDuration / $sessionCount)
            : 'N/A';

        return $stats;
    }

    /**
     * Obtenir les statistiques globales
     */
    public static function getGlobalStats($days = 30): array
    {
        $startDate = now()->subDays($days);

        $activities = LoginActivity::where('created_at', '>=', $startDate)->get();

        $stats = [
            'total_logins' => $activities->where('action', 'login')->count(),
            'total_logouts' => $activities->where('action', 'logout')->count(),
            'failed_logins' => $activities->where('action', 'failed_login')->count(),
            'password_resets' => $activities->where('action', 'password_reset')->count(),
            'session_expired' => $activities->where('action', 'session_expired')->count(),
            'unique_users' => $activities->pluck('user_id')->unique()->count(),
            'unique_ips' => $activities->pluck('ip_address')->unique()->count(),
            'top_devices' => $activities->pluck('device_type')->countBy()->sortDesc()->take(5),
            'top_browsers' => $activities->pluck('browser')->countBy()->sortDesc()->take(5),
            'top_os' => $activities->pluck('os')->countBy()->sortDesc()->take(5),
        ];

        // Activité par jour
        $dailyActivity = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        })->map(function ($dayActivities) {
            return [
                'logins' => $dayActivities->where('action', 'login')->count(),
                'logouts' => $dayActivities->where('action', 'logout')->count(),
                'failed_logins' => $dayActivities->where('action', 'failed_login')->count(),
            ];
        });

        $stats['daily_activity'] = $dailyActivity;

        return $stats;
    }

    /**
     * Formater une durée en secondes
     */
    private static function formatDuration($seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $secs);
        } else {
            return sprintf('%ds', $secs);
        }
    }
}
