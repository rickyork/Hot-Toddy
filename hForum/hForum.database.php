<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Database
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# <h1>Forum Database API</h1>
# <p>
#    This object provides database in/out for interacting with forums, forum topics, and
#    forum posts.
# </p>
# @end

class hForumDatabase extends hPlugin {

    private $hSubscription;

    public function hConstructor()
    {
        $this->hSubscription = $this->library('hSubscription');
    }

    public function &markLastResponse($forumPostId, $userId = 0)
    {
        # @return hFormDatabase

        # @description
        # <h2>Marking the Last Response</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        $this->hForumPosts->update(
            array(
                'hForumPostLastResponse'   => time(),
                'hForumPostLastResponseBy' => (int) $userId,
                'hForumPostResponseCount'  => 'hForumPostResponseCount + 1'
            ),
            array(
                'hForumPostId' => (int) $forumPostId,
                'hForumPostParentId' => 0
            )
        );

        $this->markLastResponseToTopic($forumPostId, $userId);

        return $this;
    }

    public function &markLastResponseToTopic($forumPostId, $userId = 0)
    {
        # @return hFormDatabase

        # @description
        # <h2>Marking the Last Response to a Topic</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        $forumTopicId = $this->hForumPosts->selectColumn('hForumTopicId', $forumPostId);

        $this->hForumTopics->update(
            array(
                'hForumTopicLastResponse'   => time(),
                'hForumTopicLastResponseBy' => (int) $userId,
                'hForumTopicResponseCount'  => 'hForumTopicResponseCount + 1'
            ),
            array(
                'hForumTopicId' => (int) $forumTopicId
            )
        );

        return $this;
    }

    public function getModerators($forumTopicId)
    {
        # @return array

        # @description
        # <h2>Getting Forum Moderators</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hDatabase->select(
            array(
                'DISTINCT',
                'hUsers' => 'hUserId'
            ),
            array(
                'hForumTopics',
                'hUsers',
                'hUserPermissions',
                'hUserPermissionsGroups'
            ),
            array(
                'hForumTopics.hForumTopicId' => array(
                    array('=', (int) $forumTopicId),
                    array('=', 'hUserPermissions.hFrameworkResourceKey')
                ),
                'hUserPermissions.hFrameworkResourceId'        => 4,
                'hUserPermissions.hUserPermissionsId'          => 'hUserPermissionsGroups.hUserPermissionsId',
                'hUserPermissionsGroups.hUserGroupId'          => 'hUsers.hUserId',
                'hUserPermissionsGroups.hUserPermissionsGroup' => array('LIKE', '%w%')
            )
        );
    }

    public function postIsLocked($forumPostId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Post is Locked</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->postIs($forumPostId, 'hForumPostIsLocked');
    }

    public function &togglePostLock($forumPostId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling a Post Lock</h2>
        # <p>
        #
        # </p>
        # @end

        $this->togglePostAttribute($forumPostId, 'hForumPostIsLocked');
        return $this;
    }

    public function postIsApproved($forumPostId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Post is Approved</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->postIs($forumPostId, 'hForumPostIsApproved');
    }

    public function &togglePostApproval($forumPostId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling Post Approval</h2>
        # <p>
        #
        # </p>
        # @end

        $this->togglePostAttribute($forumPostId, 'hForumPostIsApproved');
        return $this;
    }

    public function postIsSticky($forumPostId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Post is Sticky</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->postIs($forumPostId, 'hForumPostIsSticky');
    }

    public function &togglePostStickiness($forumPostId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling a Post's Stickiness</h2>
        # <p>
        #
        # </p>
        # @end

        $this->togglePostAttribute($forumPostId, 'hForumPostIsSticky');
        return $this;
    }

    public function togglePostAttribute($forumPostId, $attribute)
    {
        # @return boolean

        # @description
        # <h2>Toggling a Post Attribute</h2>
        # <p>
        #
        # </p>
        # @end

        $update[$attribute] = !((bool) $this->hForumPosts->selectColumn($attribute, $forumPostId));
        $this->hForumPosts->update($update, $forumPostId);
        return $this->postIs($forumPostId, $attribute);
    }

    public function postIs($forumPostId, $field)
    {
        # @return boolean

        # @description
        # <h2>Determining a Post's Boolean Attribute State</h2>
        # <p>
        #
        # </p>
        # @end

        $where['hForumPostId'] = (int) $forumPostId;
        $where[$field] = 1;
        return $this->hForumPosts->selectExists('hForumPostId', $where);
    }

    public function topicIsModerated($forumTopicId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Topic is Moderated</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->topicIs($forumTopicId, 'hForumTopicIsModerated');
    }

    public function &toggleTopicModeration($forumTopicId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling Topic Moderation</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->toggleTopicAttribute($forumTopicId, 'hForumTopicIsModerated');
    }

    public function topicIsLocked($forumTopicId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Topic is Locked</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->topicIs($forumTopicId, 'hForumTopicIsLocked');
    }

    public function &toggleTopicLock($forumTopicId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling a Topic Lock</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->toggleTopicAttribute($forumTopicId, 'hForumTopicIsLocked');
    }

    public function &toggleTopicAttribute($forumTopicId, $attribute)
    {
        # @return hFormDatabase

        # @description
        # <h2>Toggling a Topic Attribute</h2>
        # <p>
        #
        # </p>
        # @end

        $update[$attribute] = !((bool) $this->hForumTopics->selectColumn($attribute, $forumTopicId));
        $this->hForumTopics->update($update, $forumTopicId);

        return $this;
    }

    public function topicIs($forumTopicId, $field)
    {
        # @return boolean

        # @description
        # <h2>Determining a Topic Attribute State</h2>
        # <p>
        #
        # </p>
        # @end

        $where['hForumTopicId'] = (int) $forumTopicId;
        $where[$field] = 1;

        return $this->hForumTopics->selectExists(
            'hForumTopicId',
            $where
        );
    }

    public function getPostSubject($forumPostId)
    {
        # @return string

        # @description
        # <h2>Returning a Form Post's Subject</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectColumn(
            'hForumPostSubject',
            (int) $forumPostId
        );
    }

    public function getForumName($forumId = 0)
    {
        # @return string

        # @description
        # <h2>Returning a Forum's Name</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForums->selectColumn(
            'hForum', empty($forumId)? (int) $this->hForumId : (int) $forumId
        );
    }

    public function getUserPostCount($userId)
    {
        # @return integer

        # @description
        # <h2>Retrieving a User's Post Count</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectCount(
            'hForumPostId',
            array(
                'hUserId' => (int) $userId
            )
        );
    }

    public function getLastThread($forumTopicId)
    {
        # @return array

        # @description
        # <h2>Retrieving the Last Thread of a Topic</h2>
        # <p>
        #
        # </p>
        # @end

       return $this->hForumPosts->selectAssociative(
           array(
               'hForumPostId',
               'hUserId',
               'hForumPostDate'
           ),
           array(
               'hForumTopicId' => (int) $forumTopicId,
               'hForumPostIsApproved' => 1
           ),
           'AND',
           array(
               'hForumPostId',
               'DESC'
           ),
           1
       );
    }

    public function getForums($fileId)
    {
        # @return array

        # @description
        # <h2>Retrieving Forums</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForums->selectColumnsAsKeyValue(
            array(
                'hForumId',
                'hForum'
            ),
            array(
                'hFileId' => (int) $fileId
            ),
            'AND',
            'hForumSortIndex'
        );
    }

    public function getTopic($forumTopicId = 0)
    {
        # @return string

        # @description
        # <h2>Retrieving a Forum Topic</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumTopics->selectColumn(
            'hForumTopic', empty($forumTopicId)? (int) $this->hForumTopicId : (int) $forumTopicId
        );
    }

    public function getTopics($forumId)
    {
        # @return array

        # @description
        # <h2>Retrieving Forum Topics</h2>
        # <p>
        #
        # </p>
        # @end

        return(
            $this->hDatabase->getResults(
                $this->getTemplateSQL(
                    array_merge(
                        array(
                            'forumId' => (int) $forumId
                        ),
                        $this->getPermissionsVariablesForTemplate(true, false, 'r')
                    )
                )
            )
        );
    }

    public function getThreadCount($forumTopicId, $topLevel = false)
    {
        # @return integer

        # @description
        # <h2>Retrieving a Forum Thread Count</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectCount(
            'hForumPostId',
            array_merge(
                array(
                    'hForumTopicId' => (int) $forumTopicId,
                    'hForumPostIsApproved' => 1
                ),
                $topLevel? array('hForumPostParentId' => 0) : array()
            )
        );
    }

    public function &deleteForum($forumId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Deleting a Forum</h2>
        # <p>
        #
        # </p>
        # @end

        $forumTopics = $this->hForumTopics->select(
            'hForumTopicId',
            array(
                'hForumId' => (int) $forumId
            )
        );

        foreach ($forumTopics as $forumTopicId)
        {
            $this->deleteTopic((int) $forumTopicId);
        }

        $this->hForums->deleteSubscription((int) $forumId);
        $this->hForums->deletePermissions((int) $forumId);
        $this->hForums->delete('hForumId', (int) $forumId);

        return $this;
    }

    public function &deleteTopic($forumTopicId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Deleting a Forum Topic</h2>
        # <p>
        #
        # </p>
        # @end

        $forumPosts = $this->hForumPosts->select(
            'hForumPostId',
            array(
                'hForumTopicId' => (int) $forumTopicId
            )
        );

        foreach ($forumPosts as $forumPostId)
        {
            $this->deletePost((int) $forumPostId);
        }

        $this->hSubscription->delete('hForumTopics', (int) $forumTopicId);
        $this->hForumTopics->deletePermissions((int) $forumTopicId);
        $this->hForumTopics->delete('hForumTopicId', (int) $forumTopicId);

        return $this;
    }

    public function deletePost($forumPostId)
    {
        # @return hFormDatabase

        # @description
        # <h2>Deleting a Forum Post</h2>
        # <p>
        #
        # </p>
        # @end

        $forumPostRootId = $this->hForumPosts->selectColumn(
            'hForumPostRootId',
            $forumPostId
        );

        if (!empty($forumPostRootId))
        {
            $this->hForumPosts->update(
                array(
                    'hForumPostResponseCount' => 'hForumPostResponseCount - 1'
                ),
                $forumPostRootId
            );
        }

        $forumTopicId = $this->hForumPosts->selectColumn('hForumTopicId', $forumPostId);

        if (!empty($forumTopicId))
        {
            $this->hForumTopics->update(
                array(
                    'hForumTopicResponseCount' => 'hForumTopicResponseCount - 1'
                ),
                $forumTopicId
            );
        }

        $this->hSubscription->delete('hForumPosts', (int) $forumPostId);
        $this->hForumPosts->deletePermissions((int) $forumPostId);

        $this->hForumPosts->delete(
            array(
                'hForumPostId' => (int) $forumPostId,
                'hForumPostRootId' => (int) $forumPostId
            ),
            nil,
            'OR'
        );
    }

    public function save()
    {
        # @return integer

        # @description
        # <h2>Saving a Forum</h2>
        # <p>
        #
        # </p>
        # @end

        $columns = func_get_args();
        return $this->hForums->save($columns);
    }

    public function threadHasChildren($forumPostId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Thread Has Children</h2>
        # <p>
        #
        # </p>
        # @end

        return $thhis->hForumPosts->selectExists(
            'hForumPostId',
            array(
                'hForumPostParentId' => (int) $forumPostId
            )
        );
    }

    public function getThreads($forumTopicId, $forumPostIsApproved = 0, $forumPostIsSticky = 0, $forumPostParentId = 0, $forumPostId = 0)
    {
        # @return array

        # @description
        # <h2>Retrieving Forum Threads</h2>
        # <p>
        #
        # </p>
        # @end

        $sort = ($forumPostId)? 'ASC' : 'DESC';

        #echo $this->getTemplateSQL(
        #            array(
        #                'hForumPostId'         => (int) $forumPostId,
        #                'hForumTopicId'        => (int) $forumTopicId,
        #                'hForumPostIsApproved' => (int) $forumPostIsApproved,
        #                'hForumPostIsSticky'   => (int) $forumPostIsSticky,
        #                'hForumPostParentId'   => (int) $forumPostParentId,
        #                'hForumPostLimit'      => $this->hForumPostLimit(null),
        #                'sort'                 => $this->hForumPostSort($sort),
        #                'checkPermissions'     => !$this->inGroup('root'),
        #                'hUserId'              => $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 0,
        #                'hUserGroups'          => array(
        #                    'hUserGroupId' => $this->getGroupMembership()
        #                )
        #            )
        #        );

        return(
            $this->hDatabase->getResults(
                $this->getTemplateSQL(
                    array_merge(
                        array(
                            'hForumPostId'         => (int) $forumPostId,
                            'hForumTopicId'        => (int) $forumTopicId,
                            'hForumPostIsApproved' => (int) $forumPostIsApproved,
                            'hForumPostIsSticky'   => (int) $forumPostIsSticky,
                            'hForumPostParentId'   => (int) $forumPostParentId,
                            'hForumPostLimit'      => $this->hForumPostLimit(nil),
                            'sort'                 => $this->hForumPostSort($sort)
                        ),
                        $this->getPermissionsVariablesForTemplate(true, false, 'r')
                    )
                )
            )
        );
    }

    public function getRecentThreads($fileId, $time, $approved = 0, $limit = nil, $sort = 'DESC')
    {
        # @return array

        # @description
        # <h2>Retrieving Recent Threads</h2>
        # <p>
        #
        # </p>
        # @end

        if (!is_numeric($time))
        {
            $time = strtotime($time);
        }

        $sql = $this->getTemplateSQL(
            array_merge(
                array(
                    'hFileId'  => (int) $fileId,
                    'time'     => $time,
                    'approved' => $approved,
                    'limit'    => $limit,
                    'sort'     => $sort,
                ),
                $this->getPermissionsVariablesForTemplate(true, false, 'r')
            )
        );

        return $this->hDatabase->getResults($sql);
    }

    public function getThreadTopic($forumPostId)
    {
        # @return string

        # @description
        # <h2>Retrieving a Forum Thread Topic</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectColumn('hForumPostSubject', $forumPostId);
    }

    public function getReplyCount($forumPostId)
    {
        # @return integer

        # @description
        # <h2>Retrieving a Reply Count for a Post</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectCount(
            'hForumPostId',
            array(
                'hForumPostRootId' => (int) $forumPostId
            )
        );
    }

    public function getLastReply($forumPostId)
    {
        # @return array

        # @description
        # <h2>Getting the Latest Reply</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectAssociative(
            array(
                'hUserId',
                'hForumPostDate'
            ),
            array(
                'hForumPostRootId' => (int) $forumPostId
            ),
            'AND',
            array(
                'hForumPostDate',
                'DESC'
            ),
            1
        );
    }

    public function savePost($columns)
    {
        # @return integer

        # @description
        # <h2>Saving a Forum Post</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->save($columns);
    }

    public function getPostBody($forumPostId)
    {
        # @return HTML

        # @description
        # <h2>Retrieving a Post's Body</h2>
        # <p>
        #
        # </p>
        # @end

        return hString::decodeHTML(
            $this->hForumPosts->selectColumn(
                'hForumPost',
                (int) $forumPostId
            )
        );
    }

    public function getPostAuthor($forumPostId)
    {
        # @return integer

        # @description
        # <h2>Retrieving a Post's User Id</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->hForumPosts->selectColumn('hUserId', (int) $forumPostId);
    }

    public function getPostAuthorUserName($forumPostId)
    {
        # @return string

        # @description
        # <h2>Retrieving a Post's User Name</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->user->getUserName(
            $this->hForumPosts->selectColumn(
                'hUserId',
                (int) $forumPostId
            )
        );
    }

    public function &renameForum($forumId, $forumName)
    {
        # @return hFormDatabase

        # @description
        # <h2>Renaming a Forum</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hForums->update(
            array(
                'hForum' => $forumName
            ),
            (int) $forumId
        );

        return $this;
    }

    public function newForum($fileId, $forumName, $forumSortIndex = 0, $userId = 0)
    {
        # @return integer

        # @description
        # <h2>Creating a New Forum</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);
        $this->hSubscription = $this->library('hSubscription');

        if (empty($forumSortIndex))
        {
            $forumSortIndex = $this->hForums->selectCount(
                'hForumId',
                array(
                    'hFileId' => (int) $fileId
                )
            );
        }

        $forumId = $this->hForums->insert(
            array(
                'hForumId'        => 0,
                'hFileId'         => (int) $fileId,
                'hForum'          => $forumName,
                'hForumSortIndex' => (int) $forumSortIndex,
                'hUserId'         => (int) $userId
            )
        );

        $this->hSubscription->save('hForums', $forumId);

        return $forumId;
    }

    public function saveTopic(array $topic = array())
    {
        # @return integer

        # @description
        # <h2>Saving a Forum Topic</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($topic['hUserId']))
        {
            $topic['hUserId'] = 0;
        }

        $this->user->whichUserId($topic['hUserId']);
        $this->hSubscription = $this->library('hSubscription');

        $columns = array(
            'hForumTopicId' => $topic['hForumTopicId'],
            'hForumId'      => (int) $topic['hForumId'],
            'hForumTopic'   => $topic['hForumTopic'],
            'hUserId'       => (int) $topic['hUserId']
        );

        if (isset($topic['hForumTopicDescription']))
        {
            $columns['hForumTopicDescription'] = $topic['hForumTopicDescription'];
        }

        if (isset($topic['hForumTopicIsLocked']))
        {
            $columns['hForumTopicIsLocked'] = (int) $topic['hForumTopicIsLocked'];
        }

        if (isset($topic['hForumTopicIsModerated']))
        {
            $columns['hForumTopicIsModerated'] = (int) $topic['hForumTopicIsModerated'];
        }

        if (empty($topic['hForumTopicSortIndex']) && empty($topic['hForumTopicId']))
        {
            $columns['hForumTopicSortIndex'] = $this->hForumTopics->selectCount(
                'hForumTopicId',
                array(
                    'hForumId' => (int) $topic['hForumId']
                )
            );
        }
        else if (!empty($topic['hForumTopicSortIndex']))
        {
            $columns['hForumTopicSortIndex'] = (int) $topic['hForumTopicSortIndex'];
        }

        $forumTopicId = $this->hForumTopics->save($columns);

        if (empty($topic['hForumTopicId']))
        {
            $this->hSubscription->save(
                'hForumTopics',
                $forumTopicId
            );

            $this->setTopicGroups($this->hForumTopicDefaultGroups(nil));

            if (isset($topic['groups']))
            {
                $this->setTopicGroups($topic['groups']);
            }

            $this->setTopicGroups($this->hForumTopicDefaultModerators(nil), 'rw');

            if (isset($topic['moderators']))
            {
                $this->setTopicGroups($topic['moderators'], 'rw');
            }

            $this->hForumTopics->savePermissions(
                $forumTopicId,
                'rw',
                $this->hForumTopicDefaultWorldPermissions('')
            );
        }

        return $forumTopicId;
    }

    private function &setTopicGroups($groups, $level = 'r')
    {
        # @return hFormDatabase

        # @description
        # <h2>Setting Topic Groups</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($groups))
        {
            if (is_array($groups))
            {
                foreach ($groups as $group)
                {
                    $this->hForumTopics->setGroup($group, $level);
                }
            }
            else
            {
                $this->hForumTopics->setGroup($groups, $level);
            }
        }

        return $this;
    }

    public function &sortForums($forums)
    {
        # @return hFormDatabase

        # @description
        # <h2>Sorting Forums</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($forums as $forumId => $formSortIndex)
        {
            $this->hForums->update(
                array(
                    'hForumSortIndex' => (int) $formSortIndex
                ),
                (int) $forumId
            );
        }

        return $this;
    }

    public function &sortTopics($forums)
    {
        # @return hFormDatabase

        # @description
        # <h2>Sorting Forum Topics</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($forums as $forumId => $forumTopics)
        {
            foreach ($forumTopics as $forumTopicId => $forumTopicSortIndex)
            {
                $this->hForumTopics->update(
                    array(
                        'hForumId' => (int) $forumId,
                        'hForumTopicSortIndex' => (int) $forumTopicSortIndex
                    ),
                    (int) $forumTopicId
                );
            }
        }

        return $this;
    }
}

?>