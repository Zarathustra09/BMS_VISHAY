@php
    $checkedNames = $checkedNames ?? [];
    $readonly = $readonly ?? false;
    $actions = ['view', 'create', 'update', 'delete'];
@endphp

<div class="overflow-x-auto">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>Module</th>
                @foreach($actions as $action)
                    <th class="text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="capitalize">{{ $action }}</span>
                            @unless($readonly)
                                <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="togglePermissionColumn('{{ $action }}')">Toggle</button>
                            @endunless
                        </div>
                    </th>
                @endforeach
                <th>Other</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedPermissions as $module => $actionsMap)
            <tr>
                <td class="font-medium">
                    <div class="flex items-center gap-2">
                        <span class="capitalize">{{ str_replace('-', ' ', $module) }}</span>
                        @unless($readonly)
                            <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="togglePermissionRow('{{ $module }}')">Toggle row</button>
                        @endunless
                    </div>
                </td>
                @foreach($actions as $action)
                    <td class="text-center">
                        @if(isset($actionsMap[$action]))
                            @php $perm = $actionsMap[$action]; @endphp
                            <input type="checkbox"
                                   class="checkbox checkbox-sm"
                                   name="permissions[]"
                                   value="{{ $perm->name }}"
                                   data-module="{{ $module }}"
                                   data-action="{{ $action }}"
                                   {{ in_array($perm->name, $checkedNames) ? 'checked' : '' }}
                                   {{ $readonly ? 'disabled' : '' }} />
                        @else
                            <span class="opacity-30">—</span>
                        @endif
                    </td>
                @endforeach
                <td>
                    @if(!empty($actionsMap['other']))
                        <div class="flex flex-col gap-1">
                            @foreach($actionsMap['other'] as $perm)
                                <label class="label cursor-pointer justify-start gap-2 py-0">
                                    <input type="checkbox"
                                           class="checkbox checkbox-xs"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           data-module="{{ $module }}"
                                           data-action="other"
                                           {{ in_array($perm->name, $checkedNames) ? 'checked' : '' }}
                                           {{ $readonly ? 'disabled' : '' }} />
                                    <span class="text-xs opacity-80">{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <span class="opacity-30">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@unless($readonly)
<div class="flex justify-end mt-2">
    <button type="button" class="btn btn-ghost btn-xs" onclick="toggleAllPermissions()">Select / Deselect All</button>
</div>

@once
    @push('scripts')
    <script>
        function togglePermissionColumn(action) {
            const boxes = [...document.querySelectorAll(`input[data-action="${action}"]`)].filter(b => !b.disabled);
            const shouldCheck = !boxes.every(b => b.checked);
            boxes.forEach(b => b.checked = shouldCheck);
        }
        function togglePermissionRow(module) {
            const boxes = [...document.querySelectorAll(`input[data-module="${module}"]`)].filter(b => !b.disabled);
            const shouldCheck = !boxes.every(b => b.checked);
            boxes.forEach(b => b.checked = shouldCheck);
        }
        function toggleAllPermissions() {
            const boxes = [...document.querySelectorAll('input[name="permissions[]"]')].filter(b => !b.disabled);
            const shouldCheck = !boxes.every(b => b.checked);
            boxes.forEach(b => b.checked = shouldCheck);
        }
    </script>
    @endpush
@endonce
@endunless
