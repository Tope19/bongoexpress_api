<?php

namespace App\Http\Controllers\Api\Logistics;

use Exception;
use App\Helpers\ApiHelper;
use App\Models\OrderDropoff;
use Illuminate\Http\Request;
use App\Models\LogisticOrder;
use Illuminate\Support\Facades\Http;
use App\Models\PickupLocation;
use App\Models\LogisticPayment;
use App\Http\Controllers\Controller;
use App\Constants\General\ApiConstants;
use App\Services\Logistic\OrderService;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Product\OrderException;
use App\Exceptions\Payment\PaymentException;
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
     * Get available delivery zones
     */
    public function listDeliveryZones(Request $request)
    {
        try {
            $zones = \App\Models\DeliveryZone::where('is_active', true)->get();
            $data = \App\Http\Resources\Logistics\DeliveryZoneResource::collection($zones);
            return ApiHelper::validResponse("Delivery Zones retrieved successfully", $data);
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
     * Calculate price for delivery using zone-based pricing
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

            // Use zone-based pricing for multiple dropoffs
            $priceDetails = $this->price_service->calculateMultiDropoffZonePrice(
                $pickupLocation,
                $dropoffLocations,
                $validated['weight'],
                $validated['package_type_id']
            );

            return ApiHelper::validResponse("Estimated Fare generated using zone-based pricing", $priceDetails);
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
        $paystackSecretKey = config('services.paystack.secret_key');
        if (empty($paystackSecretKey)) {
            logger('Paystack secret key not set');
            throw new PaymentException('Paystack secret key not set');
        }
        $user = auth()->user();

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

            // Use zone-based pricing for multiple dropoffs
            $priceDetails = $this->price_service->calculateMultiDropoffZonePrice(
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
                'payment_status' => 'Pending',
            ]);

            // Create dropoff locations
            foreach ($validated['dropoffs'] as $index => $dropoff) {
                $dropoffPrice = $priceDetails['dropoff_prices'][$index]['price'] ?? 0;
                $dropoffDistance = $priceDetails['dropoff_prices'][$index]['distance'] ?? 0;

                OrderDropoff::create([
                    'logistic_order_id' => $order->id,
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

            // generate a reference for paystack
            $reference = 'psk_ref_' . uniqid();

            // create the payment
            $payment = LogisticPayment::create([
                'logistic_order_id' => $order->id,
                'reference' => $reference,
                'amount' => $priceDetails['total_price'],
                'status' => 'Pending',
            ]);


             // Initialize payment on Paystack
             $paystackResponse = Http::withToken($paystackSecretKey)->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => (int) $priceDetails['total_price'] * 100, // Paystack requires amount in kobo
                'reference' => $reference,
            ]);

            // log the response
            logger('Paystack response', [
                'response' => $paystackResponse->json(),
                'status_code' => $paystackResponse->status(),
                'body' => $paystackResponse->body(),
                'key' => substr($paystackSecretKey, 0, 10)
            ]);

            if (!$paystackResponse->ok()) {
                throw new PaymentException('Payment initialization failed');
            }

            $responseBody = $paystackResponse->json();

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'logistic_order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_price' => $order->total_price,
                    'total_distance' => $order->total_distance,
                    'price_details' => $priceDetails,
                    'payment_url' => $responseBody['data']['authorization_url'],
                    'payment_reference' => $responseBody['data']['reference'],
                    'access_code' => $responseBody['data']['access_code'],
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

    /**
     * Test zone-based pricing (for development/testing)
     */
    public function testZonePricing(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pickup_latitude' => 'required|numeric',
                'pickup_longitude' => 'required|numeric',
                'dropoff_latitude' => 'required|numeric',
                'dropoff_longitude' => 'required|numeric',
                'weight' => 'required|numeric|min:0.1',
                'package_type_id' => 'required|exists:package_types,id',
            ]);
            $validated = $validator->validated();

            // Test zone-based pricing
            $zonePriceDetails = $this->price_service->calculateZoneBasedPrice(
                $validated['pickup_latitude'],
                $validated['pickup_longitude'],
                $validated['dropoff_latitude'],
                $validated['dropoff_longitude'],
                $validated['weight'],
                $validated['package_type_id']
            );

            return ApiHelper::validResponse("Zone-based pricing test successful", $zonePriceDetails);
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
