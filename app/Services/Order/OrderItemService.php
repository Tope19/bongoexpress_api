<?php

namespace App\Services\Order;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderItemService
{
    public OrderItem $orderItem;


    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): OrderItem
    {
        $model = OrderItem::where($column, $key)
                    ->with(['size.product', 'user'])
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Order Item not found");
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



    public function create(array $data): OrderItem
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);

            // Check if this size is already in the user's Order
            $orderItemItem = OrderItem::where('user_id', $data['user_id'])
                ->where('product_size_id', $data['product_size_id'])
                ->with(['size.product', 'user'])
                ->first();

            // dd($orderItemItem);

            if($orderItemItem){
                $orderItemItem->quantity += $data['quantity'];
                $orderItemItem->save();
                // dd($orderItemItem);
            } else{
                $orderItemItem = OrderItem::create([
                    'user_id' => $data['user_id'],
                    'product_size_id' => $data['product_size_id'],
                    'quantity' => $data['quantity']
                ]);
            }

            // $orderItem = Order::create($data);
            DB::commit();
            return $orderItemItem;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // public function update(array $data, $id = null)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data = self::validate($data, $id);

    //         $orderItem = !empty($id) ? $this->getById($id) : $this->orderItem;
    //         $orderItem->quantity += $data['quantity'];
    //         $orderItem->save();

    //         DB::commit();
    //         return $orderItem->refresh();
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $orderItem = !empty($id) ? $this->getById($id) : $this->orderItem;
            $orderItem->delete();
            DB::commit();
            return $orderItem;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function getAll($per_page = null)
    {
        $query = OrderItem::query()
            ->with(['size.product', 'user']);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
