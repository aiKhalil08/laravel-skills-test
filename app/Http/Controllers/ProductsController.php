<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;

class ProductsController extends Controller
{
    public function index(Request $request, $id = null) {
        $productId = $id;
        $products = Storage::disk('public')->json('products.json');

        $sumTotalValue = array_reduce($products, function ($a, $b) {
            $quantity = (int) $b['quantity'];
            $pricePerItem = (int) $b['price'];
            $totalPrice = $quantity * $pricePerItem;
            return $a + $totalPrice;
        }, 0);

        $product = null;
        if ($productId) {
            $product = Arr::first($products, function ($product) use ($productId) {
                return $product['id'] ===  (int) $productId;
            });
        }

        return view('products.index', ['product'=> $product, 'products'=>$products, 'sumTotalValue'=>$sumTotalValue]);
    }

    public function store(Request $request) {
        $newProduct = $request->only(['name','quantity','price']);
        $newProduct['time-added'] = now();
        try {
            $exitingProducts = Storage::disk('public')->json('products.json');
            $newProduct['id'] = count($exitingProducts) + 1;
            $exitingProducts[] = $newProduct;
            $sumTotalValue = array_reduce($exitingProducts, function ($a, $b) {
                $quantity = (int) $b['quantity'];
                $pricePerItem = (int) $b['price'];
                $totalPrice = $quantity * $pricePerItem;
                return $a + $totalPrice;
            }, 0);
            Storage::disk('public')->put('products.json', json_encode($exitingProducts));
            return response()->json(['product'=> $newProduct, 'sum-total-value'=> $sumTotalValue], 200);
        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $id) {
        try {
            $products = collect(Storage::disk('public')->json('products.json'));
            $newProduct = $request->only(['name','quantity','price']);
            $newProduct['id'] = (int) $request->id;
            $productIndex = $products->search(function ($product) use ($id) {
                return $product['id'] === (int) $id;
            });
            $products[$productIndex] = [...$products[$productIndex], ...$newProduct];
            $sumTotalValue = array_reduce($products->toArray(), function ($a, $b) {
                $quantity = (int) $b['quantity'];
                $pricePerItem = (int) $b['price'];
                $totalPrice = $quantity * $pricePerItem;
                return $a + $totalPrice;
            }, 0);
            Storage::disk('public')->put('products.json', json_encode($products));
            return response()->json(['product'=> $products[$productIndex], 'sum-total-value'=> $sumTotalValue], 200);
        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 400);
        }
    }
}
