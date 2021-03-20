<?php


namespace models;

use ProgressTrait;
use vos\Amount;

class Issue
{
    use ProgressTrait;

    const GIT_STATE_CLOSED = 'closed';
    const STATE_COMPLETED = 'completed';
    const STATE_ACTIVE = 'active';
    const STATE_QUEUED = 'queued';

    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $no;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $body;
    /**
     * @var string
     */
    private $url;
    /**
     * @var ?string
     */
    private $assignee;
    /**
     * @var bool
     */
    private $paused;
    /**
     * @var ?string
     */
    private $closed;
    /**
     * @var string
     */
    private $git_state;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        int $id,
        int $no,
        string $title,
        string $git_state,
        string $body,
        string $url,
        ?string $assignee,
        bool $paused,
        Amount $completed,
        Amount $remaining,
        ?string $closed
    ) {
        $this->id = $id;
        $this->no = $no;
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->assignee = $assignee;
        $this->poused = $paused;
        $this->closed = $closed;
        $this->git_state = $git_state;
        $this->completed = $completed;
        $this->remaining = $remaining;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getState(): string
    {
        if ($this->git_state === static::GIT_STATE_CLOSED) {
            return static::STATE_COMPLETED;
        } elseif (null !== $this->assignee) {
            return static::STATE_ACTIVE;
        } else {
            return static::STATE_QUEUED;
        }
    }

    public static function labels_match(array $labels, array $needles): bool
    {
        return !empty(array_intersect($labels, $needles));
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRaw()
    {
        return [
            'id'       => $this->id,
            'number'   => $this->no,
            'title'    => $this->title,
            'body'     => $this->body,
            'url'      => $this->url,
            'assignee' => $this->assignee,
            'paused'   => $this->paused,
            'progress' => $this->getProgess(),
            'closed'   => $this->closed
        ];
    }
}