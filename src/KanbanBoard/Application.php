<?php

namespace KanbanBoard;

use exceptions\FactoryException;
use interfaces\GitFactoryInterface;
use models\Issue;
use models\Repository;

class Application
{
    /**
     * @var Repository[]
     */
    private $repositories;
    /**
     * @var string[]
     */
    private $repository_names;

    /**
     * @var GitFactoryInterface
     */
    private $git_factory;
    /**
     * @var GithubClient
     */
    private $github;

    /**
     * @param string[] $repository_names
     * @param string[] $paused_labels
     */
    public function __construct(GithubClient $github, array $repository_names, GitFactoryInterface $git_factory, array $paused_labels = [])
    {
        $this->github = $github;
        $this->repository_names = $repository_names;
        $this->paused_labels = $paused_labels;
        $this->git_factory = $git_factory;
        $this->repositories = [];
    }

    public function run(): void
    {
        foreach ($this->repository_names as $repository_name) {
            $repository = $this->git_factory->createRepository($repository_name);

            foreach ($this->github->milestones($repository_name) as $ms_no => $ms_data) {
                try {
                    $milestone = $this->git_factory->createMilestone($ms_data['title'], $ms_data['html_url'], $ms_data['number'], $ms_data['closed_issues'], $ms_data['open_issues']);
                } catch (FactoryException $e) {
                    continue;
                }
                $milestone->addIssues($this->processIssues($repository_name, $milestone->getNo()));
                $repository->addMilestone($milestone);
                $this->repositories[] = $repository;
            }
        }
    }

    /**
     * @return Issue[]
     */
    public function processIssues(string $repository, int $ms_no): array
    {
        $issues_raw = $this->github->issues($repository, $ms_no);
        $issues = [];

        foreach ($issues_raw as $issue_data) {
            $issues[] = $this->git_factory->createIssue($issue_data, $this->paused_labels);
        }

        return $issues;
    }

    public function getRawMilestones(): array
    {
        $milestones = [];

        foreach ($this->repositories as $repository) {
            $milestones += $repository->getRawMilestones();
        }

        return $milestones;
    }

}
