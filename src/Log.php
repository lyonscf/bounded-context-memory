<?php

namespace BoundedContext\Memory;

use BoundedContext\Collection\Collection;
use BoundedContext\Collection\Collectable;
use BoundedContext\Log\Item;
use BoundedContext\ValueObject\DateTime;
use BoundedContext\ValueObject\Uuid;
use BoundedContext\ValueObject\Version;

class Log implements \BoundedContext\Contracts\Log
{
    private $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function reset()
    {
        $this->items = new Collection();
    }

    public function get_collection(Uuid $id, $limit = 1000)
    {
        $can_collect = 0;
        $items = new Collection();

        if($id->is_null())
        {
            $can_collect = 1;
        }

        foreach($this->items as $item)
        {
            if($id->equals($item->id()))
            {
                $can_collect = 1;
            }

            if($can_collect)
            {
                $items->append($item);
            }

            if($items->count() == $limit)
            {
                return $items;
            }
        }

        return $items;
    }

    public function append(Collectable $event)
    {
        $this->items->append(new Item(
            Uuid::generate(),
            Uuid::generate(),
            new DateTime(),
            new Version(),
            $event
        ));
    }

    public function append_collection(Collection $events)
    {
        foreach($events as $event)
        {
            $this->append($event);
        }
    }
}