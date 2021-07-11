<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Auth\Account;

use Illuminate\Http\Request;
use App\Services\ExportDataService;
use App\Criteria\LimitOffsetCriteria;
use App\Http\Resources\GenericResource;
use App\Criteria\RequestGenericCriteria;
use App\Repositories\Auth\UserRepository;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Reports\UserLogViewedAtRepository;

/**
 * Class PersonController
 * @package App\Http\Controllers\API
 */
class UserAPIController extends AppBaseController
{
    /** @var  UserLogViewedAtRepository */
    private $modelUserLogViewedAtRepository;
    /** @var  UserRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'User';

    public function __construct(UserRepository $modelRepo, UserLogViewedAtRepository $modelUserLogViewedAtRepository)
    {
        $this->modelRepository = $modelRepo;
        $this->modelUserLogViewedAtRepository = $modelUserLogViewedAtRepository;
        $this->rules = User::$rules + Person::$rules;
    }

    public function index(Request $request)
    {
        $repoName              = $request->get('rep', '');
        $repo                  = "model{$repoName}Repository";
        $this->modelRepository = $this->$repo;

        return parent::index($request);

        // $action   = $request->get('action', '');
        // $limit    = $request->get('limit', -1);
        // $criteria = new RequestGenericCriteria($request);
        // $this->$repo->pushCriteria($criteria);
        // switch ($action) {
        //     case 'export':
        //         $headers = json_decode($request->get('fields'), true);
        //         $zname = $request->get('title', '-');
        //         return $this->export($zname, $headers, $this->$repo);
        //     default:

        //         $itemCount = $this->$repo->count();

        //         $this->$repo->pushCriteria(new LimitOffsetCriteria($request));

        //         $items = $this->$repo->all();

        //         /* */
        //         $items = GenericResource::collection($items);
        //         /* */
        //         return $this->sendResponse(
        //             ['totalPages' => abs(ceil($itemCount / $limit)), 'totalElements' => $itemCount, 'content' => $items,],
        //             __('validation.model.list', ['model' => __("models.{$this->modelNameCamel}.plural")])
        //         );
        // }
    }

    /**
     * Store a newly created Person in storage.
     * POST /people
     *
     * @param CreatePersonAPIRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $withEntity = $request->get('withEntity', '-');

        switch ($withEntity) {
            case 'auth_people':
                $this->rules = $this->rules + Person::$rules;
                break;
            default:
                $this->rules = $this->rules + Account::$rules;
                break;
        }

        $input = $this->validate($request, $this->rules);

        $model = $this->modelRepository->withAdditionalInfo('create', $input);

        if ($request->hasFile('photoUrl')) {
            return $this->fileUpload('auth_users', 'photoUrl', $model->id, 0);
        }

        return $this->sendResponse(['id' => $model->id], __('validation.model.stored', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Update the specified Person in storage.
     * PUT/PATCH /people/{id}
     *
     * @param int $id
     * @param UpdatePersonAPIRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $withEntity = $request->get('withEntity', '-');

        switch ($withEntity) {
            case 'auth_people':
                $this->rules = $this->rules + Person::$rules;
                break;
            default:
                $this->rules = $this->rules + Account::$rules;
                break;
        }

        $this->rules['email']    = 'required|string|max:191';
        $this->rules['password'] = 'min:6|confirmed';

        $input = $this->validate($request, $this->rules);
        // $input = $request->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }
        $model = $this->modelRepository->withAdditionalInfo('update', $input, $model);

        return $this->sendResponse(['id' => $model->id], __('validation.model.updated', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Remove the specified Person from storage.
     * DELETE /people/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $input = request()->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $resp = $this->modelRepository->withAdditionalInfo('delete', $input, $model);

        return $this->sendResponse(['id' => $id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    protected function export($table, $headers, $repo)
    {
        $labels   = array_values($headers);
        $fnames   = array_keys($headers);
        $exporter = (new ExportDataService('csv', 'browser', $table . '.csv'))->getExporter();

        $exporter->initialize(); // starts streaming data to web browser
        $exporter->addRow($labels);

        $repo->allForChunk()->chunk(10000, function ($items) use ($fnames, $exporter) {
            foreach ($items as $item) {
                $i = [];
                foreach ($fnames as $key) {
                    switch ($key) {
                        case 'search':
                            // logger(__FILE__ . ':' . __LINE__ . ' $item->search ', [is_array($item->search), serialize($item->search)]);
                            $strs = [];
                            if (isset($item->search['v']['conditionsList'])) {
                                foreach ($item->search['v']['conditionsList'] as $conditionItem) {
                                    if ($conditionItem['value']) {
                                        $str = $conditionItem['field']['label'];
                                        $str .= "'{$conditionItem['cond']}' ";
                                        $str .= "'{$conditionItem['value']}' ";
                                        $strs[] = $str;
                                    }
                                }
                            }

                            if (isset($item->search['v']['conditions'])) {
                                foreach ($item->search['v']['conditions'] as $conditionItem) {
                                    if ($conditionItem['value']) {
                                        $str = $conditionItem['field']['label'];
                                        $str .= "'{$conditionItem['cond']}' ";
                                        $str .= "'{$conditionItem['value']}' ";
                                        $strs[] = $str;
                                    }
                                }
                            }

                            $i[$key] = implode(' | ', $strs);

                            break;

                        case 'vieweds':
                            $strs = [];
                            foreach ($item->vieweds as $v) {
                                $str = "($v->views)";
                                $str .= $v->name;
                                $strs[] = $str;
                            }
                            $i[$key] = implode(' | ', $strs);
                            break;

                        case 'versions':
                            $strs = [];
                            foreach ($item->versions as $v) {
                                $str = "Archivo #: $v->id ";
                                $str .= "Nombre: $v->name ";
                                $str .= "version-$v->version - $v->item_id ";
                                $str .= "URL: /viewer/{$v->id}/{$v->container_id}/0";
                                $strs[] = $str;
                            }
                            $i[$key] = implode(' | ', $strs);
                            break;

                        default:
                            $i[$key] = $item->{$key};
                            break;
                    }
                }
                $exporter->addRow($i);
            }
        });

        $exporter->finalize(); // writes the footer, flushes remaining data to browser.

        exit(); // all done
    }
}
