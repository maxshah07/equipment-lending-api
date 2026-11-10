<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function index(Request $request) {
        $validated = $request->validate([
            'search' => 'nullable|string|max:150',
            'category' => 'nullable|string|max:100',
            'available' => 'nullable|boolean'
        ]);

        $search = $validated['search'] ?? null;
        $category = $validated['category'] ?? null;
        $available = $validated['available'] ?? null;

        $equipment = Equipment::query()->with('loans')
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($category, function($query) use ($category) {
                $query->where('category', 'like', "%{$category}%");
            })
            ->when($available !== null, function ($query) use ($available) {
                // If the user requests available equipment
                if ((bool) $available) {
                    $query->where('is_active', true)
                        // Equipment must have no unreturned loans
                        ->whereDoesntHave('loans', function ($q) {
                            $q->whereNull('returned_at');
                        });
                } else {
                    // If the user requests unavailable equipment
                    $query->where(function ($q) {
                        // Equipment is either inactive
                        $q->where('is_active', false)
                            // OR has an unreturned loan
                            ->orWhereHas('loans', function ($loanQuery) {
                                $loanQuery->whereNull('returned_at');
                            });
                    });
                }
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

            // Loop through each equipment item in the paginated results
            $equipment->through(function ($item) {

                // Equipment is available only if:
                // 1. It is active (is_active = true)
                // 2. It has no loans where returned_at is NULL
                $item->available = (bool) $item->is_active

                    // contains() returns true if at least one loan matches the condition
                    // The ! reverses the result, so it becomes true when no active loans exist
                    && !$item->loans->contains(function ($loan) {

                        // A NULL returned_at means the equipment hasn't been returned
                        return $loan->returned_at === null;
                    });

                // Return the equipment item with its new 'available' property
                return $item;
            });

            return response()->json($equipment);
    }

    public function show(Equipment $equipment)
    {
        // Load the equipment's loans
        $equipment->load('loans');

        // Equipment is available if it is active
        // AND it has no unreturned loans
        $equipment->available = (bool) $equipment->is_active
            && !$equipment->loans->contains(function ($loan) {
                return $loan->returned_at === null;
            });

        // Return the equipment with its available property
        return response()->json($equipment);
    }
}
