<?php

namespace App\Services;

use App\Enums\ParameterDataType;
use App\Exceptions\MachineParameterException;
use App\Interfaces\MachineParameterInterface;
use App\Models\Machine;
use App\Models\MachineParameter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MachineParameterService implements MachineParameterInterface
{
    private const BOUND_COLUMNS = ['min', 'max', 'warning_min', 'warning_max', 'critical_min', 'critical_max'];

    private const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function sync(Machine $machine, array $rows): void
    {
        $existing = $machine->parameters()->get()->keyBy('id');

        $keptIds = [];

        foreach ($rows as $row) {
            if (! empty($row['id'])) {
                if (! $existing->has((int) $row['id'])) {
                    throw new MachineParameterException('A submitted parameter does not belong to this machine.');
                }

                $keptIds[] = (int) $row['id'];
            }
        }

        // Deleted first, so a removed parameter's key can be reused by a new row in the same
        // submission (the unique(machine_id, key) index would otherwise reject it).
        $machine->parameters()->whereNotIn('id', $keptIds)->delete();

        $usedKeys = $existing->only($keptIds)->pluck('key')->all();

        foreach (array_values($rows) as $position => $row) {
            if (! empty($row['id'])) {
                $parameter = $existing->get((int) $row['id']);

                $parameter->update(array_merge(
                    $this->editableAttributes($row, $parameter->data_type),
                    ['sort_order' => $position]
                ));

                continue;
            }

            $key = $this->resolveNewKey($row, $usedKeys);
            $usedKeys[] = $key;

            $type = ParameterDataType::tryFrom($row['data_type'] ?? '') ?? ParameterDataType::Number;

            $machine->parameters()->create(array_merge(
                $this->editableAttributes($row, $type),
                ['key' => $key, 'data_type' => $type->value, 'sort_order' => $position]
            ));
        }
    }

    public function copyableRows(Machine $source): array
    {
        return $source->parameters()->get()->map(fn (MachineParameter $parameter) => [
            'key' => $parameter->key,
            'label' => $parameter->label,
            'unit' => $parameter->unit,
            'data_type' => $parameter->data_type->value,
            'required' => $parameter->required,
            'min' => $this->boundOrNull($parameter->min),
            'max' => $this->boundOrNull($parameter->max),
            'warning_min' => $this->boundOrNull($parameter->warning_min),
            'warning_max' => $this->boundOrNull($parameter->warning_max),
            'critical_min' => $this->boundOrNull($parameter->critical_min),
            'critical_max' => $this->boundOrNull($parameter->critical_max),
        ])->values()->all();
    }

    public function readingRules(Machine $machine, string $logType): array
    {
        $parameters = $machine->parameters()->get();
        $knownKeys = $parameters->pluck('key')->all();

        $rules = [
            'readings' => ['nullable', 'array', function ($attribute, $value, $fail) use ($knownKeys) {
                $unknown = array_values(array_diff(array_map('strval', array_keys((array) $value)), $knownKeys));

                if ($unknown !== []) {
                    $fail($this->unknownKeysMessage($unknown, $knownKeys));
                }
            }],
        ];

        $attributes = [];

        foreach ($parameters as $parameter) {
            $presence = ($parameter->required && $logType === 'sensor_reading') ? 'required' : 'nullable';

            $rules["readings.{$parameter->key}"] = array_merge([$presence], $parameter->data_type->valueRules($parameter));
            $attributes["readings.{$parameter->key}"] = $parameter->label;
        }

        return [$rules, $attributes];
    }

    public function normalizeReadings(Machine $machine, array $readings): array
    {
        $result = [];

        foreach ($machine->parameters()->get() as $parameter) {
            $value = $readings[$parameter->key] ?? null;

            if ($value === null) {
                continue;
            }

            $result[$parameter->key] = $parameter->data_type->cast($value);
        }

        return $result;
    }

    public function apiExample(Machine $machine): array
    {
        $parameters = $machine->parameters()->get();

        $readings = [];

        foreach ($parameters as $parameter) {
            $readings[$parameter->key] = $parameter->data_type->example($parameter);
        }

        // An empty object rather than [] — a JSON array here would be a different, invalid shape.
        $body = [
            'log_type' => 'sensor_reading',
            'readings' => $readings === [] ? new \stdClass : $readings,
        ];

        $endpoint = route('api.machine-logs.store');

        $curl = "curl -X POST \"{$endpoint}\" \\\n"
            ."  -H \"X-Device-Key: YOUR_DEVICE_KEY\" \\\n"
            ."  -H \"Content-Type: application/json\" \\\n"
            ."  -H \"Accept: application/json\" \\\n"
            ."  -d '".json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."'";

        return [
            'endpoint' => $endpoint,
            'body_json' => json_encode($body, self::JSON_FLAGS),
            'curl' => $curl,
            'responses' => $this->exampleResponses($machine, $parameters, $body),
            'parameters' => $parameters,
        ];
    }

    /**
     * The attributes a row may change on an existing parameter — everything except `key` and
     * `data_type`. Min/max and threshold bounds are cleared for a type they don't apply to
     * (only a number has an ordering).
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function editableAttributes(array $row, ParameterDataType $type): array
    {
        $attributes = [
            'label' => trim((string) $row['label']),
            'unit' => isset($row['unit']) && trim((string) $row['unit']) !== '' ? trim((string) $row['unit']) : null,
            'required' => (bool) ($row['required'] ?? false),
        ];

        foreach (self::BOUND_COLUMNS as $column) {
            $value = $row[$column] ?? null;

            $attributes[$column] = $type->supportsThresholds() && $value !== null && $value !== ''
                ? (float) $value
                : null;
        }

        return $attributes;
    }

    /**
     * A key typed for a new row is kept as-is (and must be free); a blank one is generated from
     * the label, suffixed _2, _3… until it no longer clashes.
     *
     * @param  array<string, mixed>  $row
     * @param  list<string>  $usedKeys
     */
    private function resolveNewKey(array $row, array $usedKeys): string
    {
        $submitted = trim((string) ($row['key'] ?? ''));

        if ($submitted !== '') {
            if (in_array($submitted, $usedKeys, true)) {
                throw new MachineParameterException("The parameter key \"{$submitted}\" is already used by another parameter on this machine.");
            }

            return $submitted;
        }

        $base = Str::snake(Str::ascii((string) $row['label'])) ?: 'parameter';
        $key = $base;
        $suffix = 2;

        while (in_array($key, $usedKeys, true)) {
            $key = "{$base}_{$suffix}";
            $suffix++;
        }

        return $key;
    }

    private function boundOrNull(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * @param  list<string>  $unknown
     * @param  list<string>  $knownKeys
     */
    private function unknownKeysMessage(array $unknown, array $knownKeys): string
    {
        $accepted = $knownKeys === [] ? 'none — this machine has no parameters defined yet' : implode(', ', $knownKeys);

        return 'Unknown parameter(s): '.implode(', ', $unknown).". Accepted: {$accepted}.";
    }

    /**
     * @param  Collection<int, MachineParameter>  $parameters
     * @param  array<string, mixed>  $body
     * @return list<array{status: int, title: string, json: string}>
     */
    private function exampleResponses(Machine $machine, Collection $parameters, array $body): array
    {
        $unknownMessage = $this->unknownKeysMessage(['not_a_parameter'], $parameters->pluck('key')->all());

        $responses = [
            [
                'status' => 201,
                'title' => 'Created — the reading was stored',
                'json' => json_encode([
                    'success' => true,
                    'message' => 'Machine log created successfully.',
                    'data' => [
                        'machine_id' => $machine->id,
                        'log_type' => 'sensor_reading',
                        'readings' => $body['readings'],
                        'logged_at' => now()->toIso8601String(),
                        'id' => 1,
                    ],
                ], self::JSON_FLAGS),
            ],
            [
                'status' => 401,
                'title' => 'Unauthorized — the X-Device-Key header is missing or wrong',
                'json' => json_encode(['success' => false, 'message' => 'Invalid device key.'], self::JSON_FLAGS),
            ],
            [
                'status' => 422,
                'title' => 'Unprocessable — an unknown parameter key was sent',
                'json' => json_encode(['message' => $unknownMessage, 'errors' => ['readings' => [$unknownMessage]]], self::JSON_FLAGS),
            ],
        ];

        $firstNumber = $parameters->first(fn (MachineParameter $p) => $p->data_type === ParameterDataType::Number);

        if ($firstNumber) {
            $message = "The {$firstNumber->label} field must be a number.";

            $responses[] = [
                'status' => 422,
                'title' => 'Unprocessable — a value has the wrong type (or is outside min/max)',
                'json' => json_encode(['message' => $message, 'errors' => ["readings.{$firstNumber->key}" => [$message]]], self::JSON_FLAGS),
            ];
        }

        $firstRequired = $parameters->first(fn (MachineParameter $p) => $p->required);

        if ($firstRequired) {
            $message = "The {$firstRequired->label} field is required.";

            $responses[] = [
                'status' => 422,
                'title' => 'Unprocessable — a required parameter is missing from a sensor_reading',
                'json' => json_encode(['message' => $message, 'errors' => ["readings.{$firstRequired->key}" => [$message]]], self::JSON_FLAGS),
            ];
        }

        return $responses;
    }
}
