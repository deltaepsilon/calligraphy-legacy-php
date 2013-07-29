<?php

namespace CDE\SubscriptionBundle\Model;

interface SubscriptionInterface
{
    /**
     * Adds days to an existing subscription
     */
    public function addDays($days);
}
