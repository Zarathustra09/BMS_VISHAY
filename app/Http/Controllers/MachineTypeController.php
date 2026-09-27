<?php

namespace App\Http\Controllers;

use App\Enums\MachineTypeIcon;
use App\Exceptions\MachineTypeActionException;
use App\Interfaces\MachineTypeInterface;
use App\Models\MachineType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MachineTypeController extends Controller
{
    public function __construct(protected MachineTypeInterface $machineTypeService)
    {
        $this->middleware('permission:machine-types.view')->only(['index']);
        $this->middleware('permission:machine-types.create')->only(['create', 'store']);
        $this->middleware('permission:machine-types.update')->only(['edit', 'update']);
        $this->middleware('permission:machine-types.delete')->only(['destroy', 'confirmDelete']);
    }

    public function index()
    {
        $machineTypes = $this->machineTypeService->getAllWithMachineCounts();

        return view('machine_types.index', compact('machineTypes'));
    }

    public function create()
    {
        return view('machine_types.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateMachineType($request);

        $machineType = $this->machineTypeService->create($validated);

        return redirect()->route('machine-types.index')
            ->with('success', "Machine type \"{$machineType->name}\" created successfully.");
    }

    // No show page — a type is small reference data and its edit page already shows
    // everything about it (docs/08-frontend.md's "good reason" exception, same as users/).

    public function edit(MachineType $machineType)
    {
        $inUse = $this->machineTypeService->isInUse($machineType);

        return view('machine_types.edit', compact('machineType', 'inUse'));
    }

    public function update(Request $request, MachineType $machineType)
    {
        $validated = $this->validateMachineType($request, $machineType);

        try {
            $this->machineTypeService->update($machineType, $validated);
        } catch (MachineTypeActionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('machine-types.index')
            ->with('success', "Machine type \"{$machineType->name}\" updated successfully.");
    }

    public function confirmDelete(MachineType $machineType)
    {
        $inUse = $this->machineTypeService->isInUse($machineType);

        return view('machine_types.delete', compact('machineType', 'inUse'));
    }

    public function destroy(MachineType $machineType)
    {
        try {
            $name = $machineType->name;
            $this->machineTypeService->delete($machineType);
        } catch (MachineTypeActionException $e) {
            return redirect()->route('machine-types.index')->with('error', $e->getMessage());
        }

        return redirect()->route('machine-types.index')
            ->with('success', "Machine type \"{$name}\" deleted successfully.");
    }

    private function validateMachineType(Request $request, ?MachineType $machineType = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('machine_types', 'name')->ignore($machineType?->id),
            ],
            'icon' => ['required', Rule::enum(MachineTypeIcon::class)],
            'description' => 'nullable|string|max:1000',
            'spec_fields' => 'nullable|array',
            'spec_fields.*.label' => 'required|string|max:100',
            'spec_fields.*.key' => 'nullable|string|max:60|regex:/^[a-z][a-z0-9_]*$/',
            'spec_fields.*.type' => 'required|in:text,number',
            'spec_fields.*.unit' => 'nullable|string|max:20',
            'spec_fields.*.step' => 'nullable|numeric|gt:0',
            'spec_fields.*.min' => 'nullable|numeric',
            'spec_fields.*.required' => 'boolean',
        ], [], [
            'spec_fields.*.label' => 'field label',
            'spec_fields.*.type' => 'field kind',
        ]);

        // Two labels that would generate the same key (e.g. two fields both labelled
        // "Capacity") would silently collide once MachineTypeService derives a key from
        // each — caught here as a shape problem, before the request ever reaches the service.
        $validator->after(function ($validator) use ($request) {
            $labels = collect($request->input('spec_fields', []))
                ->pluck('label')
                ->filter()
                ->map(fn ($label) => Str::snake(Str::ascii($label)));

            $duplicates = $labels->duplicates();

            foreach ($duplicates as $index => $key) {
                $validator->errors()->add(
                    "spec_fields.{$index}.label",
                    'Two fields generate the same key — give each field a distinct label.'
                );
            }
        });

        return $validator->validate();
    }
}
