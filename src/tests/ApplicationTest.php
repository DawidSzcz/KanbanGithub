<?php


namespace tests;


use KanbanBoard\Application;
use PHPUnit\Framework\TestCase;


class ApplicationTest extends TestCase
{
    /**
     * @param string[] $labels_to_match
     * @param string[] $expected_match
     *
     * @covers       \KanbanBoard\Application::labels_match
     * @dataProvider labelMatchDataProvider
     */
    public function testLabelsMatch(array $issue, array $labels_to_match, array $expected_match): void
    {
        $match = Application::labels_match($issue, $labels_to_match);

        static::assertEquals(
            $match,
            $expected_match,
            sprintf('%s not equal: %s', var_export($match, true), var_export([$expected_match], true))
        );
    }

    /**
     * @covers       \KanbanBoard\Application::board
     * @dataProvider boardDataProvider
     */
    public function testBoard(
        array $milestones,
        array $issues,
        array $repositories,
        array $excluded_labels,
        array $percents,
        array $expected
    ): void {
        $githubMock = $this->getMockBuilder(\KanbanBoard\GithubClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['milestones'])
            ->getMock();
        $githubMock->expects($this->once())->method('milestones')->willReturnMap($milestones);

        $appMock = $this->getMockBuilder(\KanbanBoard\Application::class)
            ->setConstructorArgs([$githubMock, $repositories, $excluded_labels])
            ->onlyMethods(['_percent', 'issues'])
            ->getMock();

        $ms_count = array_reduce(
            $milestones,
            function ($curry, $current) {
                return $curry + count($current[1]);
            }
        );

        $appMock->expects($this->exactly($ms_count))->method('issues')->willReturnMap($issues);
        $appMock->expects($this->exactly($ms_count))
            ->method('_percent')
            ->willReturnOnConsecutiveCalls(...$percents);

        self::assertSame($expected, $appMock->board());
    }

    /**
     * @covers       \KanbanBoard\Application::_percent
     * @dataProvider percentDataProvider
     */
    public function testPercent(int $completed_count, int $remaining_count, array $expected_result): void
    {
        $app = new Application(null, [], []);

        static::assertSame(
            $expected_result,
            $app->_percent($completed_count, $remaining_count),
        );
    }


    public static function labelMatchDataProvider()
    {
        return [
            [static::issueWithLabel(['xyz']), ['xyz'], ['xyz']],
            [static::issueWithLabel([]), ['xyz'], []],
            [[], ['xyz'], []],
            [static::issueWithLabel(['abc']), ['xyz'], []],
            [static::issueWithLabel(['xyz', 'abc']), ['xyz'], ['xyz']],
            [static::issueWithLabel(['xyz', 'abc']), ['abc'], ['abc']],
            [static::issueWithLabel(['xyz', 'abc']), ['xyz', 'abc'], ['xyz']],
            [static::issueWithLabel(['abc']), ['xyz', 'abc'], ['abc']],
            [static::issueWithLabel(['qwe']), ['xyz', 'abc'], []],
        ];
    }

    public static function percentDataProvider()
    {
        return [
            [1, 0, static::percentResult(1, 1, 0, 100.0)],
            [1, 1, static::percentResult(2, 1, 1, 50.0)],
            [0, 1, static::percentResult(1, 0, 1, 0.0)],
            [1, 2, static::percentResult(3, 1, 2, 33.0)],
            [5, 1, static::percentResult(6, 5, 1, 83.0)],
            [0, 0, []]
        ];
    }

    public static function boardDataProvider()
    {
        $queued_1 = ['number' => 1, 'labels' => [], 'state' => 'active', 'assagnee' => null, 'closed_at' => null];
        $queued_2 = ['number' => 2, 'labels' => [], 'state' => 'active', 'assagnee' => null, 'closed_at' => null];
        $completed_1 = ['number' => 3, 'labels' => [], 'state' => 'closed', 'assagnee' => null, 'closed_at' => null];
        return [
            'test_single_queued_issue'     => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'    => 1,
                            'closed'  => 0,
                            'issues'  => [$queued_1],
                            'percent' => ['not_empty']
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_two_queued_issues'       => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'    => 2,
                            'closed'  => 0,
                            'issues'  => [$queued_1, $queued_2],
                            'percent' => ['not_empty']
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_completed_queued_issues' => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'    => 1,
                            'closed'  => 1,
                            'issues'  => [$queued_1, $completed_1],
                            'percent' => ['not_empty']
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_no_issue'                => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'    => 0,
                            'closed'  => 0,
                            'issues'  => [],
                            'percent' => []
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
            'test_more_ms'                 => static::boardDataProviderSingle(
                [
                    'repo1' => [
                        2 => [
                            'open'    => 1,
                            'closed'  => 1,
                            'issues'  => [$queued_1, $queued_2],
                            'percent' => ['not_empty']
                        ],
                        3 => [
                            'open'    => 2,
                            'closed'  => 0,
                            'issues'  => [$queued_1, $completed_1],
                            'percent' => ['not_empty']
                        ]
                    ]
                ],
                ['waiting-for-feedback']
            ),
        ];
    }


    private static function boardDataProviderSingle($config, array $exluded_labels)
    {
        $milestones_github = [];
        $issues_github = [];
        $results = [];
        $repos = [];
        $percents = [];

        foreach ($config as $repo_name => $repo_config) {
            $repos[] = $repo_name;
            $ms_in_repo = [];

            foreach ($repo_config as $ms_number => $ms_config) {
                $ms_in_repo[] = static::milestone($ms_number, $ms_config['open'], $ms_config['closed']);
                $issues = array_map(
                    function ($issue_raw) use ($ms_number) {
                        return static::issue($ms_number, $issue_raw);
                    },
                    $ms_config['issues']
                );

                $issues_grouped = [
                    'queued'    => [],
                    'active'    => [],
                    'completed' => $issues,
                ];

                $issues_github[] = [$repo_name, $ms_number, $issues_grouped];
                $percents[] = $ms_config['percent'];

                if (!empty($ms_config['percent'])) {
                    $result['milestone'] = sprintf('ms-%d', $ms_number);
                    $result['url'] = sprintf('ms-%d-url', $ms_number);
                    $result['progress'] = $ms_config['percent'];

                    $results[] = $result + $issues_grouped;
                }
            }

            $milestones_github[] = [$repo_name, $ms_in_repo];
        }

        return [$milestones_github, $issues_github, $repos, $exluded_labels, $percents, $results];
    }

    private static function milestone(int $milestone_number, $open, $closed)
    {
        return [
            'html_url'      => sprintf('ms-%d-url', $milestone_number),
            'number'        => $milestone_number,
            'title'         => sprintf('ms-%d', $milestone_number),
            'open_issues'   => $open,
            'closed_issues' => $closed,
        ];
    }

    private static function issue($ms_number, array $data)
    {
        return [
            'html_url'  => 'issue-url',
            'id'        => sprintf('ms-%s-%d', $ms_number, $data['number']),
            'number'    => $data['number'],
            'title'     => $data['title'],
            'labels'    => $data['labels'],
            'state'     => $data['state'],
            'assignee'  => $data['assignee'],
            'closed_at' => $data['closed_at'],
            'body'      => '',
        ];
    }

    private static function boardResult()
    {
    }

    /**
     * @param string[] $label_names
     * @return array
     */
    private static function issueWithLabel(array $label_names)
    {
        return [
            'labels' => array_map(
                function ($label_name) {
                    return ['name' => $label_name];
                },
                $label_names
            )
        ];
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