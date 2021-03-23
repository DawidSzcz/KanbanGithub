<?php


namespace tests;


use factories\GitFactory;
use KanbanBoard\Application;
use Michelf\Markdown;
use PHPUnit\Framework\TestCase;


class ApplicationTest extends TestCase
{
    const EMPTY_PERCENT_RESULT = [];

    /**
     * @covers       \KanbanBoard\Application::board
     * @dataProvider boardDataProvider
     */
    public function testBoard(
        array $milestones,
        array $issues,
        array $repositories,
        array $excluded_labels,
        array $expected
    ): void {
        $github_issues_call_count = array_reduce(
            $milestones,
            function ($curry, $current) {
                return $curry + count(
                        array_filter(
                            $current[1],
                            function ($milestone_raw) {
                                return $milestone_raw['open_issues'] +
                                    $milestone_raw['closed_issues'] > 0;
                            }
                        )
                    );
            },
            0
        );
        $githubMock = $this->getMockBuilder(\KanbanBoard\GithubClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['milestones', 'issues'])
            ->getMock();
        $githubMock->expects($this->exactly(count($repositories)))->method('milestones')->willReturnMap($milestones);
        $githubMock->expects($this->exactly($github_issues_call_count))->method('issues')->willReturnMap($issues);

        $app = new Application($githubMock, $repositories, new GitFactory(), $excluded_labels);
        $app->run();

        self::assertSame($expected, $app->getRawMilestones());
    }

    public static function boardDataProvider()
    {
        $queued_1 = ['number' => 1, 'labels' => [], 'state' => 'active', 'avatar_url' => null, 'closed_at' => null, 'progress' => static::EMPTY_PERCENT_RESULT];
        $queued_2 = ['number' => 2, 'labels' => [], 'state' => 'active', 'avatar_url' => null, 'closed_at' => null, 'progress' => static::EMPTY_PERCENT_RESULT];
        $completed_1 = ['number' => 3, 'labels' => [], 'state' => 'closed', 'avatar_url' => null, 'closed_at' => null, 'progress' => static::EMPTY_PERCENT_RESULT];
        $completed_2 = ['number' => 4, 'labels' => [], 'state' => 'closed', 'avatar_url' => null, 'closed_at' => null, 'progress' => static::EMPTY_PERCENT_RESULT];
        $active_1 = ['number' => 5, 'labels' => ['z'], 'state' => 'active', 'avatar_url' => 'avater1', 'closed_at' => '2021-03-18T02:36:44Z', 'progress' => static::EMPTY_PERCENT_RESULT];
        $active_2 = ['number' => 6, 'labels' => ['x', 'y', 'z'], 'state' => 'active', 'avatar_url' => 'avater2', 'closed_at' => '2021-03-16T02:36:44Z', 'progress' => static::EMPTY_PERCENT_RESULT];
        $paused_3 = ['number' => 7, 'labels' => ['x', 'y'], 'state' => 'active', 'avatar_url' => 'avater3', 'closed_at' => null, 'progress' => static::EMPTY_PERCENT_RESULT];
        return [
            'test_single_queued_issue'      => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 1,
                            'closed'   => 0,
                            'issues'   => [$queued_1],
                            'progress' => static::percentResult(1, 0, 1, 0.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_queued_issues'        => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$queued_1, $queued_2],
                            'progress' => static::percentResult(2, 0, 2, 0.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_completed_queued_issues'  => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 1,
                            'closed'   => 1,
                            'issues'   => [$queued_1, $completed_1],
                            'progress' => static::percentResult(2, 1, 1, 50.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_all_issues'               => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 2,
                            'closed'   => 1,
                            'issues'   => [$queued_1, $completed_1, $active_1],
                            'progress' => static::percentResult(3, 1, 2, 33.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_completed_issue'      => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 0,
                            'closed'   => 2,
                            'issues'   => [$completed_1, $completed_2],
                            'progress' => static::percentResult(2, 2, 0, 100.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_all_twice_issue'          => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 4,
                            'closed'   => 2,
                            'issues'   => [$queued_1, $completed_1, $active_1, $queued_2, $completed_2, $active_2],
                            'progress' => static::percentResult(6, 2, 4, 33.0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_active_issue'         => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$active_2, $active_1],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_active_reverse_issue' => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$active_1, $active_2],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_paused_issue'         => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$active_1, $active_2, $paused_3],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ]
                    ]
                ],
                ['x', 'y', 'z']
            ),
            'test_no_issue'                 => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'     => 0,
                            'closed'   => 0,
                            'issues'   => [],
                            'progress' => static::EMPTY_PERCENT_RESULT
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_more_ms'                  => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        3 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$queued_1, $queued_2],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ],
                        2 => [
                            'open'     => 0,
                            'closed'   => 2,
                            'issues'   => [$completed_2, $completed_1],
                            'progress' => static::percentResult(2, 2, 0, 100.0),
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_more_repos'               => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        3 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$queued_1, $queued_2],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ],
                        2 => [
                            'open'     => 0,
                            'closed'   => 2,
                            'issues'   => [$completed_2, $completed_1],
                            'progress' => static::percentResult(2, 2, 0, 100.0),
                        ]
                    ],
                    'repo2' => [
                        1 => [
                            'open'     => 2,
                            'closed'   => 0,
                            'issues'   => [$active_1, $active_2],
                            'progress' => static::percentResult(2, 0, 2, 0)
                        ],
                        5 => [
                            'open'     => 0,
                            'closed'   => 2,
                            'issues'   => [$completed_2, $completed_1],
                            'progress' => static::percentResult(2, 2, 0, 100.0),
                        ]
                    ],
                ],
                ['waiting-for-feedback']
            ),
        ];
    }

    private static function boardDataProviderSingle($config, array $paused_labels)
    {
        $milestones_github = [];
        $issues_github = [];
        $results = [];
        $repos = [];

        foreach ($config as $repo_name => $repo_config) {
            $repos[] = $repo_name;
            $ms_in_repo = [];

            foreach ($repo_config as $ms_number => $ms_config) {
                $ms_in_repo[] = static::milestoneGitHub($ms_number, $ms_config['open'], $ms_config['closed']);
                $issues_github[sprintf('ms-%d', $ms_number)] = [
                    $repo_name,
                    $ms_number,
                    array_map(
                        function ($issue_raw) use ($ms_number) {
                            return static::issueGithub($ms_number, $issue_raw);
                        },
                        $ms_config['issues']
                    )
                ];

                if (!empty($ms_config['progress'])) {
                    $results[sprintf('ms-%d', $ms_number)] = static::milestoneResult($ms_number, $ms_config['issues'], $ms_config['progress'], $paused_labels);
                }
            }

            $milestones_github[] = [$repo_name, $ms_in_repo];
        }

        ksort($results);
        ksort($issues_github);
        return [$milestones_github, $issues_github, $repos, $paused_labels, array_values($results)];
    }

    private static function milestoneResult(int $ms_number, array $issues, array $progress, array $paused_labels): array
    {
        $milestone_result = [
            'milestone' => sprintf('ms-%d', $ms_number),
            'url'       => sprintf('ms-%d-url', $ms_number),
            'progress'  => $progress,
            'queued'    => [],
            'active'    => [],
            'completed' => []
        ];

        foreach ($issues as $issue_raw) {
            if ($issue_raw['state'] === 'closed') {
                $milestone_result['completed'][] = static::issueResult($ms_number, $issue_raw, $paused_labels);
            } elseif ($issue_raw['avatar_url']) {
                $milestone_result['active'][] = static::issueResult($ms_number, $issue_raw, $paused_labels);
            } else {
                $milestone_result['queued'][] = static::issueResult($ms_number, $issue_raw, $paused_labels);
            }
        }

        usort(
            $milestone_result['active'],
            function ($a, $b) {
                return count($a['paused']) - count($b['paused']) === 0 ? strcmp($a['title'], $b['title']) : count(
                        $a['paused']
                    ) - count($b['paused']);
            }
        );

        return $milestone_result;
    }

    private static function milestoneGitHub(int $milestone_number, $open, $closed): array
    {
        return [
            'html_url'      => sprintf('ms-%d-url', $milestone_number),
            'number'        => $milestone_number,
            'title'         => sprintf('ms-%d', $milestone_number),
            'open_issues'   => $open,
            'closed_issues' => $closed,
        ];
    }

    private static function issueResult($ms_number, array $data, array $paused_labels)
    {
        $labels = array_intersect($data['labels'], $paused_labels);
        return [
            'id'       => sprintf('ms-%s-%d', $ms_number, $data['number']),
            'number'   => $data['number'],
            'title'    => sprintf('ms-%s-%d-title', $ms_number, $data['number']),
            'body'     => Markdown::defaultTransform(''),
            'url'      => 'issue-url',
            'assignee' => isset($data['avatar_url']) ? $data['avatar_url'] . '?s=16' : null,
            'paused'   => count($labels) ? [reset($labels)] : [],
            'progress' => $data['progress'],
            'closed'   => $data['closed_at']
        ];
    }

    private static function issueGithub($ms_number, array $data)
    {
        $issue = [
            'html_url'  => 'issue-url',
            'id'        => sprintf('ms-%s-%d', $ms_number, $data['number']),
            'number'    => $data['number'],
            'title'     => sprintf('ms-%s-%d-title', $ms_number, $data['number']),
            'labels'    => array_map(
                function ($label_name) {
                    return ['name' => $label_name];
                },
                $data['labels']
            ),
            'state'     => $data['state'],
            'closed_at' => $data['closed_at'],
            'body'      => '',
            'assignee'  => isset($data['avatar_url']) ? ['avatar_url' => $data['avatar_url']] : []
        ];

        return $issue;
    }

    private static function percentResult(int $total, int $complete, int $remaining, float $percent)
    {
        return [
            'total'     => $total,
            'complete'  => $complete,
            'remaining' => $remaining,
            'percent'   => $percent
        ];
    }
}