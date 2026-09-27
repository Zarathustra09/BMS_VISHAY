<?php

namespace App\Services;

use App\Exceptions\MachineTypeActionException;
use App\Interfaces\MachineTypeInterface;
use App\Models\Machine;
use App\Models\MachineType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MachineTypeService implements MachineTypeInterface
{
    public function getAllWithMachineCounts(): Collection
    {
        return MachineType::withCount('machines')->orderBy('name')->get();
    }

    public function getForSelect(): Collection
    {
        return MachineType::select('id', 'name', 'icon', 'spec_fields')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): MachineType
    {
        return MachineType::findOrFail($id);
    }

    public function create(array $data): MachineType
    {
        $data['spec_fields'] = $this->normalizeFields($data['spec_fields'] ?? [], []);

        return MachineType::create($data);
    }

    public function update(MachineType $machineType, array $data): MachineType
    {
        $existingKeys = collect($machineType->specFields())->pluck('key')->all();
        $submittedFields = $data['spec_fields'] ?? [];

        // A key is generated once, when its field is first created, and is meant to never
        // change again — it's what a machine's `specifications` JSON is indexed by. Reusing a
        // removed field's key for an unrelated new field would silently graft old data onto
        // it; posting back an existing field under a key that isn't its own would silently
        // rewrite what that key means for every machine already using it. Both are rejected
        // rather than allowed to corrupt existing machine data.
        $submittedExistingKeys = collect($submittedFields)
            ->pluck('key')
            ->filter(fn ($key) => $key !== null && $key !== '')
            ->all();

        foreach ($submittedExistingKeys as $key) {
            if (! in_array($key, $existingKeys, true)) {
                throw new MachineTypeActionException(
                    "Field key \"{$key}\" does not belong to an existing field on this type."
                );
            }
        }

        $data['spec_fields'] = $this->normalizeFields($submittedFields, $existingKeys);

        $machineType->update($data);

        return $machineType->fresh();
    }

    public function delete(MachineType $machineType): bool
    {
        if ($this->isInUse($machineType)) {
            $count = $machineType->machines()->withTrashed()->count();

            throw new MachineTypeActionException(
                "Cannot delete \"{$machineType->name}\" — {$count} machine(s) still use this type. Reassign them to a different type first."
            );
        }

        return $machineType->delete();
    }

    public function isInUse(MachineType $machineType): bool
    {
        // withTrashed(): the machine_type_id FK uses restrictOnDelete(), which the database
        // enforces regardless of Machine's own soft-delete state — a soft-deleted machine
        // still counts as "in use" for this check.
        return Machine::withTrashed()->where('machine_type_id', $machineType->id)->exists();
    }

    /**
     * Generates a key for any new field (one with none posted back), keeps every existing
     * field's key exactly as it was, strips number-only attributes (unit/step/min) from text
     * fields, and re-indexes the result as a plain list.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, string>  $existingKeys
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFields(array $fields, array $existingKeys): array
    {
        $usedKeys = [];

        $normalized = [];

        foreach ($fields as $field) {
            $key = $field['key'] ?? null;

            if (! $key || ! in_array($key, $existingKeys, true)) {
                $key = $this->uniqueKey($field['label'], array_merge($existingKeys, $usedKeys));
            }

            $usedKeys[] = $key;

            $type = $field['type'] === 'number' ? 'number' : 'text';

            $row = [
                'key' => $key,
                'label' => $field['label'],
                'type' => $type,
                'required' => (bool) ($field['required'] ?? false),
            ];

            if ($type === 'number') {
                $row['unit'] = $field['unit'] ?? null;
                $row['step'] = $field['step'] ?? null;
                $row['min'] = isset($field['min']) && $field['min'] !== '' ? (float) $field['min'] : null;
            }

            $normalized[] = $row;
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>  $reservedKeys
     */
    private function uniqueKey(string $label, array $reservedKeys): string
    {
        $base = Str::snake(Str::ascii($label)) ?: 'field';
        $key = $base;
        $suffix = 2;

        while (in_array($key, $reservedKeys, true)) {
            $key = "{$base}_{$suffix}";
            $suffix++;
        }

        return $key;
    }
}
