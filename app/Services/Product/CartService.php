<?php

namespace App\Services\Product;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CartService
{
    public Cart $cart;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): Cart
    {   $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $model = Cart::where($column, $key)
                    ->where('user_id', $user->id)
                    ->with(['size.product.images', 'user'])
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Cart Item not found");
        }
        return $model;
    }


    public function validate(array $data, $id = null): array
    {
        // dd($data);
        $validator = Validator::make($data, [
            'product_size_id' => 'required|exists:product_sizes,id',
            'quantity' => 'required|integer|min:1',
            'user_id' => 'required|exists:users,id'
        ], [
            'product_size_id.required' => 'Product is required',
            'product_size_id.exists' => 'Product does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // dd($data);


        return $validator->validated();
    }

    public function validate2(array $data, $id = null): array
    {
        // dd($data);
        $validator = Validator::make($data, [
            'quantity' => 'required|integer|min:1',
        ], [
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1'
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // dd($data);


        return $validator->validated();
    }


    public function create(array $data): Cart
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);

            // Check if this size is already in the user's cart
            $cartItem = Cart::where('user_id', $data['user_id'])
                ->where('product_size_id', $data['product_size_id'])
                ->with(['size.product.images', 'user'])
                ->first();

            // dd($cartItem);

            if($cartItem){
                $cartItem->quantity += $data['quantity'];
                $cartItem->save();
                // dd($cartItem);
            } else{
                $cartItem = Cart::create([
                    'user_id' => $data['user_id'],
                    'product_size_id' => $data['product_size_id'],
                    'quantity' => $data['quantity']
                ]);
            }

            // $cart = Cart::create($data);
            DB::commit();
            return $cartItem;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $data = self::validate2($data, $id);

            $cart = !empty($id) ? $this->getById($id) : $this->cart;
            $cart->quantity += $data['quantity'];
            $cart->save();

            DB::commit();
            return $cart->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $cart = !empty($id) ? $this->getById($id) : $this->cart;
            $cart->delete();
            DB::commit();
            return $cart;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $query = Cart::query()
            ->where('user_id', $user->id)
            ->with(['size.product.images', 'user']);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
