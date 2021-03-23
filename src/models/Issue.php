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
     * @var string
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
     * @var array
     */
    private $paused_labels;
    /**
     * @var ?string
     */
    private $closed_at;
    /**
     * @var string
     */
    private $git_state;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(
        string $id,
        int $no,
        string $title,
        string $git_state,
        string $body,
        string $url,
        ?string $assignee,
        array $paused_labels,
        Amount $completed,
        Amount $remaining,
        ?string $closed_at
    ) {
        $this->id = $id;
        $this->no = $no;
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->assignee = $assignee;
        $this->paused_labels = $paused_labels;
        $this->closed_at = $closed_at;
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
            'paused'   => $this->paused_labels,
            'progress' => $this->getProgess(),
            'closed'   => $this->closed_at
        ];
    }

    public function getPausedLabels(): array
    {
        return $this->paused_labels;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}