<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class XenditSmoke extends BaseCommand
{
    protected $group = 'Payments';
    protected $name = 'xendit:smoke';
    protected $description = 'Build a sample Xendit payload and show internal status mappings.';
    protected $usage = 'xendit:smoke [payload <courseId> <userId> <amount> <historicalAttempts>] | [map <status> [event]]';
    protected $arguments = [
        'payload' => 'Builds a sample payload. Positional order: courseId userId amount historicalAttempts.',
        'map' => 'Normalizes a provider status/event. Positional order: status [event].',
    ];
    protected $options = [
        '--course' => 'Fallback optional course ID for payload mode when positional args are omitted.',
        '--user' => 'Fallback optional user ID for payload mode when positional args are omitted.',
        '--amount' => 'Fallback optional amount for payload mode when positional args are omitted.',
        '--attempts' => 'Fallback optional historical attempt count for payload mode when positional args are omitted.',
        '--status' => 'Fallback optional provider status for map mode when positional args are omitted.',
        '--event' => 'Fallback optional provider event name for map mode when positional args are omitted.',
    ];

    public function run(array $params)
    {
        /** @var \App\Services\Payments\XenditPaymentLinkService $service */
        $service = service('xenditPaymentLinks');

        $mode = $params[0] ?? 'payload';

        if ($mode === 'map') {
            $status = $params[1] ?? $this->optionValue($params, 'status');
            $event = $params[2] ?? $this->optionValue($params, 'event');

            CLI::write(json_encode([
                'input_status' => $status,
                'input_event' => $event,
                'mapped_status' => $service->mapToInternalStatus(
                    is_string($status) ? $status : null,
                    is_string($event) ? $event : null
                ),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

            return;
        }

        $courseId = (int) ($params[1] ?? $this->optionValue($params, 'course', 101));
        $userId = (int) ($params[2] ?? $this->optionValue($params, 'user', 7));
        $amount = (int) ($params[3] ?? $this->optionValue($params, 'amount', 250000));
        $attempts = max(0, (int) ($params[4] ?? $this->optionValue($params, 'attempts', 0)));
        $xenditConfig = config('Xendit');

        $payload = $service->buildPaymentLinkPayload([
            'course_id' => $courseId,
            'user_id' => $userId,
            'amount' => $amount,
            'course_title' => 'Premium Course Smoke Test',
            'customer_email' => 'student@example.com',
            'customer_name' => 'Smoke Test User',
            'customer_phone' => '+6281234567890',
            'historical_attempt_count' => $attempts,
            'success_redirect_url' => $xenditConfig->successRedirectUrl,
            'failure_redirect_url' => $xenditConfig->failureRedirectUrl,
            'metadata' => [
                'smoke' => true,
            ],
        ]);

        CLI::write(json_encode([
            'reference_code' => $payload['external_id'],
            'payload' => $payload,
            'status_mapping_samples' => $service->getStatusMapper()->sampleMappings(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');
    }

    private function optionValue(array $params, string $option, mixed $default = null): mixed
    {
        $cliOption = CLI::getOption($option);

        if ($cliOption !== null) {
            return $cliOption;
        }

        $prefix = '--' . $option . '=';

        foreach ($params as $index => $param) {
            if (!is_string($param)) {
                continue;
            }

            if (str_starts_with($param, $prefix)) {
                return substr($param, strlen($prefix));
            }

            if ($param === '--' . $option) {
                return $params[$index + 1] ?? $default;
            }
        }

        return $default;
    }
}
