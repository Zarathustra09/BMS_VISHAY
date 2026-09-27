<?php

namespace App\Interfaces;

use App\Models\MachineType;
use Illuminate\Support\Collection;

interface MachineTypeInterface
{
    /**
     * Every type, with a count of machines currently assigned to it — for the index list.
     */
    public function getAllWithMachineCounts(): Collection;

    /**
     * id/name/icon/spec_fields for every type, ordered by name — for the machine form's
     * Type select and its Alpine spec-field map.
     */
    public function getForSelect(): Collection;

    public function findById(int $id): MachineType;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MachineType;

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws \App\Exceptions\MachineTypeActionException if a submitted spec field's key
     *                                                    would change what an existing field's key already means (see MachineTypeService).
     */
    public function update(MachineType $machineType, array $data): MachineType;

    /**
     * @throws \App\Exceptions\MachineTypeActionException if the type is still assigned to
     *                                                    any machine, including a soft-deleted one.
     */
    public function delete(MachineType $machineType): bool;

    /**
     * Whether any machine — including a soft-deleted one — is currently assigned this type.
     * Soft-deleted machines count because the `machine_type_id` foreign key uses
     * restrictOnDelete(), which the database enforces regardless of Machine's soft-delete
     * state.
     */
    public function isInUse(MachineType $machineType): bool;
}
