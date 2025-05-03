<?php

namespace App\Services\Order;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Product\CartException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Exceptions\General\ModelNotFoundException;

class OrderService
{
    public Order $order;


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



    public function create(array $data): Order
    {
        $data = self::validate($data);
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
                return $cartItem->size->product->price * $cartItem->quantity;
            });

            // Apply any additional logic here (shipping fee, discounts, etc.)
            $total = $subtotal; // Simplified

            // create the order
            $order = Order::create([
                'user_id' => $user->id,
                'order_no' => 'BONGO_ORD-' . strtoupper(uniqid()),
                'delivery_method' => $data['delivery_method'],
                'payment_method' => $data['payment_method'],
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
                    'price' => $cartItem->size->product->price,
                ]);
            }

            // clear the cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();
            return $order;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data, $id);

            $order = !empty($id) ? $this->getById($id) : $this->order;
            $order->quantity += $data['quantity'];
            $order->save();

            DB::commit();
            return $order->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $order = !empty($id) ? $this->getById($id) : $this->order;
            $order->delete();
            DB::commit();
            return $order;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = Order::query()
            ->with(['size.product', 'user']);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
