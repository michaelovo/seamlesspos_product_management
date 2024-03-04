<?php

namespace App\Http\Controllers\API;

use App\Events\ProductCategoryEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

            /* product category event */
            ProductCategoryEvent::dispatch($product, $request);

            /* Send response */
            return $this->sendSuccessResponse(new ProductResource($product->load('productCategory')), 'Product Added Successfully', 200);
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

    public function updateProduct(Request $request, int $productId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:60', Rule::unique('products')->ignore($productId)],
                'description' => 'required|string|min:3|max:500',
                'price' => 'numeric|required',
                'quantity' => 'integer|required|min:1|digits_between: 1,9',
            ]);

            /* Check The Validator For Errors */
            if ($validator->fails()) {
                return $this->sendErrorResponse($validator->errors(), 'There are some errors in your request!', 400);
            }

            /* Confirm The product Exists and owned by the authenticated user */
            $product = Product::where('id', $productId)
                ->where('user_id', Auth::id())
                ->first();

            if (!($product)) {
                $errors = new \stdClass();
                $errors->product = ['Sorry, This product could not be retrieved!'];

                return $this->sendErrorResponse($errors, 'Invalid product Id!', 400);
            }

            /* Update product details */
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
            ]);

            return $this->sendSuccessResponse(new ProductResource($product), 'Product Updated successfully!', 201);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }
}
