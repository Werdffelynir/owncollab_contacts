<?php


namespace OCA\Owncollab_Contacts;

use OC\Group\Manager;
use OC\Session\Memory;
use OC\Session\Session;
use OCA\Owncollab_Contacts\Db\Connect;


/**
 * addressbookhandler
 * A class to generate vCards for contact data.
 */
class AddressBookHandler
{

    /**
     * @var  Connect $connect
     */
    private $connect;


    /**
     * AddressBookHandler constructor.
     * @param Connect $connect
     */
    public function __construct($connect)
    {
        //$this->userId = $uid;
        $this->connect = $connect;
    }


    /**
     * Create data for ProjectContacts
     */
    public function createProjectContacts()
    {
        $addressBookName = 'Project Contacts';
        $addressBookNameOwner = 'collab_user';
        $withoutGroupName = 'Without Group';
        $pcUsers = $this->connect->users()->pcGetUsers();
        $pcGroupUser = $this->connect->users()->pcGetGroupUser();
        $pcGroups = $this->connect->users()->pcGetGroups();
        $pcGroups[] = ['gid' => $withoutGroupName];

        $this->connect->db->beginTransaction();

        $bookId = $this->connect->addressbook()->create($addressBookName, $addressBookNameOwner, 1, 0);
        if($bookId){

            $relationContacts = [];
            $relationGroups = [];
            $relationWithoutGroups = [];

            foreach($pcUsers as $user) {

                if (in_array($user['uid'], $relationContacts))
                    continue;

                $fields = [
                    'email1' => $user['email'],
                    'display_name' => $user['displayname'] ? $user['displayname'] : $user['uid']
                ];

                $id_contact = $this->connect->addresscontacts()->create($user['uid'], $fields, 0);
                $relationContacts[$user['uid']] = $id_contact;

                if(!$user['gid']) {
                    $relationWithoutGroups[$user['uid']] = $id_contact;
                }
            }

            foreach($pcGroups as $group) {
                $id_group = $this->connect->addressgroups()->create($bookId, $group['gid'], 0);
                $relationGroups[$group['gid']] = $id_group;
            }

            foreach($pcGroupUser as $gu) {
                $id_group = $relationGroups[$gu['gid']];
                $id_contact = $relationContacts[$gu['uid']];
                $this->connect->addressRelContacts()->create($id_group, $id_contact);
            }

            foreach($relationWithoutGroups as $id_contact) {
                $this->connect->addressRelContacts()->create($relationGroups[$withoutGroupName], $id_contact);
            }

        }

        //exit;
        $this->connect->db->commit();
    }

    /**
     * Create data for Private Contacts
     * @param $uid
     * @param bool $addressBookName
     * @param array $defaultGroups
     */
    public function createPrivateContacts($uid, $addressBookName = false, array $defaultGroups = [])
    {
        $addressBookName = $addressBookName ? $addressBookName : 'Contacts';
        $groups = $defaultGroups ? $defaultGroups : ['Work', 'Home'];

        $this->connect->db->beginTransaction();
        $bookId = $this->connect->addressbook()->create($addressBookName, $uid, 0, 1);
        if($bookId){
            foreach($groups as $group) {
                // Create group with name = $group
                $groupId = $this->connect->addressgroups()->create($bookId, $group, 1);
            }
        }
        $this->connect->db->commit();
    }

    /**
     * Get records of ProjectContacts
     * @return bool|array|null
     */
    public function getProjectContacts()
    {
        $addressBook = $this->connect->select('*', $this->connect->addressbook()->getTableName(),
            'is_project = ?', [1]);

        if($addressBook) {
            $data['book'] = $addressBook[0];

            $data['groups'] = $this->connect->select('*', $this->connect->addressgroups()->getTableName(),
                'id_book = ?', [$data['book']['id_book']]);

            $data['contacts'] = $this->connect->addresscontacts()->getContactsByAddressbook($data['book']['id_book'], true);

            return $data;
        }

        return false;
    }


    /**
     * Get records of AddressBook $id_book
     * @param $id_book
     * @return bool|array|null
     */
    public function getContactsByAddressBook($id_book)
    {
        $addressBook = $this->connect->select('*', $this->connect->addressbook()->getTableName(),
            'id_book = ?', [$id_book]);

        if($addressBook) {

            $data['book'] = $addressBook[0];

            $data['groups'] = $this->connect->select('*', $this->connect->addressgroups()->getTableName(),
                'id_book = ?', [$data['book']['id_book']]);

            $data['contacts'] = $this->connect->addresscontacts()->getContactsByAddressbook($data['book']['id_book'], true);

            return $data;
        }

        return false;
    }

    /**
     * Get all Custom "Private" AddressBooks
     * @param $uid
     * @return mixed
     */
    public function getAllCustomAddressBooks($uid)
    {
        $data = $this->connect->select('*', $this->connect->addressbook()->getTableName(),
            'is_project = 0 AND uid = ?', [$uid]);

        return $data;
    }


    public function updateProjectContacts($projectContacts)
    {
        $addressBookId = $projectContacts['book']['id_book'];
        $addressBookName = 'Project Contacts';
        $addressBookNameOwner = 'collab_user';
        $withoutGroupName = 'Without Group';

        $pcUsers = $this->connect->users()->pcGetUsers();
        $pcGroups = $this->connect->users()->pcGetGroups();
        $pcGroupUser = $this->connect->users()->pcGetGroupUser();
        $pcGroups[] = ['gid' => $withoutGroupName];

        $oldGroups = $this->connect->select('*', $this->connect->addressgroups()->getTableName(),
            'id_book = ?', [$addressBookId]);

        $oldContacts = $this->connect->addresscontacts()->getContactsByAddressbook($addressBookId);

        $oldArrGroups  = array_map(function($item){ return $item['name'];}, $oldGroups);
        $pcArrGroups = array_map(function($item){ return $item['gid'];}, $pcGroups);

        $this->connect->db->beginTransaction();

        // удаленные группы
        if ($removed = array_diff($oldArrGroups, $pcArrGroups)) {
            // удалить группу & перенести всех пользователей в группу без-группы
            foreach ($removed as $gName) {
                $this->connect->addressgroups()->removeGroupAndReplaceUsersTo($gName, $withoutGroupName);
            }
        }

        //  добавленные группы
        if ($added = array_diff($pcArrGroups, $oldArrGroups)) {
            foreach ($added as $gName) {
                $this->connect->addressgroups()->create($addressBookId, $gName, 0);
            }
        }

        // Part 1, check and update contacts
        foreach ($pcUsers as $user) {
            $this->checkedUpdateContactUser($user, $projectContacts);
        }
        // Part 2, check and update contacts, multi groups
       foreach ($this->multiGroups as $uid => $multiData) {
           // delete user
           if (!empty($multiData)) {
               $id_rel_contact = array_values($multiData)[0]['id_rel_contact'];
               $this->connect->addressRelContacts()->removeAllIn([$id_rel_contact]);
           }
        }

        $this->connect->addressbook()->setLastUpdate($addressBookId);

        //exit;
        $this->connect->db->commit();

    }

    private $multiGroups = [];

    /**
     * @param $user
     * @param $projectContacts
     */
    public function checkedUpdateContactUser($user, $projectContacts)
    {
        $withoutGroupName = 'Without Group';
        $addressBookId = $projectContacts['book']['id_book'];
        $userContacts = $this->connect->addresscontacts()->getAllGroupsForUserProjectContacts($user['uid']);

        // New Contact
        if(empty($userContacts)) {
            $group = $this->connect->addressgroups()->getOneByName($user['gid']);
            $fields = [
                'email1' => $user['email'],
                'display_name' => $user['displayname'] ? $user['displayname'] : $user['uid']
            ];
            if ($group) {
                $id_contact = $this->connect->addresscontacts()->create($user['uid'], $fields, 0);
                $this->connect->addressRelContacts()->create($group['id_group'], $id_contact);
            }
        }

        // update or create
        else if (count($userContacts) == 1) {
            if ($userContacts[0]['name'] !== $user['gid']) {
                if ($user['gid'] == null && $userContacts[0]['name'] != $withoutGroupName) {
                    $this->connect->addressgroups()->replaceContactToGroup($userContacts[0]['name'], $withoutGroupName);
                }else if ($user['gid']){
                    // Add user to group
                    $group = $this->connect->addressgroups()->getOneByName($user['gid']);
                    $this->connect->addressRelContacts()->create($group['id_group'], $userContacts[0]['id_contact']);
                }
            }
        }
        // множество групп
        else if (count($userContacts) > 1) {

            if (!isset($this->multiGroups[$user['uid']])) {
                $this->multiGroups[$user['uid']] = $userContacts;
            }

            if (isset($this->multiGroups[$user['uid']])) {
                foreach ($this->multiGroups[$user['uid']] as $index => $multiData) {
                    if ($user['gid'] == $multiData['name']) {
                        unset($this->multiGroups[$user['uid']][$index]);
                    }
                }
            }

        }

    }


    /** @var  \OC\User\Manager $userManager */
    private $userManager;
    /** @var \OC\Group\Manager $groupManager */
    private $groupManager;
    /** @var  Memory $session */
    private $session;
    /** @var  \OC\User\Session $userSession */
    private $userSession;

    /**
     * enable Triggers for Listen
     */
    public function enableTriggers()
    {
        $this->userManager  = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();
        $this->session      = new \OC\Session\Memory('');
        $this->userSession  = new \OC\User\Session($this->userManager, $this->session);

        $this->triggersListeners();
    }

    /**
     * Listener
     */
    public function triggersListeners()
    {
        $userSession = $this->userSession;
        $groupManager = $this->groupManager;

        if($userSession instanceof \OC\User\Session) {

            $groupManager->listen('\OC\Group', 'preAddUser', [$this, 'onAddUserToGroup']);
            $groupManager->listen('\OC\Group', 'preRemoveUser', [$this, 'onRemoveUserFromGroup']);

            $userSession->listen('\OC\User', 'postCreateUser', [$this, 'onCreateUser']);
            $userSession->listen('\OC\User', 'postDelete', [$this, 'onDeleteUser']);

            $groupManager->listen('\OC\Group', 'postCreate', [$this, 'onCreateGroup']);
            $groupManager->listen('\OC\Group', 'postDelete', [$this, 'onDeleteGroup']);

            $userSession->listen('\OC\User', 'changeUser', [$this, 'onChangeUser']);
        }
    }

    /**
     * Добавление нового пользователя
     * @param \OC\User\User $user
     */
    public function onCreateUser(\OC\User\User $user)
    {
        $uid = $user->getUID();
        $uEmail = $user->getEMailAddress();

        //sleep(1);
        //$user = $this->connect->addresscontacts()->getOneByUid($uid);

        //$uGroup = $user->();
/*
        $this->connect->db->beginTransaction();
        $this->connect->addresscontacts()->create($uid, [
            'display_name' => ucfirst($uid),
            'email1' => $uEmail,
        ], false);
        $this->connect->db->commit();*/


        Helper::appLoger('Event: Create-User; user: ' . $uid);
    }

    /**
     * Удаления пользователя
     * @param \OC\User\User $user
     */
    public function onDeleteUser(\OC\User\User $user)
    {
        // remove all relations
        // remove contact
        $error = null;
        $uid = $user->getUID();
        //$error = $this->connect->addresscontacts()->removeByUidWithRelations($uid);
        Helper::appLoger('Event: Delete-User: '.$uid. ' Error: '.$error);
    }


    /**
     * Создание новой группы
     * @param \OC\Group\Group $group
     */
    public function onCreateGroup(\OC\Group\Group $group)
    {
        $gid = $group->getGID();

        $pcBook = $this->connect->addressbook()->getProjectContactBook();
        $this->connect->addressgroups()->create($pcBook['id_book'], $gid, false);

        Helper::appLoger('Event: Create-Group; Group: '.$gid);
    }
    /**
     * Удаление группы
     * @param \OC\Group\Group $group
     */
    public function onDeleteGroup(\OC\Group\Group $group)
    {
        $gid = $group->getGID();
        $withoutGroupName = 'Without Group';

        //$pcBook = $this->connect->addressbook()->getProjectContactBook();
        //$this->connect->addressgroups()->removeGroup($pcBook['id_book'], $gid);
        $this->connect->addressgroups()->removeGroupAndReplaceUsersTo($gid, $withoutGroupName);

        Helper::appLoger('Event: Delete-Group; Group: '.$gid);
    }


    /**
     * Добавление нового пользователя вместе с добавлением его в группу
     * @param \OC\Group\Group $group
     * @param \OC\User\User $user
     */
    public function onAddUserToGroup(\OC\Group\Group $group, \OC\User\User $user)
    {
        $uid = $user->getUID();
        $gid = $group->getGID();
        /*$email1 = $user->getEMailAddress();

        $userContact = $this->connect->addresscontacts()->getOneByUid($uid);
        $contactGroup = $this->connect->addressgroups()->getOneByName($gid);

        if(!$userContact) {

            $this->connect->db->beginTransaction();

            $id_contact = $this->connect->addresscontacts()->create($uid, [
                'display_name' => ucfirst($uid),
                'email1' => $email1,
            ], false);

            if($contactGroup)
                $this->connect->addressRelContacts()->create($contactGroup['id_group'], $id_contact);

            $this->connect->db->commit();

        } else {

            //$user['id_contact']
            //$this->connect->addressRelContacts()->create($gid, $uid);
            //$this->connect->addressRelContacts()->remove();
            //$this->connect->addressRelContacts()->update();
        }



        Helper::appLoger('Event: Add-User-To-Group '.json_encode([$uid, $gid, $email1, $user]));
        */
    }
    /**
     * Удаление пользоватея, он состоит в группе
     * @param \OC\Group\Group $group
     * @param \OC\User\User $user
     */
    public function onRemoveUserFromGroup(\OC\Group\Group $group, \OC\User\User $user)
    {
        $data = ['uid' => $user->getUID(), 'gid' => $group->getGID()];
        Helper::appLoger('Event: Remove-User-From-Group '.json_encode($data));
    }


    /**
     * Все изминения над пользователем, нужно отлавлевать доб./уд.в группы
     * @param \OC\User\User $user
     * @param $feature
     * @param $value
     */
    public function onChangeUser(\OC\User\User $user, $feature, $value) {
        /** @var $user \OC\User\User */
        $data = ['user' => $user->getUID(), 'feature' => $feature, 'value' => $value];
        Helper::appLoger('Event: changeUser: ' . json_encode($data));
    }


}
