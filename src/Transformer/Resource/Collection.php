<?php

declare(strict_types=1);

namespace Transformer\Resource;

use Transformer\Arrayable;

class Collection implements Arrayable
{
    /** @var Arrayable[] */
    protected $objects;
    /** @var ResourceAbstract */
    protected $resource;
    /** @var \Closure */
    protected $afterCreate;

    public function __construct(array $objects, ResourceAbstract $resource)
    {
        $this->objects = $objects;
        $this->resource = $resource;
    }

    /**
     *
     * @param array $objects
     * @param Object|string $resource
     * @param \Closure $afterCreate
     * @return Collection
     */
    public static function create(array $objects, $resource, \Closure $afterCreate = null): Collection
    {
        try {
            if (is_string($resource)) {
                $resource = new $resource(null);
            }
        } catch (\Error $e) {
            throw new ResourceException(sprintf('Resource "%s" not found for collection', $resource), 0, $e);
        }
        if (!($resource instanceof ResourceAbstract)) {
            throw new ResourceException(sprintf('Class "%s" is not ResourceAbstract', get_class($resource)));
        }
        return (new Collection($objects, $resource))->setAfterCreate($afterCreate);
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->objects as $object) {
            if ($this->afterCreate) {
                // Should use callback for every Resource.
                $resource = $this->resource->setObject($object);
                ($this->afterCreate)($resource);
                $data[] = $resource->toArray();
            } else {
                $data[] = $this->resource->setObject($object)->toArray();
            }
        }
        return $data;
    }

    public function setAfterCreate(\Closure $afterCreate = null): Collection
    {
        $this->afterCreate = $afterCreate;
        return $this;
    }
}
