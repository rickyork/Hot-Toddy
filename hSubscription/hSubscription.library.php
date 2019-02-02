<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>Subscription API</h1>
# <p>
#    This object provides information related to subscriptions.  Determining if a user
#    is subscribed.  Getting a user's subscriptions.  Subscribing or unsubscribing a
#    user to or from a framework resource, and so on.
# </p>
# @end

class hSubscriptionLibrary extends hPlugin {

    public function toggleSubscription($frameworkResource, $frameworkResourceKey, $userId = 0)
    {
        $this->user->whichUserId($userId);

        if ($this->isSubscribed($frameworkResource, $frameworkResourceKey, $userId))
        {
            $this->unsubscribe(
                $frameworkResource,
                $frameworkResourceKey,
                $userId
            );

            return false;
        }
        else
        {
            $this->subscribe(
                $frameworkResource,
                $frameworkResourceKey,
                $userId
            );

            return true;
        }
    }

    public function getUserSubscriptions($userId = 0)
    {
        $this->user->whichUserId($userId);

        $subscriptions = $this->hSubscriptionUsers->selectResults(
            'hSubscriptionId',
            array(
                'hUserId' => (int) $userId
            )
        );

        $subscriptions = array();

        foreach ($subscriptions as $subscriptionId)
        {
            $name = $this->getSubscriptionName($subscriptionId);

            if (!empty($name))
            {
                $subscriptions[$subscriptionId] = $this->getSubscriptionName($subscriptionId);
            }
        }

        return $subscriptions;
    }

    public function getMultipleSubscriptions(array $frameworkResources)
    {
        $hUsers = array();

        foreach ($frameworkResources as $frameworkResource => $frameworkResourceKey)
        {
            $hUsers = array_merge(
                $hUsers,
                $this->getSubscriptions(
                    $frameworkResource,
                    $frameworkResourceKey
                )
            );
        }

        return array_unique($hUsers);
    }

    public function getSubscriptions($frameworkResource, $frameworkResourceKey)
    {
        $frameworkResourceId = $this->getResourceId($frameworkResource);
        $subscriptionId = $this->getSubscriptionId($frameworkResourceId, $frameworkResourceKey);

        $userIds = $this->hSubscriptionUsers->select(
            'hUserId',
            array(
                'hSubscriptionId' => (int) $subscriptionId
            )
        );

        if (!is_array($userIds))
        {
            $userIds = array();
        }

        if ($this->hSubscriptionGroup)
        {
            array_push($userIds, $this->hSubscriptionGroup);
        }

        $subscribedUsers = $userIds;

        foreach ($userIds as $i => $userId)
        {
            if (empty($userId))
            {
                unset($subscribedUsers[$i]);
                continue;
            }

            if ($this->isGroup($userId))
            {
                $groupMembers = $this->getGroupMembers($userId);

                foreach ($groupMembers['hUsers'] as $groupMember)
                {
                    if (!empty($groupMember) && !in_array($groupMember, $subscribedUsers))
                    {
                        array_push($subscribedUsers, $groupMember);
                    }
                }
            }
        }

        return array_unique($subscribedUsers);
    }

    public function getSubscriptionName($subscriptionId)
    {
        $data = $this->hSubscriptions->selectAssociative(
            array(
                'hFrameworkResourceId',
                'hFrameworkResourceKey'
            ),
            (int) $subscriptionId
        );

        return $this->getResourceName(
            $data['hFrameworkResourceId'],
            $data['hFrameworkResourceKey']
        );
    }

    private function getSubscriptionId($frameworkResourceId, $frameworkResourceKey)
    {
        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        return $this->hSubscriptions->selectColumn(
            'hSubscriptionId',
            array(
                'hFrameworkResourceId'  => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey
            )
        );
    }

    public function delete($frameworkResource, $frameworkResourceKey)
    {
        $frameworkResourceId = $this->getResourceId($frameworkResource);

        $subscriptionId = $this->getSubscriptionId(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        $this->hSubscriptionUsers->delete(
            'hSubscriptionId',
            $subscriptionId
        );

        $this->hSubscriptions->delete(
            'hSubscriptionId',
            $subscriptionId
        );
    }

    public function save($frameworkResource, $frameworkResourceKey)
    {
        $frameworkResourceId = $this->getResourceId($frameworkResource);

        return (!$this->getSubscriptionId($frameworkResourceId, $frameworkResourceKey))?
            $this->hSubscriptions->insert('null', $frameworkResourceId, $frameworkResourceKey) : 0;
    }

    public function subscribe($frameworkResource, $frameworkResourceKey, $userId = 0)
    {
        $this->user->whichUserId($userId);
        $frameworkResourceId = $this->getResourceId($frameworkResource);

        $subscriptionId = $this->getSubscriptionId(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        $this->hSubscriptionUsers->insert(
            $userId,
            $subscriptionId
        );
    }

    public function unsubscribeById($subscriptionId, $userId = 0)
    {
        $this->user->whichUserId($userId);

        $this->hSubscriptionUsers->delete(
            array(
                'hUserId' => (int) $userId,
                'hSubscriptionId' => (int) $subscriptionId
            )
        );
    }

    public function unsubscribe($frameworkResource, $frameworkResourceKey, $userId = 0)
    {
        $this->user->whichUserId($userId);

        $frameworkResourceId = $this->getResourceId($frameworkResource);

        $subscriptionId = $this->getSubscriptionId(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        $this->unsubscribeById(
            $subscriptionId,
            $userId
        );
    }

    public function isSubscribed($frameworkResourceId, $frameworkResourceKey, $userId = 0)
    {
        $this->user->whichUserId($userId);

        if (!is_numeric($frameworkResourceId))
        {
            $frameworkResourceId = $this->getResourceId($frameworkResourceId);
        }

        $subscriptionId = $this->hSubscriptions->selectColumn(
            'hSubscriptionId',
            array(
                'hFrameworkResourceId'  => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey
            )
        );

        if (empty($subscriptionId))
        {
            return false;
        }
        else
        {
            $this->user->whichUserId($userId);

            return $this->hSubscriptionUsers->selectExists(
                'hUserId',
                array(
                    'hSubscriptionId' => (int) $subscriptionId,
                    'hUserId' => (int) $userId
                )
            );
        }
    }
}

?>