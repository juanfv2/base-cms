<?php

namespace Juanfv2\BaseCms\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenericResourceCollection extends AnonymousResourceCollection
{
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
     * @param  array  $includes
     * @return void
     */
    public function __construct($resource, public $collects, public $includes)
    {
        parent::__construct($resource, $collects);
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
    {
        return $this->collection->map->toArray($request, $this->includes)->all();
    }
}
