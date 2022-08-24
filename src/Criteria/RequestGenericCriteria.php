<?php

namespace Juanfv2\BaseCms\Criteria;

use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Contracts\CriteriaInterface;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

/**
 * Class RequestGenericCriteria
 * @package namespace Juanfv2\BaseCms\Criteria
 */
class RequestGenericCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;
    protected $fieldsSearchable;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $table                  = $model->getModel()->getTable();
        $this->fieldsSearchable = $repository->getFieldsSearchable();
        $conditions             = $this->request->get('conditions', '');
        $joins                  = $this->request->get('joins', '');
        $select1                = $this->request->get('select', null);
        $sorts                  = $this->request->get('sorts', '');
        $withCount              = $this->request->get('withCount', '');
        $with                   = $this->request->get('with', '');
        $onlyTrashed            = $this->request->get('onlyTrashed', '');
        $conditions             = json_decode(urldecode($conditions));
        $joins                  = json_decode(urldecode($joins));
        $sorts                  = json_decode(urldecode($sorts));
        $withCount              = json_decode(urldecode($withCount));
        $with                   = json_decode(urldecode($with));
        $select2                = $select1 ? json_decode(urldecode($select1)) : null;
        $select                 = $select2 ? $select2 : ($select1 ? explode(',', urldecode($select1)) : null);

        // logger(__FILE__ . ':' . __LINE__ . ' $this->request ', [$this->request]);
        logger(__FILE__ . ':' . __LINE__ . ' $onlyTrashed ', [$onlyTrashed]);


        if (is_array($conditions)) {
            $this->mGroup($model, $table, $conditions, '_ini_', 'and', $this->request->has('mq.massiveWithFile'));
        }

        if (is_array($joins)) {
            foreach ($joins as $k) {
                $split = explode('.', $k->c);
                if (count($split) < 3) {
                    continue;
                }

                $joinType = '';
                $joinTable  = $split[0];
                $foreignKey = $split[1];

                switch (count($split)) {
                    case 5:
                        $ownTable   = $split[2];
                        $ownerKey   = $split[3];
                        $joinType   = $split[4];
                        break;
                    case 4:
                        $ownTable   = $split[2];
                        $ownerKey   = $split[3];
                        if (strlen($split[3]) == 1) {
                            $joinType   = $split[3];
                        }
                        break;
                    default:
                        $ownTable   = $table;
                        $ownerKey   = $split[2];

                        break;
                }

                switch ($joinType) {
                    case '<':
                        $model = $model->leftJoin($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;
                    case '>':
                        $model = $model->leftJoin($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;

                    default:
                        $model = $model->join($joinTable, $joinTable . '.' . $foreignKey, '=', $ownTable . '.' . $ownerKey);
                        break;
                }

                if (isset($k->v)) {
                    $model = $model->addSelect($k->v);
                }
            } // end for ...
        }

        if (is_array($select)) {
            foreach ($select as $k) {

                if (is_string($k)) {
                    $model = $model->addSelect($k);
                } else {
                    $model = $model->selectRaw($k->v);
                    if ($k->c == 'GROUP-BY') {
                        $model = $model->groupBy($k->v);
                    }
                }
            }
        } else {
            $model = $model->addSelect($table . '.*');
        }

        if ($sorts) {
            foreach ($sorts as $k) {
                $model = $model->orderBy($k->field, ($k->order == 1 ? 'asc' : 'desc'));
            }
        }

        if ($withCount) {
            $model->withCount($withCount);
        }

        if ($with) {
            $model->with($with);
        }

        if ($onlyTrashed) {
            $model = $model->onlyTrashed();
        }
        return $model;
    }

    /**
     * @param $k
     * @param null $query
     * @param int $currentIndex
     */
    private function mGroup(&$model, $table, $kParent, $query = null, $_kOperatorStrParam = 'AND', $hasMq = false)
    {

        $q = $model->forNestedWhere();
        foreach ($kParent as $k) {
            // logger(__FILE__ . ':' . __LINE__ . ' inner $k ', [$k]);
            if (is_array($k)) {
                $qw = $this->mGroup($model, $table, $k, '_nested_', $_kOperatorStrParam);
                $q->addNestedWhereQuery($qw->getQuery(), $_kOperatorStrParam);
                continue; // continuar con el siguiente.
            }

            $noValue          = '--false--';
            $nullOrEmpty      = '---';
            $_kOperatorStr    = 'AND';
            $_kConditionalStr = '=';
            $condition        = explode(' ', $k->c);

            switch (count($condition)) {
                case 3:
                    list($_kOperatorStr, $_kFieldStr, $_kConditionalStr) = $condition;
                    break;
                case 2:
                    list($_kOperatorStr, $_kFieldStr) = $condition;
                    break;
                default:
                    list($_kFieldStr) = $condition;
            }
            if ($_kFieldStr === 'OR') {
                $_kOperatorStrParam = 'OR';
                continue; // next
            }

            $_kValue              = property_exists($k, 'v') ? $k->v : $noValue;
            $_kValueIsOptionNull  = strpos($_kConditionalStr, 'null') !== false;
            $_kValueIsOptionEmpty = strpos($_kConditionalStr, 'empty') !== false;
            $kFieldStrK           = str_replace("$table.", '', $_kFieldStr);

            if ($_kValueIsOptionNull || $_kValueIsOptionEmpty) {
                $_kValue = $nullOrEmpty;
            }

            if ($_kValue === $noValue) {
                continue;
            }

            if (in_array($kFieldStrK, $this->fieldsSearchable)) {
                continue; // next
            }

            if ($_kConditionalStr === 'like') {
                $_kValue = '%' . $_kValue . '%';
            }
            if ($_kConditionalStr === 'like>') {
                $_kConditionalStr = 'like';
                $_kValue = $_kValue . '%';
            }
            if ($_kConditionalStr === '<like') {
                $_kConditionalStr = 'like';
                $_kValue = '%' . $_kValue;
            }

            switch ($_kConditionalStr) {
                case 'is-empty':
                case 'not-empty':
                    $isNot = $_kConditionalStr === 'not-empty';
                    $qSub = $model->forNestedWhere();
                    $qSub->whereNull($_kFieldStr, $_kOperatorStr, $isNot)->orWhere($_kFieldStr, $isNot ? '!=' : '=', '');
                    $q->addNestedWhereQuery($qSub->getQuery(), $_kOperatorStr);

                    break;
                case 'is-null':
                case 'not-null':
                    $isNot = $_kConditionalStr === 'not-null';
                    $q->whereNull($_kFieldStr, $_kOperatorStr, $isNot);

                    break;
                case 'in':
                case 'not-in':
                    $isNot = $_kConditionalStr === 'not-in';
                    $q->whereIn($_kFieldStr, $_kValue, $_kOperatorStr, $isNot);

                    break;
                default:
                    $q->where($_kFieldStr, $_kConditionalStr, [$_kValue], $_kOperatorStr);

                    break;
            }
        } // end for ...

        if ($query == '_nested_') {
            return $q;
        }
        if ($hasMq) {
            $qw = $this->applyWithFile($model);
            if (property_exists($qw, 'query')) {
                $q->addNestedWhereQuery($qw->query, $qw->prevOperator);
            }
        }

        if ($query == '_ini_') {
            $model = $model->addNestedWhereQuery($q->getQuery());
        }
    }


    public function applyWithFile(&$model)
    {

        $table                = $model->getModel()->getTable();
        $massiveQ             = $this->request->get('mq');
        $conditions           = isset($massiveQ['conditions']) ? $massiveQ['conditions'] : null;
        $conditions           = json_decode(urldecode($conditions));
        $massiveQueryFileName = isset($massiveQ['massiveWithFile']) ? $massiveQ['massiveWithFile'] : '';
        $exactSearch          = isset($massiveQ['exactSearch']) ? ($massiveQ['exactSearch'] === 'true') : false;
        $rCountry             = $this->request->header('r-country', '');
        $basename             = basename($massiveQueryFileName);
        $fileTempName         = pathinfo($basename, PATHINFO_FILENAME);
        $baseAssets           = 'assets/adm';

        if ($rCountry) {
            $baseAssets = $baseAssets . '/' . $rCountry;
        }

        $path    = "$baseAssets/temporals/$fileTempName/$table/massive-with-file/$massiveQueryFileName";
        $columns = [];

        if (!Storage::disk('public')->exists($path)) {
            throw new \Juanfv2\BaseCms\Exceptions\NoReportException("Archivo no encontrado: '{$massiveQueryFileName}'");
        }

        try {
            ini_set('auto_detect_line_endings', true);

            $dataCombined = [];
            $_versionsCsv_File = Storage::disk('public')->path($path);

            if (($handle = fopen($_versionsCsv_File, "r")) !== false) {

                $delimiter    = _file_delimiter($_versionsCsv_File);

                while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    $c = 0;
                    if ($exactSearch) {
                        // $dataCombined[] =  $conditions _array_combine($columns, $data);
                        $temp = json_decode(json_encode($conditions));
                        foreach ($data as $k => $d) {
                            if ($d !== null && $d !== '') {
                                $temp[$c]->v = $d;
                            }
                            $c++;
                        }
                        $dataCombined[] = $temp;
                    } else {
                        foreach ($data as $k => $d) {
                            if ($d !== null && $d !== '') {
                                $columns[$c][] = $d;
                            }
                            $c++;
                        }
                    }
                }
                fclose($handle);
            }

            if ($exactSearch) {
                // logger(__FILE__ . ':' . __LINE__ . ' $dataCombined ', [json_encode($dataCombined)]);
                $result = new stdClass;
                $result->prevOperator = 'AND';
                $q = $this->mGroup($model, $table, $dataCombined, '_nested_', 'OR', false);
                $result->query = $q->getQuery();

                return $result;
            }
            if (is_array($conditions) && $columns) {
                return $this->mGroupWithFile($model, $conditions, $columns);
            }

            ini_set('auto_detect_line_endings', false);
        } catch (\Throwable $th) {
            // throw $th;
            // logger(__FILE__ . ':' . __LINE__ . ' $th ', [$th]);
            // return $model;
        }

        return null;
    }

    private function mGroupWithFile(&$model, $conditions, $columns)
    {
        $q = $model->forNestedWhere();
        $result = new stdClass;
        $result->prevOperator = 'AND';

        for ($i = 0; $i < count($conditions); $i++) {


            $column     = $columns[$i];
            $_condition = $conditions[$i];
            $condition  = explode(' ', $_condition->c);
            $cCount     = count($condition);

            if ($cCount != 3) {
                continue;
            }

            list($kOperatorStr, $kFieldStr, $kConditionalStr) = $condition;

            if ($i == 0) {
                $result->prevOperator = $kOperatorStr;
            }


            $qSub = $model->forNestedWhere();
            if ($kConditionalStr == 'like') {
                foreach ($column as $key) {
                    $qSub->where($kFieldStr, $kConditionalStr, ["%$key%"], 'OR');
                }
            } else {
                $isNot = $kConditionalStr === '!=';
                $qSub->whereIn($kFieldStr, $column, $kOperatorStr, $isNot);
            }

            $q->addNestedWhereQuery($qSub->getQuery(), $kOperatorStr);
        }

        $result->query = $q->getQuery();

        return $result;
    }

    public static function conditionz($conditions = [], $_kOperatorStrNested = '')
    {
        if ($conditions) {

            // $_kOperatorStrNested = null; // $kOperator AND, OR ...

            $where = $_kOperatorStr = '';
            $_kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...

            foreach ($conditions as $index => $k) {

                $_kOperatorStr = $_kOperatorStr == '' ? 'AND' : $_kOperatorStr;
                $_kOperatorStrNested = $_kOperatorStrNested == '' ? 'AND' : $_kOperatorStrNested;

                if ($index == 0) {
                    $_kOperatorStr = $_kOperatorStrNested = '';
                }

                if (is_array($k)) {
                    $where .= " $_kOperatorStrNested (" . RequestGenericCriteria::conditionz($k, 'OR') . ')';
                    continue; // continuar con el siguiente.
                }
                $condition = explode(' ', $k->c);

                switch (count($condition)) {
                    case 3:
                        list($_kOperatorStr, $kFieldStr, $_kConditionalStr) = $condition;
                        break;
                    case 2:
                        list($_kOperatorStr, $kFieldStr) = $condition;
                        break;
                    default:
                        list($kFieldStr) = $condition;
                }
                if ($kFieldStr === 'OR') {
                    $_kOperatorStrNested = 'OR';
                    continue; // next
                }
                if ($index == 0) {
                    $_kOperatorStr = '';
                }
                $noValue = '--false--';
                $_kValue = property_exists($k, 'v') ? $k->v : $noValue;
                $_kValueIsOptionNull = strpos($_kConditionalStr, 'null') !== false;

                if (!$_kValueIsOptionNull && $_kValue === $noValue) {
                    continue;
                }

                if ($_kConditionalStr === 'like') {
                    $_kValue = '%' . $_kValue . '%';
                }
                if ($_kConditionalStr === 'like>') {
                    $_kConditionalStr = 'like';
                    $_kValue = $_kValue . '%';
                }
                if ($_kConditionalStr === '<like') {
                    $_kConditionalStr = 'like';
                    $_kValue = '%' . $_kValue;
                }

                if ($_kValueIsOptionNull) {
                    $isNot = $_kConditionalStr === 'not-null' ? ' NOT' : '';
                    $where .= " $_kOperatorStr $kFieldStr$isNot $_kConditionalStr '$_kValue'";
                } elseif (strpos($_kConditionalStr, 'in') !== false) {
                    $isNot = $_kConditionalStr === 'not-in' ? ' NOT' : '';
                    $inStr = implode("','", $_kValue);
                    $where .= " $_kOperatorStr $kFieldStr$isNot $_kConditionalStr ('$inStr')";
                } else {
                    $where .= " $_kOperatorStr $kFieldStr $_kConditionalStr '$_kValue'";
                }
            }

            return $where;
        } else {
            return '';
        }
    }
}
