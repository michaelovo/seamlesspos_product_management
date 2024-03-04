<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Traits\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use Helpers;

    public function createProduct(ProductStoreRequest $request): JsonResponse
    {

        try {

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'status_id' => ProductController::getStatusId('Active'),
            ]);

            foreach ($request->category as $category) {
                /* Create product categories */
                $product_categories = ProductCategory::create([
                    'product_id' => $product->id,
                    'category_id' => $category,
                ]);
            }

            /* Send response */
            return $this->sendSuccessResponse(new ProductResource($product), 'Product Added Successfully', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function fetchAllProducts(): JsonResponse
    {
        try {

            /** check for existence of cache 'products' data */
            if (Cache::has('products')) {
                $products = Cache::get('products');

                /* Prepare the response */
                $data = new \stdClass();
                $data->products = ProductResource::collection($products)->response()->getData(true);

                return $this->sendSuccessResponse($data, 'Products retrieved successfully!', 200);
            }

            /* cache products data for 60 seconds */
            $products = Cache::remember('products', 60, function () {
                return Product::latest()->paginate(15);
            });

            /* Prepare the response */
            $data = new \stdClass();
            $data->products = ProductResource::collection($products)->response()->getData(true);

            return $this->sendSuccessResponse($data, 'Products retrieved successfully!', 200);

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function fetchProductById(int $productId): JsonResponse
    {
        try {

            /** check for existence of cache 'products' data */
            if (Cache::has('productId-' . $productId)) {
                $product = Cache::get('productId-' . $productId);
                return $this->sendSuccessResponse(new ProductResource($product), 'Product retrieved successfully!', 200);
            }

            /** find product */
            $product = Product::where('id', $productId)->first();

            /* Ensure product exist */
            if (!($product)) {
                $errors = new \stdClass();
                $errors->product = ['The selected product does not exist'];
                return $this->sendErrorResponse($errors, 'Failed to fetch selected product!', 404);
            }

            /** Cache product */
            Cache::put('productId-' . $productId, $product, 60);

            /* Send Back Response */
            return $this->sendSuccessResponse(new ProductResource($product), 'Product retrieved successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function deleteProduct(int $productId): JsonResponse
    {
        try {

            /* allow deletion of only product created by the authenticated user */
            $product = Product::where('id', $productId)
                ->where('user_id', Auth::id())
                ->first();

            /* Confirm that product exist and was created by the auth user */
            if (!($product)) {
                $errors = new \stdClass();
                $errors->product = ['Sorry, This product is not available'];

                return $this->sendErrorResponse($errors, 'Invalid Product id', 400);
            }

            /* Delete the product */
            $product->delete();

            /* Prepare response */
            $data = new \stdClass();
            $data->product = [];

            return $this->sendSuccessResponse($data, 'Product deleted successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }

    }
}
