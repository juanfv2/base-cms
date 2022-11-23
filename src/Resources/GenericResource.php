<?php

namespace Juanfv2\BaseCms\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class GenericResource extends JsonResource
{
    public $includes;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $ownIncludes = null)
    {
        $this->includes = $ownIncludes;

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request, $ownIncludes = null)
    {
        $data = parent::toArray($request);

        $this->includes = $ownIncludes ?: $this->includes;

        if ($request->has('includes')) {
            $arr = $request['includes'] = is_string($request['includes']) ? json_decode($request['includes'], true) : $request['includes'];

            // logger(__FILE__ . ':' . __LINE__ . ' $arr ', [$arr]);
            // logger(__FILE__ . ':' . __LINE__ . ' $this->includes ', [$this->includes]);

            if (is_array($this->includes)) {
                $arr = $this->includes[0] == 'reset' ? null : $this->includes;
            }

            // logger(__FILE__ . ':' . __LINE__ . ' $arr ', [$arr]);

            if ($arr && is_array($arr)) {
                foreach ($arr as $kValue => $kProperty) {
                    if ($this->hidden && in_array($kProperty, $this->hidden)) {
                        continue;
                    }

                    $realProperty = $kProperty;
                    $includes = ['reset'];
                    if (is_array($kProperty)) {
                        $realProperty = array_keys($kProperty)[0];
                        $includes = $kProperty[$realProperty];
                    }

                    // logger(__FILE__ . ':' . __LINE__ . '. $realProperty   .', [$this->id, $realProperty]);
                    // logger(__FILE__ . ':' . __LINE__ . '. $kProperty      .', [$kProperty, $kValue]);
                    // logger(__FILE__ . ':' . __LINE__ . '. $this->resource .', [$this->resource]);

                    $this->add2data($data, $realProperty, $includes);
                }
            }
        }

        return $data;
    }

    private function add2data(&$data, $realProperty, $includes)
    {
        $rValue = $this->$realProperty;
        if ($rValue instanceof Model) {
            $data[$realProperty] = new GenericResource($rValue, $includes);
        } elseif ($rValue instanceof Collection) {
            $data[$realProperty] = GenericResource::coll($rValue, $includes);
        } elseif ($rValue) {
            $data[$realProperty] = $rValue;
        }
    }

    /**
     * Create new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function coll($resource, $includes)
    {
        return new GenericResourceCollection($resource, get_called_class(), $includes);
    }
}
