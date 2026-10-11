<?php

namespace App\Support;

use Sentry\Event;
use Sentry\EventHint;

final class SentryBeforeSend
{
    public static function handle(Event $event, ?EventHint $hint): ?Event
    {
        $sensitiveKey = static fn (string $key): bool => (bool) preg_match(
            '/password|token|secret|recovery[_-]?code|authorization|cookie|session/i',
            $key
        );
        $scrubValue = static function (array $value) use (&$scrubValue, $sensitiveKey): array {
            $safe = [];
            foreach ($value as $key => $item) {
                if ($sensitiveKey((string) $key)) {
                    continue;
                }
                $safe[$key] = is_array($item) ? $scrubValue($item) : $item;
            }

            return $safe;
        };

        $request = $event->getRequest();
        $safeRequest = [];
        if (isset($request['url'])) {
            $safeRequest['url'] = preg_replace('/\?.*$/', '', (string) $request['url']);
        }
        if (isset($request['method'])) {
            $safeRequest['method'] = $request['method'];
        }
        if (isset($request['query_string'])) {
            $safeRequest['query_string'] = is_array($request['query_string'])
                ? $scrubValue($request['query_string'])
                : $request['query_string'];
        }

        $safeBreadcrumbs = [];
        foreach ($event->getBreadcrumbs() as $breadcrumb) {
            $metadata = $breadcrumb->getMetadata();
            $safeMetadata = $scrubValue($metadata);
            $sanitized = $breadcrumb;
            foreach (array_keys($metadata) as $key) {
                $sanitized = $sanitized->withoutMetadata((string) $key);
            }
            foreach ($safeMetadata as $key => $value) {
                $sanitized = $sanitized->withMetadata((string) $key, $value);
            }
            $safeBreadcrumbs[] = $sanitized;
        }

        $event->setRequest($safeRequest)
            ->setUser(null)
            ->setExtra([])
            ->setBreadcrumb($safeBreadcrumbs);

        return $event;
    }
}
