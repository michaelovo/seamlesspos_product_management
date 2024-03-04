<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryUpdateRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{

    public function updateProductCategory(ProductCategoryUpdateRequest $request, int $productId, int $categoryId): JsonResponse
    {
        try {
            /* find Exists */
            $product = ProductCategory::where('product_id', $productId)
                ->where('category_id', $categoryId)
                ->first();

            /* confirm product with the selected category exist */
            if (is_null($product)) {
                $errors = new \stdClass();
                $errors->product = ['Sorry, Product could not be retrieved!'];

                return $this->sendErrorResponse($errors, 'Product could not be retrieved!', 400);
            }

            /* Initial and complete update if no duplicate found for the selected item for update */
            DB::beginTransaction();

            try {
                /* Update product category detail */
                $product->update(['category_id' => $request->category]);
                DB::commit();

                return $this->sendSuccessResponse(new ProductCategoryResource($product), 'Product Category Updated Successfully', 200);
            } catch (QueryException $exception) {
                /* Abort/rollback operation if duplicate record exist for the selected item */

                $errorCode = $exception->errorInfo[1];
                if ($errorCode == '1062') {
                    DB::rollback();
                    $errors = new \stdClass();
                    $errors->product = ['product already exist with the same category! Please try again!'];
                    return $this->sendErrorResponse($errors, 'Duplicate entry!', 400);
                }
            }

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }
}
