<?php


namespace models;


use ProgressTrait;
use vos\Amount;

class Milestone
{
    use ProgressTrait;

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $url;
    /**
     * @var int
     */
    private $no;

    /**
     * @var Issue[]
     */
    private $issues_active = [];

    /**
     * @var Issue[]
     */
    private $issues_queued = [];

    /**
     * @var Issue[]
     */
    private $issues_completed = [];

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $title, string $url, int $no, Amount $closed_issues_count, Amount $opened_issues_count)
    {
        $this->title = $title;
        $this->url = $url;
        $this->no = $no;
        $this->completed = $closed_issues_count;
        $this->remaining = $opened_issues_count;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNo(): int
    {
        return $this->no;
    }

    /**
     * TODO move sorting algorithm to external Strategy class
     *
     * @param Issue[] $issues
     */
    public static function sortIssues(array $issues)
    {
        usort(
            $issues,
            function (Issue $a, Issue $b) {
                $count_a = count($a->getPausedLabels());
                $count_b = count($b->getPausedLabels());

                return $count_a === $count_b
                    ? strcmp($a->getTitle(), $b->getTitle())
                    : $count_a - $count_b;
            }
        );

        return $issues;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRaw(): array
    {
        return [
            'milestone'            => $this->title,
            'url'                  => $this->url,
            'progress'             => $this->getProgess(),
            Issue::STATE_QUEUED    => array_map(
                function (Issue $issue) {
                    return $issue->getRaw();
                },
                $this->issues_queued
            ),
            Issue::STATE_ACTIVE    => array_map(
                function (Issue $issue) {
                    return $issue->getRaw();
                },
                static::sortIssues($this->issues_active)
            ),
            Issue::STATE_COMPLETED => array_map(
                function (Issue $issue) {
                    return $issue->getRaw();
                },
                $this->issues_completed
            ),
        ];
    }

    /**
     * @param Issue[] $issues
     * @throws \Exception
     */
    public function addIssues(array $issues): void
    {
        foreach ($issues as $issue) {
            $this->addIssue($issue);
        }
    }

    public function addIssue(Issue $issue): void
    {
        switch ($state = $issue->getState()) {
            case Issue::STATE_COMPLETED;
                $this->issues_completed[] = $issue;
                break;
            case Issue::STATE_QUEUED;
                $this->issues_queued[] = $issue;
                break;
            case Issue::STATE_ACTIVE;
                $this->issues_active[] = $issue;
                break;
            default:
                throw new \Exception(sprintf('Unknown issue state [%s]', $state));
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}