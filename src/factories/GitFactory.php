<?php


namespace factories;

use exceptions\ApiException;
use exceptions\FactoryException;
use interfaces\GitFactoryInterface;
use Michelf\Markdown;
use models\Issue;
use models\Milestone;
use models\Repository;
use vos\Amount;

class GitFactory implements GitFactoryInterface
{
    public function createRepository(string $name): Repository
    {
        return new Repository($name);
    }

    public function createMilestone(string $title, string $url, int $no, int $closed_issues_count, int $opened_issues_count): Milestone
    {
        if ($closed_issues_count + $opened_issues_count == 0) {
            throw new FactoryException('Empty milestone');
        }
        return new Milestone($title, $url, $no, new Amount($closed_issues_count), new Amount($opened_issues_count));
    }

    public function createIssue(array $data, array $paused_labels): Issue
    {
        if (isset($data['pull_request'])) {
            throw new FactoryException('Issue is pull request');
        }

        return new Issue(
            $data['id'],
            $data['number'],
            $data['title'],
            $data['state'],
            Markdown::defaultTransform($data['body']),
            $data['html_url'],
            !empty($data['assignee']) ? $data['assignee']['avatar_url'] . '?s=16' : null,
            static::labels_match($data['labels'], $paused_labels),
            new Amount(substr_count(strtolower($data['body']), '[x]')),
            new Amount(substr_count(strtolower($data['body']), '[ ]')),
            $data['closed_at']
        );
    }

    public static function labels_match(array $labels, array $needles): array
    {
        $labels = array_intersect(
            array_map(
                function ($label) {
                    if (!isset($label['name'])) {
                        throw new ApiException('Insufficient data');
                    }
                    return $label['name'];
                },
                $labels
            ),
            $needles
        );

        return !empty($labels) ? [reset($labels)] : [];
    }
}