<?php

namespace App\Services\Payment;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\LogisticPayment;
use App\Models\DeliveryZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Exceptions\Product\CartException;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Payment\PaymentException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\General\ModelNotFoundException;

class PaymentService
{
    public Order $order;
    public Payment $payment;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): Order
    {
        $model = Order::where($column, $key)
                    ->with(['items', 'user'])
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Order not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        // dd($data);
        $validator = Validator::make($data, [
            'delivery_method' => 'required|in:Door Delivery,Self Pickup',
            'payment_method' => 'required|in:Bank Transfer,Paystack',
        ], [
            'delivery_method.required' => 'Delivery method is required',
            'delivery_method.in' => 'Delivery method must be either Door Delivery or Self Pickup',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Payment method must be either Bank Transfer or Paystack',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // dd($data);


        return $validator->validated();
    }



    public function create(array $data)
    {
        $data = self::validate($data);
        $paystackSecretKey = config('services.paystack.secret_key');
        if (empty($paystackSecretKey)) {
            logger('Paystack secret key not set');
            throw new PaymentException('Paystack secret key not set');
        }
        $user = auth()->user();
        // Get Cart Items
        $cartItems = Cart::where('user_id', $user->id)
            ->with(['size.product'])
            ->get();

        if ($cartItems->isEmpty()) {
            throw new CartException("Cart is empty");
        }
        DB::beginTransaction();
        try {

            // calculate subtotal
            $subtotal = $cartItems->sum(function ($cartItem) {
                return $cartItem->size->price * $cartItem->quantity;
            });
            $total = 0.00;
            // Apply any additional logic here (shipping fee, discounts, etc.)
            if ($data['delivery_method'] == 'Door Delivery') {
                // Calculate shipping fee based on location
                $shippingFee = $this->calculateLocationBasedShippingFee($data);
                $total += $shippingFee;
            }

            // dd($cartItems);

            // $total = $subtotal; // Simplified

            // generate a reference for paystack
            $reference = 'psk_ref_' . uniqid();

            // create the order
            $order = Order::create([
                'user_id' => $user->id,
                'order_no' => 'BONGO_ORD-' . strtoupper(uniqid()),
                'delivery_method' => $data['delivery_method'],
                'payment_method' => $data['payment_method'],
                'delivery_fee' => $data['delivery_method'] == 'Door Delivery' ? $shippingFee : 0.00,
                'subtotal_price' => $subtotal,
                'total_price' => $total, // Simplified
                'status' => 'Pending',
                'payment_status' => 'Pending',
            ]);


            // create the order items
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'product_size_id' => $cartItem->product_size_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->size->price,
                ]);
            }

            // create the payment
            $payment = Payment::create([
                'order_id' => $order->id,
                'reference' => $reference,
                'amount' => $total,
                'status' => 'Pending',
            ]);

            // Initialize payment on Paystack
            $paystackResponse = Http::withToken($paystackSecretKey)->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => $total * 100, // Paystack requires amount in kobo
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


            // clear the cart
            // Cart::where('user_id', $user->id)->delete();
            DB::commit();
            // Return authorization_url to frontend
            return $responseBody;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function handleWebhook(array $payload)
    {
        // Verify Paystack signature
        $paystackSignature = request()->header('x-paystack-signature');
        $paystackSecretKey = config('services.paystack.secret_key');
        $computedHash = hash_hmac('sha512', file_get_contents('php://input'), $paystackSecretKey);

        if ($paystackSignature !== $computedHash) {
            logger('Invalid Paystack signature');
            abort(401, 'Invalid signature.');
        }

        // Process only successful charges
        if ($payload['event'] === 'charge.success') {
            logger('Paystack charge success', [
                'payload' => $payload,
            ]);
            $reference = $payload['data']['reference'];
            $alreadyProcessed = Payment::where('reference', $reference)->where('status', 'Success')->exists()
                || LogisticPayment::where('reference', $reference)->where('status', 'Success')->exists();

            if ($alreadyProcessed) {
                logger('Already processed payment', [
                    'reference' => $reference,
                ]);
                return response('OK', 200); // Early exit
            }
            $amountPaid = $payload['data']['amount'] / 100; // Convert from kobo

            DB::transaction(function () use ($reference, $amountPaid) {
                $payment = Payment::where('reference', $reference)->first();
                if($payment){
                    $order = $payment->order;

                    if ($payment->status === 'Success') {
                        // Already processed
                        return;
                    }

                    // Update payment
                    $payment->update([
                        'status' => 'Success',
                    ]);

                    // Update order
                    $order->update([
                        'payment_status' => 'Paid',
                        'status' => 'Ongoing', // You can change depending on your flow
                    ]);

                    // clear the cart
                    $user = $order->user;
                    Cart::where('user_id', $user->id)->delete();
                } else{
                    // Try to find in LogisticPayment model
                    $logisticPayment = LogisticPayment::where('reference', $reference)->firstOrFail();

                    if ($logisticPayment->status === 'Success') {
                        // Already processed
                        return;
                    }

                    // Update logistic payment
                    $logisticPayment->update([
                        'status' => 'Success',
                    ]);

                    // Update related order or delivery task — update this part based on your model relationships
                    $order = $logisticPayment->order;
                    $order->update([
                        'status' => 'Ongoing', // Or another relevant status
                        'payment_status' => 'Paid',
                    ]);
                }
            });
        }

        // Optionally handle `charge.failed`
        if ($payload['event'] === 'charge.failed') {
            logger('Paystack charge failed', [
                'payload' => $payload,
            ]);
            $reference = $payload['data']['reference'];

            DB::transaction(function () use ($reference) {
                $payment = Payment::where('reference', $reference)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'Failed',
                    ]);

                    $payment->order->update([
                        'payment_status' => 'Failed',
                        'status' => 'Pending', // You may want to keep it pending
                    ]);
                } else{
                    // Try to find in LogisticPayment model
                    $logisticPayment = LogisticPayment::where('reference', $reference)->firstOrFail();

                    if ($logisticPayment->status === 'Failed') {
                        // Already processed
                        return;
                    }

                    // Update logistic payment
                    $logisticPayment->update([
                        'status' => 'Failed',
                    ]);

                    // Update related order or delivery task — update this part based on your model relationships
                    $order = $logisticPayment->order;
                    $order->update([
                        'status' => 'Pending', // Or another relevant status
                        'payment_status' => 'Failed',
                    ]);
                }
            });
        }

        return $payload;
    }

    /**
     * Calculate location-based shipping fee
     */
    private function calculateLocationBasedShippingFee(array $data): float
    {
        // Default shipping fee if no location data
        $defaultShippingFee = 500.00;

        // Check if we have location data
        if (!isset($data['state_id'])) {
            return $defaultShippingFee;
        }

        // Get zones for the state
        $zones = DeliveryZone::getZonesByState($data['state_id']);

        if ($zones->isEmpty()) {
            // Fallback to catch-all zone
            $fallbackZone = DeliveryZone::where('state_id', null)
                ->where('is_active', true)
                ->first();

            if ($fallbackZone) {
                return $fallbackZone->base_price;
            }

            return $defaultShippingFee;
        }

        // Use the first available zone (you can add logic to select specific zone)
        return $zones->first()->base_price;
    }
}
