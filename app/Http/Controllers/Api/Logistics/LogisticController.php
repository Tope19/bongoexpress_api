<?php

namespace App\Http\Controllers\Api\Logistics;

use Exception;
use App\Helpers\ApiHelper;
use App\Models\OrderDropoff;
use Illuminate\Http\Request;
use App\Models\LogisticOrder;
use App\Models\PickupLocation;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Logistic\OrderService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Product\OrderException;
use App\Exceptions\Product\ProductException;
use Illuminate\Validation\ValidationException;
use App\Services\Logistic\PriceCalculationService;
use App\Http\Resources\Logistics\PackageTypeResource;
use App\Http\Resources\Logistics\LogisticOrderResource;

class LogisticController extends Controller
{
    public $price_service;
    public $order_service;

    public function __construct()
    {
        $this->price_service = new PriceCalculationService;
        $this->order_service = new OrderService;
    }

    /**
     * Get available package types
     */
    public function listPackageTypes(Request $request)
    {
        try {
            $packages = $this->price_service->getPackageTypes();
            // dd($products);
            $data = PackageTypeResource::collection($packages);
            return ApiHelper::validResponse("Package Types retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Calculate price for delivery
     */
    public function generatePrice(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pickup_latitude' => 'required|numeric',
                'pickup_longitude' => 'required|numeric',
                'dropoff_locations' => 'required|array|min:1',
                'dropoff_locations.*.latitude' => 'required|numeric',
                'dropoff_locations.*.longitude' => 'required|numeric',
                'weight' => 'required|numeric|min:0.1',
                'package_type_id' => 'required|exists:package_types,id',
            ]);
            $validated = $validator->validated();

            $pickupLocation = [
                'lat' => $validated['pickup_latitude'],
                'lon' => $validated['pickup_longitude'],
            ];

            $dropoffLocations = [];
            foreach ($validated['dropoff_locations'] as $location) {
                $dropoffLocations[] = [
                    'lat' => $location['latitude'],
                    'lon' => $location['longitude'],
                ];
            }
            $priceDetails = $this->price_service->calculateMultiDropoffPrice(
                $pickupLocation,
                $dropoffLocations,
                $validated['weight'],
                $validated['package_type_id']
            );
            return ApiHelper::validResponse("Estimated Fare generated", $priceDetails);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ProductException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Create a new order via API
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Pickup information
            'pickup_name' => 'required|string|max:255',
            'pickup_address' => 'required|string|max:255',
            'pickup_phone' => 'required|string|max:20',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'save_pickup_location' => 'boolean',

            // Dropoff information
            'dropoffs' => 'required|array|min:1',
            'dropoffs.*.name' => 'required|string|max:255',
            'dropoffs.*.address' => 'required|string|max:255',
            'dropoffs.*.phone' => 'required|string|max:20',
            'dropoffs.*.latitude' => 'required|numeric',
            'dropoffs.*.longitude' => 'required|numeric',
            // 'dropoffs.*.notes' => 'nullable|string',

            // Package information
            'package_type_id' => 'required|exists:package_types,id',
            'weight' => 'required|numeric|min:0.1',
            'notes_for_rider' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            // Start transaction
            \DB::beginTransaction();

            // Create or get pickup location
            $pickupLocationId = null;

            if ($validated['save_pickup_location'] ?? false) {
                $location = PickupLocation::create([
                    'name' => $validated['pickup_name'],
                    'address' => $validated['pickup_address'],
                    'phone_number' => $validated['pickup_phone'],
                    'latitude' => $validated['pickup_latitude'],
                    'longitude' => $validated['pickup_longitude'],
                    'user_id' => $request->user()->id,
                ]);

                $pickupLocationId = $location->id;
            }

            // Calculate price and distance
            $pickupLocation = [
                'lat' => $validated['pickup_latitude'],
                'lon' => $validated['pickup_longitude'],
            ];

            $dropoffLocations = [];
            foreach ($validated['dropoffs'] as $dropoff) {
                $dropoffLocations[] = [
                    'lat' => $dropoff['latitude'],
                    'lon' => $dropoff['longitude'],
                ];
            }

            $priceDetails = $this->price_service->calculateMultiDropoffPrice(
                $pickupLocation,
                $dropoffLocations,
                $validated['weight'],
                $validated['package_type_id']
            );

            // Create order
            $order = LogisticOrder::create([
                'user_id' => auth()->id(),
                'pickup_location_id' => $pickupLocationId,
                'package_type_id' => $validated['package_type_id'],
                'weight' => $validated['weight'],
                'total_distance' => $priceDetails['distance'],
                'total_price' => $priceDetails['total_price'],
                'notes_for_rider' => $validated['notes_for_rider'],
            ]);

            // Create dropoff locations
            foreach ($validated['dropoffs'] as $index => $dropoff) {
                $dropoffPrice = $priceDetails['dropoff_prices'][$index]['price'] ?? 0;
                $dropoffDistance = $priceDetails['dropoff_prices'][$index]['distance'] ?? 0;

                OrderDropoff::create([
                    'order_id' => $order->id,
                    'recipient_name' => $dropoff['name'],
                    'address' => $dropoff['address'],
                    'phone_number' => $dropoff['phone'],
                    'latitude' => $dropoff['latitude'],
                    'longitude' => $dropoff['longitude'],
                    'distance_from_pickup' => $dropoffDistance,
                    'price' => $dropoffPrice,
                    // 'notes' => $dropoff['notes'] ?? null,
                    'sequence' => $index,
                ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_price' => $order->total_price,
                    'total_distance' => $order->total_distance,
                    'price_details' => $priceDetails
                ],
                'message' => 'Order created successfully'
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all orders
     */
    public function getOrders(Request $request){
        try {
            $orders = $this->order_service->getAll();
            // dd($orders);
            $data = LogisticOrderResource::collection($orders)->toArray($request);
            return ApiHelper::validResponse("Orders retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OrderException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderDetails(Request $request, $id)
    {
        try {
            $order = $this->order_service->getById($id);
            return ApiHelper::validResponse("Order retrieved successfully", new LogisticOrderResource($order));
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OrderException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }


}
