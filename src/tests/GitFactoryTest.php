<?php

namespace factories;

use PHPUnit\Framework\TestCase;

class GitFactoryTest extends TestCase
{
    /**
     * @param string[] $labels_to_match
     * @param string[] $expected_match
     *
     * @covers       \factories\GitFactory::labels_match
     * @dataProvider labelMatchDataProvider
     */
    public function testLabelsMatch(array $issue, array $labels_to_match, array $expected_match): void
    {
        $match = GitFactory::labels_match($issue, $labels_to_match);

        static::assertEquals(
            $match,
            $expected_match,
            sprintf('%s not equal: %s', var_export($match, true), var_export([$expected_match], true))
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

    /**
     * @param string[] $label_names
     * @return array
     */
    private static function issueWithLabel(array $label_names)
    {
        return array_map(
            function ($label_name) {
                return ['name' => $label_name];
            },
            $label_names
        );
    }
}
