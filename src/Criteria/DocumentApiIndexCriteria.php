<?php

namespace Juanfv2\BaseCms\Criteria;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Contracts\CriteriaInterface;
use Juanfv2\BaseCms\Contracts\RepositoryInterface;

/**
 * Class LimitOffsetCriteria
 * @package namespace Juanfv2\BaseCms\Criteria
 */
class DocumentApiIndexCriteria implements CriteriaInterface
{

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

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
        $massiveQ             = $this->request->get('mq');
        $queries              = isset($massiveQ['queries']) ? $massiveQ['queries'] : null;
        $massiveQueryFileName = isset($massiveQ['massiveQuery']) ? $massiveQ['massiveQuery'] : '';
        $massiveQueryModel    = isset($massiveQ['model']) ? $massiveQ['model'] : '';
        $massiveQueryFile     = 'pulic/assets/adm/files/' . $massiveQueryModel . '/massiveQuery/' . $massiveQueryFileName;
        $columns              = array();

        try {
            ini_set('auto_detect_line_endings', true);
            if (($handle = fopen($massiveQueryFile, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $c = 0;
                    foreach ($data as $k => $d) {
                        if ($d !== null && $d !== '') {
                            $columns[$c][] = $d;
                        }
                        $c++;
                    }
                }
                fclose($handle);
            }
            ini_set('auto_detect_line_endings', false);
        } catch (\Throwable $th) {
            // throw $th;
            // logger(__FILE__ . ':' . __LINE__ . ' $th ', [$th]);
            return $model;
        }

        if ($queries) {
            $conditions = json_decode($queries);
            // dd($queries);
            $i = 0;
            foreach ($conditions as $k) {
                if (is_array($k)) {
                    $model = $model->where(function ($q) use ($k, $i, $columns) {
                        $j = 0;

                        foreach ($k as $key) {
                            $kOperatorStr = 'AND'; // $kOperator AND, OR ...
                            $kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...
                            $kFieldStr = '';
                            $condition = explode(' ', $key->condition);
                            switch (count($condition)) {
                                case 3:
                                    list($kOperatorStr, $kFieldStr, $kConditionalStr) = $condition;
                                    break;
                                default:
                                    continue;
                            }

                            if ($kConditionalStr === 'like') {
                                $q->where(function ($q2) use ($columns, $kFieldStr, $kConditionalStr, $j) {
                                    if (isset($columns[$j])) {
                                        foreach ($columns[$j] as $value) {
                                            $value = '%' . $value . '%';
                                            // $q->orWhere($kField, $value);
                                            $q2->where($kFieldStr, $kConditionalStr, [$value], 'OR');
                                        }
                                    }
                                });
                            } elseif ($kConditionalStr === '=') {
                                $isNot = $kConditionalStr === '!=';

                                $q->whereIn($kFieldStr, $columns[$j], $kOperatorStr, $isNot);
                            }
                            $j++;
                        } // end for ...
                    });
                    continue;
                }

                $kOperatorStr = 'AND'; // $kOperator AND, OR ...
                $kConditionalStr = '='; // $kConditional =, LIKE, >, <, =>, ...
                $kFieldStr = '';
                $condition = explode(' ', $k->condition);
                switch (count($condition)) {
                    case 3:
                        list($kOperatorStr, $kFieldStr, $kConditionalStr) = $condition;
                        break;
                    default:
                        continue;
                }
                if ($kConditionalStr === 'like') {
                    $model->where(function ($q) use ($columns, $kFieldStr, $kConditionalStr, $i) {
                        foreach ($columns[$i] as $value) {
                            $value = '%' . $value . '%';
                            // $q->orWhere($kField, $value);
                            $q->where($kFieldStr, $kConditionalStr, [$value], 'OR');
                        }
                    });
                } elseif ($kConditionalStr === '=') {
                    $isNot = $kConditionalStr === '!=';

                    $model = $model->whereIn($kFieldStr, $columns[$i], $kOperatorStr, $isNot);
                    // continue;
                }
                $i++;
            } // end for...
        }

        return $model;
    }
}
