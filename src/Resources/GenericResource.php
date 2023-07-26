<?php

namespace Juanfv2\BaseCms\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class GenericResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, public $includes = null)
    {
        parent::__construct($resource);
    }

    public function toArray($request, $ownIncludes = null)
    {
        $data = parent::toArray($request);

        $this->includes = $ownIncludes ?: $this->includes;

        $includes = $this->getIncludesFromRequest($request);

        if ($includes && is_array($includes)) {
            foreach ($includes as $kProperty) {
                $this->add2data($data, $kProperty);
            }
        }

        return $data;
    }

    private function getIncludesFromRequest($request)
    {
        if (is_array($this->includes)) {
            return $this->includes[0] == 'reset' ? null : $this->includes;
        }

        $includes = $request->input('includes');

        // Parse includes from JSON string if provided as a string
        if (is_string($includes)) {
            $includes = json_decode($includes, true, 512, JSON_ERROR_NONE);
        }

        // logger(__FILE__ . ':' . __LINE__ . ' $arr ', [$arr]);
        // logger(__FILE__ . ':' . __LINE__ . ' $this->includes ', [$this->includes]);

        return $includes;
    }

    private function add2data(&$data, $kProperty)
    {
        $realProperty = $kProperty;
        $nestedIncludes = ['reset'];

        if (is_array($kProperty)) {
            $realProperty = array_keys($kProperty)[0];
            $nestedIncludes = $kProperty[$realProperty];
        }

        // logger(__FILE__ . ':' . __LINE__ . ' $kProperty ', [$kProperty, $realProperty]);

        if ($this->hidden && in_array($realProperty, $this->hidden)) {
            return;
        }

        // logger(__FILE__ . ':' . __LINE__ . '. $realProperty   .', [$this->id, $realProperty]);
        // logger(__FILE__ . ':' . __LINE__ . '. $kProperty      .', [$kProperty, $kValue]);
        // logger(__FILE__ . ':' . __LINE__ . '. $this->resource .', [$this->resource]);

        $rValue = $this->$realProperty;

        if ($rValue instanceof Model) {
            $data[$realProperty] = new GenericResource($rValue, $nestedIncludes);
        } elseif ($rValue instanceof Collection) {
            $data[$realProperty] = new GenericResourceCollection($rValue, static::class, $nestedIncludes);
        } elseif ($rValue) {
            $data[$realProperty] = $rValue;
        }
    }
}
