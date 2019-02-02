<?php
  class hUserExportLibrary extends hPlugin { public function hConstructor() { } public function getUsers(array $export = array()) {                $query = $this->hUsers->select(); $hUsers = array(); $i = 0; foreach ($query as $data) { $hContact = $this->hContacts->select( '*', array( 'hContactAddressBookId' => 1, 'hUserId' => $data['hUserId'] ) ); $groupProperties = $this->hUserGroupProperties->selectAssociative( '*', array( 'hUserId' => $data['hUserId'] ) ); if (!empty($groupProperties['hUserGroupOwner'])) { $groupProperties['hUserGroupOwner'] = $this->user->getUserName($groupProperties['hUserGroupOwner']); } $hUsers[$i] = array_merge( $data, $this->hUserLog->selectAssociative( '*', array( 'hUserId' => $data['hUserId'] ) ), $groupProperties, $this->hUserUnixProperties->selectAssociative( '*', array( 'hUserId' => $data['hUserId'] ) ), $this->contact->getRecord(0, $data['hUserId']) ); $hUsers[$i]['hUserVariables'] = $this->hUserVariables->select( array( 'hUserVariable', 'hUserValue' ), array( 'hUserId' => $data['hUserId'] ) ); $i++; } $hUserGroups = array(); $query = $this->hUserGroups->select(); foreach ($query as $data) { $hUserGroups[] = array( 'hUserGroupId' => $this->user->getUserName($data['hUserGroupId']), 'hUserId' => $this->user->getUserName($data['hUserId']) ); } /**

        $this->getUsers(
            array(
                'tmpTrainingLessons' => array(
                    'hUserId',
                    'tmpTrainingSignedOffBy'
                ),
                'tmpTrainingModules' => array(
                    'hUserGroupId',
                    'hUserId',
                    'tmpTrainer',
                    'tmpTrainingSignedOffBy'
                ),
                'tmpTrainingQuizAnswers' => array(
                    'hUserId'
                ),
                'tmpTrainingQuizGrades' => array(
                    'hUserGroupId',
                    'hUserId'
                ),
                'tmpTrainingTIA' => array(
                    'hUserId'
                )
            )
        )

        **/ $exportData = array();  foreach ($export as $table => $userColumns) { $query = $this->hDatabase->select('*', $table); foreach ($query as $i => $data) { foreach ($userColumns as $userColumn) { $query[$i][$userColumn] = $this->user->getUserName($query[$i][$userColumn]); } } $exportData[$table] = $query; }   return array( 'hUsers' => $hUsers, 'hUserGroups' => $hUserGroups, 'export' => $export, 'exportData' => $exportData ); } } ?>