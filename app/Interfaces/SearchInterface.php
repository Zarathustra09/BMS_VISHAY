<?php

namespace App\Interfaces;

interface SearchInterface
{
    /**
     * Header search: machines matching the term on name, machine code, serial number or model
     * number, shaped for the live dropdown (display fields plus a link to machines.show).
     *
     * @return array<int, array{id: int, name: string, code: string, type: ?string, status: string, status_label: string, status_badge: string, url: string}>
     */
    public function searchMachines(string $term, int $limit = 8): array;
}
