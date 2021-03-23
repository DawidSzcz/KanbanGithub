<?php

use PHPUnit\Framework\TestCase;
use vos\Amount;

class ProgressTraitTest extends TestCase
{
    const EMPTY_PERCENT_RESULT = [];

    /**
     * @covers       \ProgressTrait::getProgess()
     * @dataProvider percentDataProvider
     */
    public function testPercent(int $completed_count, int $remaining_count, array $expected_result): void
    {
        $progress_trait_mock = $this->getMockForTrait(
            \ProgressTrait::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getCompleted',
                'getRemaining'
            ]
        );
        $progress_trait_mock->expects($this->once())->method('getCompleted')->willReturn(new Amount($completed_count));
        $progress_trait_mock->expects($this->once())->method('getRemaining')->willReturn(new Amount($remaining_count));

        static::assertSame(
            $expected_result,
            $progress_trait_mock->getProgess()
        );
    }

    public static function percentDataProvider()
    {
        return [
            [1, 0, static::percentResult(1, 1, 0, 100.0)],
            [1, 1, static::percentResult(2, 1, 1, 50.0)],
            [0, 1, static::percentResult(1, 0, 1, 0.0)],
            [1, 2, static::percentResult(3, 1, 2, 33.0)],
            [5, 1, static::percentResult(6, 5, 1, 83.0)],
            [0, 0, static::EMPTY_PERCENT_RESULT]
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
