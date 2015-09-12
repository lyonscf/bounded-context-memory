<?php

namespace BoundedContext\Memory\Projection;

use BoundedContext\Collection\Collection;
use BoundedContext\Log\Item;
use BoundedContext\Projection\AggregateCollections\Projection;
use BoundedContext\ValueObject\Uuid;

class AggregateCollections implements Projection
{
    private $aggregates;

    public function __construct()
    {
        $this->aggregates = [];
    }

    public function exists(Uuid $id)
    {
        return array_key_exists($id->serialize(), $this->aggregates);
    }

    public function reset()
    {
        $this->aggregates = [];
    }

    public function get(Uuid $id)
    {
        if(!$this->exists($id))
        {
            throw new \Exception("Aggregate [".$id->serialize()."] does not exist.");
        }

        return $this->aggregates[$id->serialize()];
    }

    public function append(Item $item)
    {
        $aggregate_id = $item->payload()->id();

        if(!$this->exists($aggregate_id))
        {
            $this->aggregates[$aggregate_id->serialize()] = new Collection();
        }

        $this->aggregates[$aggregate_id->serialize()]->append($item);
    }

    public function append_collection(Collection $items)
    {
        foreach($items as $item)
        {
            $this->append($item);
        }
    }
}
