<?php


use vos\Amount;

trait ProgressTrait
{
    /**
     * @var Amount
     */
    private $completed;
    /**
     * @var Amount
     */
    private $remaining;

    public function getProgess(): array
    {
        $completed = $this->completed->getContent();
        $remaining = $this->remaining->getContent();
        $total = $completed + $remaining;

        if ($total > 0) {
            /** TODO create dedicated model for progress */
            return [
                'total'     => $total,
                'completed' => $completed,
                'remaining' => $remaining,
                'percent'   => round($completed / $total * 100)
            ];
        }
        return [];
    }
}