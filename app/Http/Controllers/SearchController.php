<?php

namespace App\Http\Controllers;

use App\Interfaces\SearchInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected SearchInterface $searchService)
    {
        // Results link to machines.show, so searching needs machines.view as well as machines.search.
        $this->middleware('permission:machines.view');
        $this->middleware('permission:machines.search');
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        return response()->json([
            'data' => $this->searchService->searchMachines($validated['q']),
        ]);
    }
}
