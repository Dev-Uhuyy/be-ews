<?php
namespace App\Http\Controllers;

use App\Models\ErrorLog;
use OpenApi\Annotations as OA;

abstract class Controller
{
    public function exceptionError($e, $exception, $status = 400)
    {
        $request = request();

        ErrorLog::create([
            'user_id' => optional($request->user())->id,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => json_encode($request->all()),
        ]);


        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => 'Exception Error : ' . $exception,
        ], $status);
    }


    public function successResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function paginationResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ], $status);
    }

    public function paginate(int $total, int $perPage, int $currentPage, array $data, $next = null, $prev = null)
    {
        return [
            'total' => $total,
            'count' => count($data),
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => (int) ceil($total / $perPage),
            'links' => [
                'prev' => $prev ?? ($currentPage > 1 ? url()->current() . '?page=' . ($currentPage - 1) : null),
                'next' => $next ?? ($currentPage * $perPage < $total ? url()->current() . '?page=' . ($currentPage + 1) : null),
            ]
        ];
    }

    public function respond($data)
    {
        return response()->json($data);
    }
}
