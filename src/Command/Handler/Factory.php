<?php namespace BoundedContext\Memory\Command\Handler;

use BoundedContext\Collection\Collection;
use BoundedContext\Contracts\Command;
use BoundedContext\Repository\Repository;
use BoundedContext\ValueObject\Uuid;
use DI\Container;

class Factory
{
    protected $container;

    public function __construct(Container $container, Command $command)
    {
        $this->container = $container;
        $this->command = $command;
    }

    public function generate()
    {
        $command_class = get_class($this->command);

        $aggregate_namespace = substr($command_class, 0, strpos($command_class, "Command"));

        $handler_class = $aggregate_namespace . "Handler";
        $aggregate_class = $aggregate_namespace . "Aggregate";
        $state_class = $aggregate_namespace . "State";

        $repository = new Repository(
            $this->container->get('EventLog'),
            $this->container->get('BoundedContext\Projection\AggregateCollections\Projection'),
            new $aggregate_class(Uuid::null(), new $state_class, new Collection())
        );

        $namespaced_handler_class = '\\' . $handler_class;
        $namespaced_handler_class = new \ReflectionClass($namespaced_handler_class);
        $parameters = $namespaced_handler_class->getMethods()[0]->getParameters();

        if(count($parameters) == 1)
        {
            return new $handler_class($repository);
        }

        array_shift($parameters);

        $params_array = [];
        foreach($parameters as $parameter)
        {
            $params_array[] = $this->container->get($parameter->getClass()->getName());
        }

        array_unshift($params_array, $repository);

        $r = new \ReflectionClass($handler_class);

        return $r->newInstanceArgs($params_array);
    }
}
