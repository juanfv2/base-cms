@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Http\Controllers;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Criteria\LimitOffsetCriteriaModel;
use Juanfv2\BaseCms\Criteria\RequestCriteriaModel;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Traits\ControllerFiles;
use Juanfv2\BaseCms\Traits\ControllerResponses;
use Juanfv2\BaseCms\Traits\ImportableExportable;

/**
 * @OA\Server(url="/{{ $apiPrefix }}")
 * @OA\Info(
 *   title="InfyOm Laravel Generator APIs",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    use ControllerResponses, ControllerFiles, ImportableExportable;

    /**
     * Display a listing of the "CurrentModel".
     * GET|HEAD /"CurrentModel"
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '');
        $limit = $request->get('limit', -1);

        $this->model->pushCriteria(new RequestCriteriaModel($request));

        switch ($action) {
            case 'export':
                $headers = json_decode($request->get('fields', '[]'), true, 512, JSON_THROW_ON_ERROR);
                $zName = $request->get('title', '-');
                $extension = $request->get('ext', 'csv');

                return $this->export($zName, $extension, $headers, $this->model);

            case 'countable':
                $items = $this->model->mAll();
                $itemCount = $items->count();

                return $this->sendResponse(
                    ['totalPages' => abs(ceil($itemCount / $limit)), 'totalElements' => $itemCount, 'content' => $items],
                    __('validation.model.list', ['model' => __("models.{$this->modelNameCamel}.plural")])
                );
                break;

            default:
                $itemCount = $this->model->mCount();

                $this->model->pushCriteria(new LimitOffsetCriteriaModel($request));

                $items = $this->model->mAll();

                /* */
                $items = GenericResource::collection($items);
                /* */
                // dd($items->toArray());
                return $this->sendResponse(
                    ['totalPages' => abs(ceil($itemCount / $limit)), 'totalElements' => $itemCount, 'content' => $items],
                    __('validation.model.list', ['model' => __("models.{$this->modelNameCamel}.plural")])
                );
        }
    }

    /**
     * Store a newly created "CurrentModel" in storage.
     * POST /{model}
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if ($request->has('to_index')) {
            return $this->index($request);
        }

        $input = $this->validate($request, $this->model::$rules);
        // $input = $request->all();

        $model = $this->model->mSave($input);

        // $model = new GenericResource($model);

        return $this->sendResponse(['id' => $model->id], __('validation.model.stored', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Display the specified "CurrentModel".
     * GET|HEAD /{model}/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $with = json_decode(urldecode(request('with', '[]')), null, 512, JSON_THROW_ON_ERROR);

        /** @var \App\Models\"CurrentModel" $model */
        $model = $with ?
            $this->model->with($with)->find($id) :
            $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $model = new GenericResource($model);

        return $this->sendResponse($model, __('validation.model.showed', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Update the specified "CurrentModel" in storage.
     * PUT/PATCH /{model}/{id}
     *
     * @param  int  $id
     * @param  Request  $request
     * @return Response
     */
    public function update($id, Request $request)
    {
        $input = $this->validate($request, $this->model::$rules);
        // $input = $request->all();

        /** @var \App\Models\"CurrentModel" $model */
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $model = $model->mSave($input);

        // $model = new GenericResource("CurrentModel");

        return $this->sendResponse(['id' => $model->id], __('validation.model.updated', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Remove the specified "CurrentModel" from storage.
     * DELETE /{model}/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        /** @var \App\Models\"CurrentModel" $model */
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $resp = $model->delete();

        return $this->sendResponse(['id' => $model->id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }
}
