<?php

namespace Juanfv2\BaseCms\Traits;

trait ControllerResponses
{
    /**
     * @param  object|array|string  $data
     * @param  string  $message
     * @param  bool  $isSuccess
     * @param  int  $responseCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($data, $message = '', $isSuccess = true, $responseCode = 200)
    {
        return response()->json($this->makeResponse($data, $message, $isSuccess), $responseCode);
    }

    /**
     * @param  array|string  $data
     * @param  string  $message
     * @param  int  $responseCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($data, $message = '', $responseCode = 404)
    {
        $d = $data;
        $m = $message;
        $c = $responseCode;
        if (is_numeric($message)) {
            $c = $message;
        }

        return response()->json($this->makeResponse($d, $m, false), $c);
    }

    /**
     * @param $elements array|anything
     * @param  int  $totalElements
     * @param  int  $limit
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response2Api($elements, $totalElements = 0, $limit = 0)
    {
        $totalPages = abs(ceil($totalElements / $limit));

        return response()->json([
            'totalPages' => $totalPages,
            'totalElements' => $totalElements,
            'content' => $elements,
        ]);
    }

    /**
     * @param  string  $message
     * @param  mixed|string  $data
     * @return array
     */
    private function makeResponse($data, $message, $isSuccess = true)
    {
        $d = $data;
        $m = $message;
        if (is_string($data)) {
            $m = $data;
            $d = [];
        }

        $r = [
            'success' => $isSuccess,
            'message' => $m,
        ];

        if ($d) {
            $r['data'] = $d;
        }

        return $r;
    }
}
