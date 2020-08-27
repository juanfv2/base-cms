<?php

namespace Juanfv2\BaseCms\Traits;

use InfyOm\Generator\Utils\ResponseUtil;

trait ControllerResponses
{
    public function sendResponse($message, $result)
    {
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404, $data = [])
    {
        return response()->json(ResponseUtil::makeError($error, $data), $code);
    }

    /**
     * @param $elements
     * @param int $totalElements
     * @param int $limit
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
}
