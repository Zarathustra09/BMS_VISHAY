<?php

namespace App\Services;

use App\Enums\SpecFieldType;
use App\Interfaces\MachineInterface;
use App\Interfaces\SettingInterface;
use App\Models\Alert;
use App\Models\Floor;
use App\Models\Machine;
use App\Models\MachineType;
use App\Models\SensorThreshold;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class MachineService implements MachineInterface
{
    public function __construct(protected SettingInterface $settings) {}

    public function getQueryForDataTable(array $filters): Builder
    {
        $query = Machine::with(['building', 'floor', 'machineType']);

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['machine_type_id']) && $filters['machine_type_id'] !== 'all') {
            $query->where('machine_type_id', $filters['machine_type_id']);
        }

        if (! empty($filters['building_id']) && $filters['building_id'] !== 'all') {
            $query->where('building_id', $filters['building_id']);
        }

        if (! empty($filters['floor_id']) && $filters['floor_id'] !== 'all') {
            $query->where('floor_id', $filters['floor_id']);
        }

        return $query;
    }

    public function getStats(): array
    {
        return [
            'total' => Machine::count(),
            'active' => Machine::where('status', 'active')->count(),
            'maintenance' => Machine::where('status', 'maintenance')->count(),
            'fault' => Machine::where('status', 'fault')->count(),
            'inactive' => Machine::where('status', 'inactive')->count(),
            'critical' => Machine::where('is_critical', true)->count(),
        ];
    }

    public function findById(int $id): Machine
    {
        return Machine::with(['building', 'floor', 'machineType'])->findOrFail($id);
    }

    public function getLogStats(Machine $machine): array
    {
        return [
            'total' => $machine->logs()->count(),
            'today' => $machine->logs()->whereDate('logged_at', today())->count(),
            'week' => $machine->logs()->where('logged_at', '>=', now()->subWeek())->count(),
            'critical' => $machine->logs()->where('severity', 'critical')->count(),
            'errors' => $machine->logs()->where('severity', 'error')->count(),
            'warnings' => $machine->logs()->where('severity', 'warning')->count(),
        ];
    }

    public function getRecentAlerts(Machine $machine, int $limit = 10): Collection
    {
        return $machine->logs()
            ->whereIn('severity', ['critical', 'error'])
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLogTypeDistribution(Machine $machine): Collection
    {
        return $machine->logs()
            ->selectRaw('log_type, COUNT(*) as count')
            ->groupBy('log_type')
            ->pluck('count', 'log_type');
    }

    public function getActivityFeed(Machine $machine, int $limit = 20): Collection
    {
        $workOrderIds = $machine->workOrders()->pluck('id');
        $alertIds = $machine->alerts()->pluck('id');

        // Keyed by the machine's *current* type — if a change was made under a since-replaced
        // type, its keys just fall back to a de-slugged raw key and plain text formatting
        // instead of the field's real label/kind, which is an acceptable simplification for a
        // display-only feed.
        $specFieldsByKey = collect($machine->machineType?->specFields() ?? [])->keyBy('key');

        return Activity::query()
            ->where(function (Builder $query) use ($machine, $workOrderIds, $alertIds) {
                $query->where(fn ($q) => $q->where('subject_type', Machine::class)->where('subject_id', $machine->id))
                    ->orWhere(fn ($q) => $q->where('subject_type', WorkOrder::class)->whereIn('subject_id', $workOrderIds))
                    ->orWhere(fn ($q) => $q->where('subject_type', Alert::class)->whereIn('subject_id', $alertIds));
            })
            ->with('causer')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Activity $activity) => $this->expandSpecificationChanges($activity, $specFieldsByKey));
    }

    public function create(array $data): Machine
    {
        $data = $this->withNormalizedSpecifications($data);

        $machine = Machine::create($data);

        $this->applyDefaultThresholds($machine);

        return $machine;
    }

    public function update(Machine $machine, array $data): Machine
    {
        $data = $this->withNormalizedSpecifications($data);

        $machine->update($data);

        return $machine->fresh();
    }

    /**
     * The specs a machine's show page renders for its current type — [label, value, unit,
     * type] per field with a stored value, formatted via SpecFieldType::display() (a boolean
     * renders as Yes/No, a multiselect as a comma-joined list, a date as "M d, Y", etc.).
     * `power_rating`/`spec_voltage` are real columns on Machine and aren't part of this; the
     * view renders them separately. `type` lets the view render a `url` value as a link.
     *
     * @return list<array{label: string, value: string, unit: ?string, type: string}>
     */
    public function specificationRows(Machine $machine): array
    {
        $specs = $machine->specifications ?? [];
        $rows = [];

        foreach ($machine->machineType?->specFields() ?? [] as $field) {
            $kind = SpecFieldType::tryFrom($field['type'] ?? '') ?? SpecFieldType::Text;
            $value = $specs[$field['key']] ?? null;

            if ($kind->isEmpty($value)) {
                continue;
            }

            $rows[] = [
                'label' => $field['label'],
                'value' => $kind->display($value),
                'unit' => $field['unit'] ?? null,
                'type' => $kind->value,
            ];
        }

        return $rows;
    }

    public function delete(Machine $machine): bool
    {
        return $machine->delete();
    }

    public function floorBelongsToBuilding(int $floorId, ?int $buildingId): bool
    {
        return Floor::where('id', $floorId)
            ->when($buildingId, fn (Builder $query) => $query->where('building_id', $buildingId))
            ->exists();
    }

    /**
     * Applies Settings' default_thresholds (configured on the Settings page) to a
     * newly-created machine — existing machines are never touched by this, only new ones.
     */
    private function applyDefaultThresholds(Machine $machine): void
    {
        $defaults = $this->settings->get('default_thresholds', []);

        foreach ($defaults as $metric => $bounds) {
            SensorThreshold::create(array_merge(
                ['machine_id' => $machine->id, 'metric' => $metric],
                $bounds
            ));
        }
    }

    /**
     * Rebuilds $data['specifications'] against the selected type's own field keys, so a
     * request carrying another type's keys — or a key that doesn't belong to any field on the
     * chosen type at all — can never reach storage. This is the actual enforcement; the
     * controller's validation rules only shape what a well-formed request looks like.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withNormalizedSpecifications(array $data): array
    {
        if (! array_key_exists('machine_type_id', $data)) {
            return $data;
        }

        $type = MachineType::find($data['machine_type_id']);

        $data['specifications'] = $type
            ? $this->normalizeSpecifications($type, $data['specifications'] ?? [])
            : [];

        return $data;
    }

    /**
     * Keeps only $type's own field keys, drops an empty value, and casts each value per its
     * field's kind (SpecFieldType::cast()) — a number to a float, a multiselect to a plain
     * list, etc. Everything else in $input (another type's keys, or junk from a hand-crafted
     * request) is silently discarded. A boolean is always stored, even when false — unlike
     * every other kind, false is a real answer, not an absent one.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalizeSpecifications(MachineType $type, array $input): array
    {
        $result = [];

        foreach ($type->specFields() as $field) {
            $kind = SpecFieldType::tryFrom($field['type'] ?? '') ?? SpecFieldType::Text;
            $value = $input[$field['key']] ?? null;

            if ($kind === SpecFieldType::Boolean) {
                $result[$field['key']] = $kind->cast($value);

                continue;
            }

            if ($kind === SpecFieldType::Multiselect) {
                $cast = $kind->cast($value);

                if (! empty($cast)) {
                    $result[$field['key']] = $cast;
                }

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $result[$field['key']] = $kind->cast($value);
        }

        return $result;
    }

    /**
     * If this activity's diff touched `specifications` (a single old/new pair of JSON
     * objects/arrays), expands it into one old/new pair per changed spec key, labelled and
     * formatted per the field's kind (SpecFieldType::display()) — the Activity tab's generic
     * "field: old → new" rendering (machines/show.blade.php) otherwise has to `{{ }}`-echo a
     * raw array or boolean, which throws.
     */
    private function expandSpecificationChanges(Activity $activity, Collection $specFieldsByKey): Activity
    {
        $attributes = $activity->properties->get('attributes');

        if (! is_array($attributes) || ! array_key_exists('specifications', $attributes)) {
            return $activity;
        }

        $old = $activity->properties->get('old', []);
        $newSpecs = (array) ($attributes['specifications'] ?? []);
        $oldSpecs = (array) ($old['specifications'] ?? []);

        unset($attributes['specifications'], $old['specifications']);

        foreach (array_unique(array_merge(array_keys($newSpecs), array_keys($oldSpecs))) as $key) {
            $newValue = $newSpecs[$key] ?? null;
            $oldValue = $oldSpecs[$key] ?? null;

            if ($newValue === $oldValue) {
                continue;
            }

            $field = $specFieldsByKey->get($key);
            $kind = SpecFieldType::tryFrom($field['type'] ?? '') ?? SpecFieldType::Text;
            $label = $field['label'] ?? str_replace('_', ' ', $key);

            $attributes[$label] = $newValue === null ? '—' : $kind->display($newValue);
            $old[$label] = $oldValue === null ? '—' : $kind->display($oldValue);
        }

        $activity->properties = collect(['attributes' => $attributes, 'old' => $old]);

        return $activity;
    }
}
