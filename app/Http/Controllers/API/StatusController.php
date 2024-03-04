<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Models\Status;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatusController extends Controller
{

    public function fetchStatuses()
    {
        try {

            /** check for existence of cache 'statuses' data */
            if (Cache::has('statuses')) {
                $statuses = Cache::get('statuses');

                /* Prepare the response */
                $data = new \stdClass();
                $data->statuses = StatusResource::collection($statuses);

                return $this->sendSuccessResponse($data, 'Statuses retrieved successfully!', 200);
            }

            /*Fetch all statuses and cache for a minute */
            $statuses = Cache::remember('statuses', 60, function () {
                return Status::orderBy('name', 'asc')->get();
            });

            /* Prepare the response */
            $data = new \stdClass();
            $data->statuses = StatusResource::collection($statuses);

            return $this->sendSuccessResponse($data, 'Statuses retrieved successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }

    public function fetchStatusById(int $statusId): JsonResponse
    {
        try {

            /* Confirm The status Exists using status id */
            $status = Status::where('id', $statusId)->first();

            if (is_null($status)) {
                $errors = new \stdClass();
                $errors->status = ['Sorry, This Status could not be retrieved!'];

                return $this->sendErrorResponse($errors, 'Status could not be retrieved!', 400);
            }
            /* Prepare the response */
            $data = new \stdClass();
            $data->statuses = new StatusResource($status);

            return $this->sendSuccessResponse(new StatusResource($status), 'Status retrieved successfully!', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e->getTrace()]);
            return $this->sendServerError('Sorry, Something went wrong. Please, try again.');
        }
    }
}
