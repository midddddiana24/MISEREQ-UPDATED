<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sentry\State\HubInterface;

use function Sentry\captureMessage;
use function Sentry\flush;

class SentryStatus extends Command
{
    protected $signature = 'sentry:status';

    protected $description = 'Send one gated Sentry configuration test event';

    public function handle(HubInterface $hub): int
    {
        if (! (bool) env('SENTRY_STATUS_CHECK_ENABLED', false)) {
            $this->error('Sentry status checks are disabled.');

            return self::FAILURE;
        }
        if (! in_array(config('app.env'), ['local', 'testing'], true)) {
            $this->error('Sentry status checks are allowed only in local or testing environments.');

            return self::FAILURE;
        }

        $client = $hub->getClient();
        $options = $client?->getOptions();
        if (! $options || ! $options->getDsn()) {
            $this->error('Sentry is not configured with a DSN.');

            return self::FAILURE;
        }

        captureMessage('MIS Service Request Sentry status check');
        flush();

        $this->table(['Setting', 'Value'], [
            ['configured', $options && $options->getDsn() ? 'yes' : 'no'],
            ['environment', $options?->getEnvironment() ?: 'not set'],
            ['release', $options?->getRelease() ?: 'not set'],
            ['default PII', $options && $options->shouldSendDefaultPii() ? 'enabled' : 'disabled'],
            ['test event sent', 'yes'],
        ]);

        return self::SUCCESS;
    }
}
