<?php

namespace interfaces;

use models\Issue;
use models\Milestone;
use models\Repository;


interface GitFactoryInterface
{
    public function createRepository(string $name): Repository;

    public function createMilestone(string $title, string $url, int $no, int $closed_issues_count, int $opened_issues_count): Milestone;

    public function createIssue(array $data, array $paused_labels): Issue;
}