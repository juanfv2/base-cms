<?php

namespace Juanfv2\BaseCms\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenericResourceCollection extends AnonymousResourceCollection
{
    public $includes;

    /**
     * The name of the resource being collected.
     *
     * @var string
     */
    public $collects;

    /**
     * The mapped collection instance.
     *
     * @var \Juanfv2\BaseCms\Resources\GenericResource
     */
    public $collection;

    /**
     * Create a new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @param  string  $collects
     * @return void
     */
    public function __construct($resource, $collects, $includes)
    {
        $this->collects = $collects;

        $this->includes = $includes;

        parent::__construct($resource, $collects);
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->map->toArray($request, $this->includes)->all();
    }
}
