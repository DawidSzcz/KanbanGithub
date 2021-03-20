<?php

namespace vos;

class Amount
{
    /**
     * @var int
     */
    private $content;

    public function __construct(int $content)
    {
        if($content < 0) {
            throw new \Exception(sprintf('Invalid amount: %d', $content));
        }
        $this->content =  $content;
    }

    public function getContent(): int
    {
        return $this->content;
    }
}