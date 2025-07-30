<?php

namespace App\Http\Controllers\Api\Location;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Models\State;
use App\Models\City;
use App\Models\DeliveryZone;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    /**
     * Get all available states for delivery
     */
    public function getStates(Request $request)
    {
        try {
            $states = State::getAvailableStates();

            $data = $states->map(function ($state) {
                return [
                    'id' => $state->id,
                    'name' => $state->name,
                    'code' => $state->code,
                    'delivery_available' => $state->delivery_available,
                ];
            });

            return ApiHelper::validResponse("States retrieved successfully", $data);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Get cities by state
     */
    public function getCitiesByState(Request $request, $stateId)
    {
        try {
            $cities = City::getCitiesByState($stateId);

            $data = $cities->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'state_id' => $city->state_id,
                    'delivery_available' => $city->delivery_available,
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                ];
            });

            return ApiHelper::validResponse("Cities retrieved successfully", $data);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Get delivery zones by state
     */
    public function getZonesByState(Request $request, $stateId)
    {
        try {
            $zones = DeliveryZone::getZonesByState($stateId);

            $data = $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'base_price' => $zone->base_price,
                    'state' => $zone->state ? [
                        'id' => $zone->state->id,
                        'name' => $zone->state->name,
                        'code' => $zone->state->code,
                    ] : null,
                    'city' => $zone->city ? [
                        'id' => $zone->city->id,
                        'name' => $zone->city->name,
                    ] : null,
                ];
            });

            return ApiHelper::validResponse("Delivery zones retrieved successfully", $data);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Get delivery zones by city
     */
    public function getZonesByCity(Request $request, $cityId)
    {
        try {
            $zones = DeliveryZone::getZonesByCity($cityId);

            $data = $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'base_price' => $zone->base_price,
                    'state' => $zone->state ? [
                        'id' => $zone->state->id,
                        'name' => $zone->state->name,
                        'code' => $zone->state->code,
                    ] : null,
                    'city' => $zone->city ? [
                        'id' => $zone->city->id,
                        'name' => $zone->city->name,
                    ] : null,
                ];
            });

            return ApiHelper::validResponse("Delivery zones retrieved successfully", $data);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Calculate shipping fee based on location
     */
    public function calculateShippingFee(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'state_id' => 'required|exists:states,id',
                'city_id' => 'nullable|exists:cities,id',
                'delivery_method' => 'required|in:Door Delivery,Pickup',
            ]);

            if ($validator->fails()) {
                return ApiHelper::inputErrorResponse($validator->errors()->first(), ApiConstants::VALIDATION_ERR_CODE);
            }

            $validated = $validator->validated();

            // If delivery method is pickup, no shipping fee
            if ($validated['delivery_method'] === 'Pickup') {
                return ApiHelper::validResponse("Shipping fee calculated", [
                    'shipping_fee' => 0.00,
                    'delivery_method' => 'Pickup',
                    'message' => 'No shipping fee for pickup'
                ]);
            }

            // Get zones for the state
            $zones = DeliveryZone::getZonesByState($validated['state_id']);

            if ($zones->isEmpty()) {
                // Fallback to catch-all zone
                $fallbackZone = DeliveryZone::where('state_id', null)
                    ->where('is_active', true)
                    ->first();

                if ($fallbackZone) {
                    $shippingFee = $fallbackZone->base_price;
                } else {
                    $shippingFee = 10000.00; // Default fallback
                }
            } else {
                // Use the first available zone (you can add logic to select specific zone)
                $shippingFee = $zones->first()->base_price;
            }

            return ApiHelper::validResponse("Shipping fee calculated", [
                'shipping_fee' => $shippingFee,
                'delivery_method' => 'Door Delivery',
                'available_zones' => $zones->map(function ($zone) {
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'base_price' => $zone->base_price,
                    ];
                }),
            ]);

        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }
}
