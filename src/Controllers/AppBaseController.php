<?php

namespace Juanfv2\BaseCms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Juanfv2\BaseCms\Criteria\LimitOffsetCriteriaModel;
use Juanfv2\BaseCms\Criteria\RequestCriteriaModel;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Traits\ControllerFiles;
use Juanfv2\BaseCms\Traits\ControllerResponses;
use Juanfv2\BaseCms\Traits\ImportableExportable;

class AppBaseController extends Controller
{
    use ControllerResponses, ControllerFiles, ImportableExportable;

    /** $model \Illuminate\Database\Eloquent\Model */
    public $model;

    public $modelNameCamel;

    /**
     * Display a listing of the {{CurrentModel}}.
     * GET|HEAD /{model}
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|string
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '');
        $limit = $request->get('limit', -1);

        $this->model->pushCriteria(new RequestCriteriaModel($request));

        switch ($action) {
            case 'export':
                $headers = json_decode($request->get('fields'), true, 512, JSON_THROW_ON_ERROR);
                $zName = _sanitize($request->get('title', '-'));
                $extension = $request->get('ext', 'csv');

                return $this->export($zName, $extension, $headers, $this->model);

            case 'countable':
                $items = $this->model->mAll();
                $itemCount = $items->count();

                break;

            case 'distinct':
                $itemCount = $this->model->mQueryWithCriteria()->distinct()->count($this->model->getTable().'.'.$this->model->getKeyName());

                $this->model->pushCriteria(new LimitOffsetCriteriaModel($request));

                $items = $this->model->mDistinct();

                break;

            default:
                $itemCount = $this->model->mCount();

                $this->model->pushCriteria(new LimitOffsetCriteriaModel($request));

                $items = $this->model->mAll();
                break;
        }

        /* */
        $items = GenericResource::collection($items);
        /* */
        return $this->sendResponse(
            ['totalPages' => abs(ceil($itemCount / $limit)), 'totalElements' => $itemCount, 'content' => $items],
            __('validation.model.list', ['model' => __("models.{$this->modelNameCamel}.plural")])
        );
    }

    /**
     * Store a newly created {{CurrentModel}} in storage.
     * POST /{model}
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|string
     */
    public function store(Request $request)
    {
        if ($request->has('to_index')) {
            return $this->index($request);
        }

        $input = $this->validate($request, $this->model::$rules);
        // $input = $request->all();

        $model = $this->model->newInstance()->mSave($input);

        // $model = new GenericResource($model);

        return $this->sendResponse(['id' => $model->id], __('validation.model.stored', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Display the specified {{CurrentModel}}.
     * GET|HEAD /{model}/{id}
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $with = json_decode(urldecode(request('with', '[]')), null, 512, JSON_THROW_ON_ERROR);

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
     * Update the specified {{CurrentModel}} in storage.
     * PUT/PATCH /{model}/{id}
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $input = $this->validate($request, $this->model::$rules);
        // $input = $request->all();

        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $model = $model->mSave($input);

        // $model = new GenericResource({{CurrentModel}});

        return $this->sendResponse(['id' => $model->id], __('validation.model.updated', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Remove the specified {{CurrentModel}} from storage.
     * DELETE /{model}/{id}
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $resp = $model->delete();

        return $this->sendResponse(['id' => $model->id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }
}
