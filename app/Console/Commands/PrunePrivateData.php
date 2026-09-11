<?php

namespace App\Console\Commands;

use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\UserSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrunePrivateData extends Command
{
    protected $signature = 'system:privacy-prune {--dry-run : Report records without deleting them}';

    protected $description = 'Apply configured retention periods to temporary security and notification data';

    public function handle(): int
    {
        $targets = [
            'login_attempts' => [
                LoginAttempt::where('created_at', '<', now()->subDays((int) config('governance.login_audit_retention_days', 365))),
                'delete',
            ],
            'revoked_sessions' => [
                UserSession::whereNotNull('revoked_at')
                    ->where('revoked_at', '<', now()->subDays((int) config('governance.revoked_session_retention_days', 90))),
                'delete',
            ],
            'old_notifications' => [
                Notification::where('created_at', '<', now()->subDays((int) config('governance.notification_retention_days', 730)))
                    ->whereIn('status', ['sent', 'failed']),
                'delete',
            ],
        ];

        foreach ($targets as $label => [$query]) {
            $count = (clone $query)->count();
            $this->line("{$label}: {$count}");
            if (! $this->option('dry-run') && $count > 0) {
                $query->delete();
            }
        }

        $expiredTokens = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(24));
        $tokenCount = (clone $expiredTokens)->count();
        $this->line("expired_password_tokens: {$tokenCount}");
        if (! $this->option('dry-run') && $tokenCount > 0) {
            $expiredTokens->delete();
        }

        $this->info($this->option('dry-run') ? 'Dry run completed; no records were deleted.' : 'Privacy retention completed.');

        return self::SUCCESS;
    }
}
