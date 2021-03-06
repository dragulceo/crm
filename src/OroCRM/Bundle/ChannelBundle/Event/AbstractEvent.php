<?php

namespace OroCRM\Bundle\ChannelBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class AbstractEvent extends Event
{
    /** @var Channel */
    protected $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
