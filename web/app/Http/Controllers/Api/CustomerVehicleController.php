<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerVehicleController extends Controller
{
    
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = $request->user()
            ->vehicles()
            ->latest('id')
            ->get();

        return response()->json([
            'vehicles' => $vehicles->map(
                fn(Vehicle $vehicle) => [
                    'id' => $vehicle->id,
                    'plate_display' => $vehicle->plate_display,
                    'plate_region' => $vehicle->plate_region,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'colour' => $vehicle->colour,
                    'notes' => $vehicle->notes,
                ],
            )->values(),
        ]);
    }

    public function store(
        StoreCustomerVehicleRequest $request,
    ): JsonResponse {
        $this->authorize('create', Vehicle::class);

        $validated = $request->validated();

        $plateNormalised = Str::upper(
            preg_replace(
                '/[\s\-_\.]+/u',
                '',
                trim($validated['plate_display']),
            ),
        );

        $vehicle = Vehicle::create([
            'organization_id' => $request->user()->organization_id,
            'plate_normalised' => $plateNormalised,
            'plate_display' => trim($validated['plate_display']),
            'plate_region' => $validated['plate_region'] ?? null,
            'vehicle_type' => $validated['vehicle_type'],
            'make' => $validated['make'] ?? null,
            'model' => $validated['model'] ?? null,
            'colour' => $validated['colour'] ?? null,
            'owner_user_id' => $request->user()->id,
            'is_blacklisted' => false,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'plate_display' => $vehicle->plate_display,
                'plate_region' => $vehicle->plate_region,
                'vehicle_type' => $vehicle->vehicle_type,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'colour' => $vehicle->colour,
                'notes' => $vehicle->notes,
            ],
        ], 201);
    }
}