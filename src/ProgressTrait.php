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
        $completed = $this->getCompleted()->getContent();
        $remaining = $this->getRemaining()->getContent();
        $total = $completed + $remaining;

        if ($total > 0) {
            /** TODO create dedicated model for progress */
            return [
                'total'     => $total,
                'complete'  => $completed,
                'remaining' => $remaining,
                'percent'   => round($completed / $total * 100)
            ];
        }
        return [];
    }

    /**
     * @return Amount
     */
    public function getRemaining(): Amount
    {
        return $this->remaining;
    }

    /**
     * @return Amount
     */
    public function getCompleted(): Amount
    {
        return $this->completed;
    }
}