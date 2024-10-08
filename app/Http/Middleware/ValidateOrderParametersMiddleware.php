<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateOrderParametersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $errors = [];

        $requiredParams = ['customer_name', 'customer_email', 'total_price', 'products'];
        foreach ($requiredParams as $param) {
            if (!$request->has($param)) {
                $errors[$param] = "The $param field is required.";
            }
        }

        $products = $request->input('products', []);
        if (!is_array($products) || count($products) === 0) {
            $errors['products'] = "The list of products is required and must be an array.";
        } else {
            foreach ($products as $index => $product) {
                if (!isset($product['id'], $product['quantity'], $product['price'])) {
                    $errors["products.$index"] = "Each product must have product_id, quantity, and price.";
                }

                if (isset($product['quantity']) && $product['quantity'] <= 0) {
                    $errors["products.$index.quantity"] = "The quantity must be greater than 0.";
                }
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return $next($request);
    }
}
