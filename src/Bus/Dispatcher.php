<?php

namespace BoundedContext\Memory\Bus;

use BoundedContext\Memory\Command\Handler;
use BoundedContext\Collection\Collection;
use BoundedContext\Contracts\Command;
use DI\Container;

class Dispatcher implements \BoundedContext\Contracts\Dispatcher
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch(Command $command)
    {
        $handler = (new Handler\Factory($this->container, $command))->generate();
        $handler->handle($command);

        $player = new Projector\Player($this->container);
        $player->play();

        $command_log = $this->container->make('CommandLog');
        $command_log->append($command);
    }

    public function dispatch_collection(Collection $commands)
    {
        foreach($commands as $command)
        {
            $this->dispatch($command);
        }
    }


}