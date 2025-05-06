<?php

namespace App\Services\Order;

use App\Models\Order;
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
        $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $model = Order::where($column, $key)
                    ->where('user_id', $user->id)
                    ->with(['items', 'user'])
                    ->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Order not found");
        }
        return $model;
    }




    public function getAll($per_page = null)
    {
        $user = auth()->user();
        if (empty($user)) {
            throw new ModelNotFoundException("User not found");
        }
        $query = Order::query()
            ->where('user_id', $user->id)
            ->with(['items.product.product.images', 'user']);
        if (!empty($per_page)) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }
}
