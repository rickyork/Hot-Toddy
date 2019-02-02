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

class hTicketDatabase extends hPlugin {

    public function hConstructor()
    {

    }

    public function getTickets()
    {
        return $this->hDatabase->query(
            $this->getTemplateSQL(
                array(

                )
            )
        );
    }

    public function save(array $columns)
    {
        if (!isset($columns['hTicketId']))
        {
            $columns['hTicketId'] = nil;
        }
        else
        {
            $columns['hTicketId'] = (int) $columns['hTicketId'];
        }

        if (!isset($columns['hUserId']) && empty($columns['hTicketId']))
        {
            $columns['hUserId'] = (int) $_SESSION['hUserId'];
            $columns['hTicketCreated'] = time();
        }

        if (!empty($columns['hTicketId']))
        {
            $columns['hTicketLastModified'] = time();
            $columns['hTicketLastModifiedBy'] = (int) $_SESSION['hUserId'];
        }

        return $this->hTickets->save($columns);
    }

    public function saveResponse(array $columns)
    {
        if (empty($columns['hTicketId']))
        {
            return false;
        }

        if (!isset($columns['hTicketResponseId']))
        {
            $columns['hTicketResponseId'] = nil;
        }
        else
        {
            $columns['hTicketResponseId'] = (int) $columns['hTicketResponseId'];
        }

        if (!isset($columns['hUserId']) && empty($columns['hTicketResponseId']))
        {
            $columns['hUserId'] = (int) $_SESSION['hUserId'];
            $columns['hTicketResponseCreated'] = time();
        }

        if (!empty($columns['hTicketResponseId']))
        {
            $columns['hTicketResponseLastModified'] = time();
            $columns['hTicketResponseLastModifiedBy'] = (int) $_SESSION['hUserId'];
        }

        return $this->hTicketResponses->save($columns);
    }

    public function saveStatus()
    {
        if (!isset($columns['hTicketStatusId']))
        {
            $columns['hTicketStatusId'] = nil;
        }
        else
        {
            $columns['hTicketStatusId'] = (int) $columns['hTicketStatusId'];
        }

        if (!isset($columns['hUserId']) && empty($columns['hTicketStatusId']))
        {
            $columns['hUserId'] = (int) $_SESSION['hUserId'];
            $columns['hTicketStatusCreated'] = time();
        }

        if (!empty($columns['hTicketStatusId']))
        {
            $columns['hTicketStatusLastModified'] = time();
            $columns['hTicketStatusLastModifiedBy'] = (int) $_SESSION['hUserId'];
        }

        return $this->hTicketStatuses->save($columns);
    }

    public function assignStatuses($ticketId, $statuses)
    {
        $this->hTicketsToStatuses->delete('hTicketId', $ticketId);

        if (!is_array($statuses))
        {
            $statuses = array($statuses);
        }

        foreach ($statuses as $statusId)
        {
            $this->hTicketsToStatuses->insert(
                array(
                    'hTicketId' => (int) $ticketId,
                    'hTicketStatusId' => (int) $statusId
                )
            );
        }
    }

    public function assignStatus($ticketId, $statusId)
    {
        $exists = $this->hTicketsToStatuses->selectExists(
            'hTicketId',
            array(
                'hTicketId' => (int) $ticketId,
                'hTicketStatusId' => (int) $statusId
            )
        );

        if (!$exists)
        {
            $this->hTicketsToStatuses->insert(
                array(
                    'hTicketId' => (int) $ticketId,
                    'hTicketStatusId' => (int) $statusId
                )
            );
        }
    }
}

?>