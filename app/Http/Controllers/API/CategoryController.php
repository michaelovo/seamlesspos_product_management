<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    use Helpers;

    public function fetchCategories(): JsonResponse
    {
        try {

            /** check for existence of cache 'categories' data */
            if (Cache::has('categories')) {
                $categories = Cache::get('categories');

                return $this->sendSuccessResponse(CategoryResource::collection($categories), 'Categories retrieved successfully!', 200);
            }

            $categories = Cache::remember('categories', 60, function () {
                return Category::latest()->with('status:id,name')->get();
            });

            /* Prepare the response */
            return $this->sendSuccessResponse(CategoryResource::collection($categories), 'Categories retrieved successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function fetchCategoryById(int $categoryId): JsonResponse
    {
        try {

            /* Confirm The category Exists using the category id */
            $category = Category::where('id', $categoryId)->with('status:id,name')->first();

            if (is_null($category)) {
                $errors = new \stdClass();
                $errors->status = ['Sorry, This Category could not be retrieved!'];

                return $this->sendErrorResponse($errors, 'Category could not be retrieved!', 400);
            }
            /* Prepare the response */
            return $this->sendSuccessResponse(new CategoryResource($category), 'Category retrieved successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }
}
