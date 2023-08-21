<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Contracts\CriteriaInterfaceModel;
use stdClass;

interface Trashed
{
    public const Without = 0;

    public const Only = 1;

    public const With = 2;
}

/**
 * Class RequestGenericCriteria
 */
class RequestCriteriaModel implements CriteriaInterfaceModel
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    protected $model;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(&$model)
    {
        $this->model = $model;
        $conditions = $this->request->get('conditions', '[]');
        $joins = $this->request->get('joins', '[]');
        $select1 = $this->request->get('select', '');
        $sorts = $this->request->get('sorts', '[]');
        $withCount = $this->request->get('withCount', '[]');
        $with = $this->request->get('with', '[]');
        $onlyTrashed = (int) $this->request->get('trashed', Trashed::Without);
        $conditions = json_decode(urldecode((string) $conditions), null, 512, JSON_THROW_ON_ERROR);
        $joins = json_decode(urldecode((string) $joins), null, 512, JSON_THROW_ON_ERROR);
        $sorts = json_decode(urldecode((string) $sorts), null, 512, JSON_THROW_ON_ERROR);
        $withCount = json_decode(urldecode((string) $withCount), null, 512, JSON_THROW_ON_ERROR);
        $with = json_decode(urldecode((string) $with), null, 512, JSON_THROW_ON_ERROR);
        $select2 = _isJson($select1) ? json_decode(urldecode((string) $select1), null, 512, JSON_THROW_ON_ERROR) : null;
        $select = $select2 ?: ($select1 ? explode(',', urldecode((string) $select1)) : null);

        // logger(__FILE__ . ':' . __LINE__ . ' $this->request ', [$this->request]);

        $this->mNestedWhereQuery($conditions, '_ini_', 'and', $this->request->has('mq.massiveWithFile'));

        $this->mJoins($joins);

        $this->mSelect($select);

        if ($withCount) {
            $this->model->getJQuery()->withCount($withCount);
        }

        if ($with) {
            $this->model->getJQuery()->with($with);
        }

        if ($sorts) {
            foreach ($sorts as $k) {
                $this->model->getJQuery()->orderBy($k->field, ($k->order == 1 ? 'asc' : 'desc'));
            }
        }

        $this->mTrashed($onlyTrashed);
    }

    private function mNestedWhereQuery($conditions, $query = null, $_kOperatorStrParam = 'AND', $hasMq = false)
    {
        if (is_array($conditions)) {
            $q = $this->model->forNestedWhere();
            foreach ($conditions as $k) {
                // logger(__FILE__ . ':' . __LINE__ . ' inner $k ', [$k]);
                if (is_array($k)) {
                    $qw = $this->mNestedWhereQuery($k, '_nested_', $_kOperatorStrParam);
                    $q->addNestedWhereQuery($qw->getQuery(), $_kOperatorStrParam);

                    continue; // continuar con el siguiente.
                }

                $noValue = '--false--';
                $nullOrEmpty = '---';
                $_kOperatorStr = 'AND';
                $_kFieldStr = null;
                $_kConditionalStr = '=';
                $condition = explode(' ', (string) $k->c);

                switch (count($condition)) {
                    case 3:
                        $_kOperatorStr = $condition[0];
                        $_kFieldStr = $condition[1];
                        $_kConditionalStr = $condition[2];
                        // [$_kOperatorStr, $_kFieldStr, $_kConditionalStr] = $condition;
                        break;
                    case 2:

                        $_kOperatorStr = $condition[0];
                        $_kFieldStr = $condition[1];
                        // [$_kOperatorStr, $_kFieldStr] = $condition;
                        break;
                    default:
                        $_kFieldStr = $condition[0];
                        // [$_kFieldStr] = $condition;
                }

                if ($_kFieldStr === 'OR') {
                    $_kOperatorStrParam = 'OR';

                    continue; // next
                }

                $_kValue = property_exists($k, 'v') ? $k->v : $noValue;
                $_kValueIsOptionNull = str_contains($_kConditionalStr, 'null');
                $_kValueIsOptionEmpty = str_contains($_kConditionalStr, 'empty');
                $kFieldStrK = str_replace("{$this->model->getTable()}.", '', $_kFieldStr);

                if ($_kValueIsOptionNull || $_kValueIsOptionEmpty) {
                    $_kValue = $nullOrEmpty;
                }

                if ($_kValue === $noValue) {
                    continue;
                }

                if (in_array($kFieldStrK, $this->model->hidden ?? [])) {
                    continue; // next
                }

                if ($_kConditionalStr === 'like') {
                    $_kValue = '%'.$_kValue.'%';
                }
                if ($_kConditionalStr === 'like>') {
                    $_kConditionalStr = 'like';
                    $_kValue = $_kValue.'%';
                }
                if ($_kConditionalStr === '<like') {
                    $_kConditionalStr = 'like';
                    $_kValue = '%'.$_kValue;
                }

                switch ($_kConditionalStr) {
                    case 'is-empty':
                    case 'not-empty':
                        $isNot = $_kConditionalStr === 'not-empty';
                        $qSub = $this->model->forNestedWhere();
                        $qSub->whereNull($_kFieldStr, $_kOperatorStr, $isNot)->orWhere($_kFieldStr, $isNot ? '!=' : '=', '');
                        $q->addNestedWhereQuery($qSub->getQuery(), $_kOperatorStr);

                        break;
                    case 'null':
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
                $qw = $this->applyWithFile($this->model);
                if ($qw && property_exists($qw, 'query')) {
                    $q->addNestedWhereQuery($qw->query, $qw->prevOperator);
                }
            }

            if ($query == '_ini_') {
                $this->model->getJQuery()->addNestedWhereQuery($q->getQuery());
            }
        }
    }

    private function mJoins($joins)
    {
        if (is_array($joins)) {
            foreach ($joins as $k) {
                $split = explode('.', (string) $k->c);
                if (count($split) < 3) {
                    continue;
                }

                $joinType = '';
                $joinTable = $split[0];
                $foreignKey = $split[1];

                switch (count($split)) {
                    case 5:
                        $ownTable = $split[2];
                        $ownerKey = $split[3];
                        $joinType = $split[4];
                        break;
                    case 4:
                        $ownTable = $split[2];
                        $ownerKey = $split[3];
                        if (strlen($split[3]) == 1) {
                            $joinType = $split[3];
                        }
                        break;
                    default:
                        $ownTable = $this->model->getTable();
                        $ownerKey = $split[2];

                        break;
                }

                match ($joinType) {
                    '<' => $this->model->getJQuery()->leftJoin($joinTable, $joinTable.'.'.$foreignKey, '=', $ownTable.'.'.$ownerKey),
                    '>' => $this->model->getJQuery()->leftJoin($joinTable, $joinTable.'.'.$foreignKey, '=', $ownTable.'.'.$ownerKey),
                    default => $this->model->getJQuery()->join($joinTable, $joinTable.'.'.$foreignKey, '=', $ownTable.'.'.$ownerKey),
                };

                if (isset($k->v)) {
                    $this->model->getJQuery()->addSelect($k->v);
                }
            } // end for ...
        }
    }

    private function mSelect($select)
    {
        if (is_array($select)) {
            foreach ($select as $k) {
                if (is_string($k)) {
                    $this->model->getJQuery()->addSelect($k);
                } else {
                    $this->model->getJQuery()->selectRaw($k->v);
                    if ($k->c == 'GROUP-BY') {
                        $this->model->getJQuery()->groupBy($k->v);
                    }
                }
            }
        } else {
            $this->model->getJQuery()->addSelect($this->model->getTable().'.*');
        }
    }

    private function mTrashed($trashed)
    {
        switch ($trashed) {
            case Trashed::Only:
                $this->model->getJQuery()->onlyTrashed();
                break;

            case Trashed::With:
                $this->model->getJQuery()->withTrashed();
                break;
            default:
                // code...
                break;
        }
    }

    public function applyWithFile(&$model)
    {
        $this->model = $model;
        $massiveQ = $this->request->get('mq');
        $conditions = $massiveQ['conditions'] ?? null;
        $conditions = json_decode(urldecode((string) $conditions), null, 512, JSON_THROW_ON_ERROR);
        $massiveQueryFileName = $massiveQ['massiveWithFile'] ?? '';
        $exactSearch = isset($massiveQ['exactSearch']) ? ($massiveQ['exactSearch'] === 'true') : false;
        $rCountry = $this->request->header('r-country', $this->request->get('rCountry', session('r-country', '.l.')));
        $basename = basename((string) $massiveQueryFileName);
        $fileTempName = pathinfo($basename, PATHINFO_FILENAME);
        $baseAssets = 'assets/adm';

        if ($rCountry) {
            $baseAssets = $baseAssets.'/'.$rCountry;
        }

        $path = "$baseAssets/temporals/$fileTempName/{$this->model->getTable()}/massive-with-file/$massiveQueryFileName";
        $columns = [];

        if (! Storage::disk('public')->exists($path)) {
            throw new \Juanfv2\BaseCms\Exceptions\NoReportException("Archivo no encontrado: '{$massiveQueryFileName}'");
        }

        try {
            ini_set('auto_detect_line_endings', true);

            $dataCombined = [];
            $_versionsCsv_File = Storage::disk('public')->path($path);

            if (($handle = fopen($_versionsCsv_File, 'r')) !== false) {
                $delimiter = _file_delimiter($_versionsCsv_File);

                $limit = 1000;
                $rowIndex = 0;
                while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    $c = 0;
                    if ($exactSearch) {
                        // $dataCombined[] =  $conditions _array_combine($columns, $data);
                        $temp = json_decode(json_encode($conditions, JSON_THROW_ON_ERROR), null, 512, JSON_THROW_ON_ERROR);
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

                    if ($rowIndex == $limit) {
                        break;
                    }

                    $rowIndex++;
                }

                fclose($handle);
            }

            if ($exactSearch) {
                // logger(__FILE__ . ':' . __LINE__ . ' $dataCombined ', [json_encode($dataCombined)]);
                $result = new stdClass;
                $result->prevOperator = 'AND';
                $q = $this->mNestedWhereQuery($dataCombined, '_nested_', 'OR', false);
                $result->query = $q->getQuery();

                return $result;
            }
            if (is_array($conditions) && $columns) {
                return $this->mGroupWithFile($conditions, $columns);
            }

            ini_set('auto_detect_line_endings', false);
        } catch (\Throwable) {
            // throw $th;
            // logger(__FILE__ . ':' . __LINE__ . ' $th ', [$th]);
            // return $model;
        }

        return null;
    }

    private function mGroupWithFile($conditions, $columns)
    {
        $q = $this->model->forNestedWhere();
        $result = new stdClass;
        $result->prevOperator = 'AND';

        for ($i = 0; $i < (is_countable($conditions) ? count($conditions) : 0); $i++) {
            $column = $columns[$i];
            $_condition = $conditions[$i];
            $condition = explode(' ', (string) $_condition->c);
            $cCount = count($condition);

            if ($cCount != 3) {
                continue;
            }

            [$kOperatorStr, $kFieldStr, $kConditionalStr] = $condition;

            if ($i == 0) {
                $result->prevOperator = $kOperatorStr;
            }

            $qSub = $this->model->forNestedWhere();
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
                    $where .= " $_kOperatorStrNested (".RequestGenericCriteria::conditionz($k, 'OR').')';

                    continue; // continuar con el siguiente.
                }
                $condition = explode(' ', (string) $k->c);

                [$_kOperatorStr, $kFieldStr, $_kConditionalStr] = match (count($condition)) {
                    3 => $condition,
                    2 => $condition,
                    default => $condition,
                };
                if ($kFieldStr === 'OR') {
                    $_kOperatorStrNested = 'OR';

                    continue; // next
                }
                if ($index == 0) {
                    $_kOperatorStr = '';
                }
                $noValue = '--false--';
                $_kValue = property_exists($k, 'v') ? $k->v : $noValue;
                $_kValueIsOptionNull = str_contains($_kConditionalStr, 'null');

                if (! $_kValueIsOptionNull && $_kValue === $noValue) {
                    continue;
                }

                if ($_kConditionalStr === 'like') {
                    $_kValue = '%'.$_kValue.'%';
                }
                if ($_kConditionalStr === 'like>') {
                    $_kConditionalStr = 'like';
                    $_kValue = $_kValue.'%';
                }
                if ($_kConditionalStr === '<like') {
                    $_kConditionalStr = 'like';
                    $_kValue = '%'.$_kValue;
                }

                if ($_kValueIsOptionNull) {
                    $isNot = $_kConditionalStr === 'not-null' ? ' NOT' : '';
                    $where .= " $_kOperatorStr $kFieldStr$isNot $_kConditionalStr '$_kValue'";
                } elseif (str_contains($_kConditionalStr, 'in')) {
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
