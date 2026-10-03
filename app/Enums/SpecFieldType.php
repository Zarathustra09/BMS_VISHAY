<?php

namespace App\Enums;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * The kinds of specification field a Machine Type's builder can define — replaces the old
 * text-or-number-only choice. Each kind decides which of the builder's kind-specific settings
 * apply (settings()), whether "Required" is meaningful for it (supportsRequired()), how a
 * submitted machine value is validated (valueRules()) and cast for storage (cast()), and how a
 * stored value is rendered back to a human (display()).
 */
enum SpecFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Select = 'select';
    case Multiselect = 'multiselect';
    case Boolean = 'boolean';
    case Date = 'date';
    case Url = 'url';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Textarea => 'Long Text',
            self::Number => 'Number',
            self::Select => 'Dropdown',
            self::Multiselect => 'Multi-select',
            self::Boolean => 'Yes / No',
            self::Date => 'Date',
            self::Url => 'URL',
            self::Email => 'Email',
        };
    }

    /**
     * Which of the builder's kind-specific settings apply to this kind — used both to
     * validate what's submitted and to decide which inputs the builder UI shows.
     *
     * @return list<string>
     */
    public function settings(): array
    {
        return match ($this) {
            self::Text, self::Textarea, self::Url, self::Email => ['placeholder'],
            self::Number => ['unit', 'step', 'min', 'max'],
            self::Select, self::Multiselect => ['options'],
            self::Boolean, self::Date => [],
        };
    }

    public function supports(string $setting): bool
    {
        return in_array($setting, $this->settings(), true);
    }

    /**
     * A Yes/No value is always present (the machine form's toggle always submits 0 or 1),
     * so "Required" has nothing to enforce for it — the builder hides that checkbox for this
     * kind, and MachineTypeService forces it to false regardless of what's posted.
     */
    public function supportsRequired(): bool
    {
        return $this !== self::Boolean;
    }

    /**
     * The `specifications.{key}` validation rules for a machine submitting a value for this
     * field. Keyed by path suffix relative to `specifications.{key}` — `''` is the field's own
     * path, `'*'` (multiselect only) is each selected option.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, list<mixed>>
     */
    public function valueRules(array $field, bool $required): array
    {
        $req = $required ? 'required' : 'nullable';

        return match ($this) {
            self::Text => ['' => [$req, 'string', 'max:255']],
            self::Textarea => ['' => [$req, 'string', 'max:5000']],
            self::Url => ['' => [$req, 'url:http,https', 'max:255']],
            self::Email => ['' => [$req, 'email', 'max:255']],
            self::Number => ['' => array_values(array_filter([
                $req,
                'numeric',
                isset($field['min']) && $field['min'] !== null && $field['min'] !== ''
                    ? "min:{$field['min']}" : null,
                isset($field['max']) && $field['max'] !== null && $field['max'] !== ''
                    ? "max:{$field['max']}" : null,
            ]))],
            self::Select => ['' => [$req, Rule::in($field['options'] ?? [])]],
            self::Multiselect => [
                '' => array_merge(['array'], $required ? ['min:1'] : []),
                '*' => [Rule::in($field['options'] ?? [])],
            ],
            self::Boolean => ['' => ['boolean']],
            self::Date => ['' => [$req, 'date']],
        };
    }

    /**
     * Casts a submitted (already-validated) value to the shape stored in a machine's
     * `specifications` JSON.
     */
    public function cast(mixed $value): mixed
    {
        return match ($this) {
            self::Number => is_numeric($value) ? (float) $value : null,
            self::Boolean => $this->truthy($value),
            self::Multiselect => is_array($value)
                ? array_values(array_filter($value, fn ($v) => $v !== null && $v !== ''))
                : [],
            default => (string) $value,
        };
    }

    /**
     * Formats a stored value for display (the machine show page, the AI context). Tolerates a
     * legacy or mismatched value — e.g. a "Yes"/"No" string left over from before a field was
     * converted from Text to Boolean — rather than erroring on it.
     */
    public function display(mixed $value): string
    {
        return match ($this) {
            self::Boolean => $this->truthy($value) ? 'Yes' : 'No',
            self::Multiselect => is_array($value) ? implode(', ', $value) : (string) $value,
            self::Date => $value ? Carbon::parse((string) $value)->format('M d, Y') : '',
            default => (string) $value,
        };
    }

    /**
     * Whether $value counts as "nothing stored" for this kind — a stored `false` is a real
     * Yes/No answer, not an absent one, so only a genuinely-unset boolean is empty; an empty
     * multiselect (no options chosen) is empty; every other kind is empty on null or ''.
     */
    public function isEmpty(mixed $value): bool
    {
        return match ($this) {
            self::Boolean => $value === null,
            self::Multiselect => empty($value),
            default => $value === null || $value === '',
        };
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    /**
     * @return array<string, string> value => label, for populating the builder's Kind <select>
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
