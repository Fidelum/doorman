<?php

namespace AsyncPHP\Doorman\Manager;

use AsyncPHP\Doorman\Manager;
use AsyncPHP\Doorman\Task;

class GroupProcessManager implements Manager
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Task[][]
     */
    protected $waiting = array();

    /**
     * @var Task[][]
     */
    protected $queuing = array();

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     *
     * @param Task $task
     *
     * @return $this
     */
    public function addTask(Task $task)
    {
        array_push($this->waiting, array($task));

        return $this;
    }

    /**
     * Adds a group of tasks to be handled.
     *
     * @param Task[] $tasks
     *
     * @return $this
     */
    public function addTaskGroup(array $tasks)
    {
        foreach ($tasks as $task) {
            assert($task instanceof Task);
        }

        array_push($this->waiting, $tasks);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function tick()
    {
        if (!empty($this->queuing)) {
            $this->manager->addTask(array_shift($this->queuing));
        }

        if ($this->manager->tick()) {
            return true;
        }

        if (empty($this->waiting)) {
            return false;
        }

        $this->queuing = array_shift($this->waiting);

        return true;
    }

    /**
     * Passes missing method calls to the decorated manager.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array(array($this->manager, $method), $parameters);
    }
}
