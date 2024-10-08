<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::find($id);

        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'total_price' => $request->input('total_price'),
            ]);

            foreach ($request->input('products') as $productData) {
                $productInstance = Product::find($productData['id']);

                if ($productInstance->stock < $productData['quantity']) {
                    throw new \Exception("Stock insuficiente para el producto {$productInstance->name}");
                }

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                ]);

                $productInstance->decrement('stock', $productData['quantity']);
            }

            DB::commit();

            return response()->json(['message' => 'Order successfully created', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
