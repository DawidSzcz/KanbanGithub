<?php

namespace models;

class Repository
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $milestones = [];

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @codeCoverageIgnore
     */
    public function addMilestone(Milestone $milestone): void
    {
        $this->milestones[] = $milestone;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Milestone[]
     */
    public function getRawMilestones(): array
    {
        return array_map(
            function (Milestone $milestone) {
                return $milestone->getRaw();
            },
            $this->milestones
        );
    }
}