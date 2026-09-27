<?php

namespace App\Enums;

/**
 * A predetermined set of icons a machine type can use — replaces free-text emoji entry.
 * Every path is Heroicons v1 "outline" (MIT license, Tailwind Labs), the same icon family
 * already used everywhere else in this app (sidebar, header, buttons — see e.g. Cog below,
 * which is the exact path used for the Settings link in layouts/header.blade.php). Rendered
 * by <x-machine-type-icon> as `fill="none" stroke="currentColor" viewBox="0 0 24 24"` with
 * `stroke-width="2"` on each path, matching every other icon in the app so a type's icon
 * follows the current theme color instead of being a fixed-color emoji glyph.
 */
enum MachineTypeIcon: string
{
    case LightningBolt = 'lightning-bolt';
    case Fire = 'fire';
    case Sun = 'sun';
    case Cloud = 'cloud';
    case Beaker = 'beaker';
    case Cube = 'cube';
    case Cog = 'cog';
    case Chip = 'chip';
    case Database = 'database';
    case LightBulb = 'light-bulb';
    case Refresh = 'refresh';
    case SwitchVertical = 'switch-vertical';
    case Truck = 'truck';
    case Filter = 'filter';
    case ShieldCheck = 'shield-check';
    case Sparkles = 'sparkles';
    case Globe = 'globe';
    case Wifi = 'wifi';
    case Server = 'server';
    case OfficeBuilding = 'office-building';

    public function label(): string
    {
        return match ($this) {
            self::LightningBolt => 'Lightning Bolt',
            self::Fire => 'Fire',
            self::Sun => 'Sun',
            self::Cloud => 'Cloud',
            self::Beaker => 'Beaker',
            self::Cube => 'Cube',
            self::Cog => 'Cog',
            self::Chip => 'Chip',
            self::Database => 'Database',
            self::LightBulb => 'Light Bulb',
            self::Refresh => 'Refresh',
            self::SwitchVertical => 'Switch Vertical',
            self::Truck => 'Truck',
            self::Filter => 'Filter',
            self::ShieldCheck => 'Shield Check',
            self::Sparkles => 'Sparkles',
            self::Globe => 'Globe',
            self::Wifi => 'Wifi',
            self::Server => 'Server',
            self::OfficeBuilding => 'Office Building',
        };
    }

    /**
     * The SVG path "d" attribute(s) for this icon — most icons are one path, a few (Cog,
     * Fire) are two, drawn in order.
     *
     * @return list<string>
     */
    public function paths(): array
    {
        return match ($this) {
            self::LightningBolt => [
                'M13 10V3L4 14h7v7l9-11h-7z',
            ],
            self::Fire => [
                'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z',
                'M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z',
            ],
            self::Sun => [
                'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
            ],
            self::Cloud => [
                'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z',
            ],
            self::Beaker => [
                'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
            ],
            self::Cube => [
                'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            ],
            self::Cog => [
                'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            self::Chip => [
                'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
            ],
            self::Database => [
                'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
            ],
            self::LightBulb => [
                'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            ],
            self::Refresh => [
                'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            ],
            self::SwitchVertical => [
                'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4',
            ],
            self::Truck => [
                'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z',
                'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1',
            ],
            self::Filter => [
                'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z',
            ],
            self::ShieldCheck => [
                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ],
            self::Sparkles => [
                'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            ],
            self::Globe => [
                'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            self::Wifi => [
                'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0',
            ],
            self::Server => [
                'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-14 0h14m-9-3h4m-4 6h4',
            ],
            self::OfficeBuilding => [
                'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            ],
        };
    }

    /**
     * @return array<string, string> value => label, for populating a <select> (used only as
     *                               a fallback where a full icon grid isn't practical, e.g. a datalist).
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn (self $case) => $case->value, self::cases()),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
