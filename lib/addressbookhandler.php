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


    public function createProjectContacts()
    {
        $addressBookName = 'Project Contacts';
        $groupsUsers = $this->connect->users()->getGroupsUsersList();
        $ungroupsUsers = $this->connect->users()->getUngroupUsers();
        $deniedUsers = ['collab_user'];

        $this->connect->db->beginTransaction();
        $bookId = $this->connect->addressbook()->create($addressBookName, 'admin', 1, 0);
        if($bookId){
            // Create addresses for users with group
            foreach($groupsUsers as $group => $users){
                // Create group name = $group
                $groupId = $this->connect->addressgroups()->create($bookId, $group, 0);
                foreach($users as $user) {
                    // Disable an denied users
                    if(in_array($user['uid'], $deniedUsers)) continue;
                    // Create user contact for $group
                    $fields = ['email1' => $user['email'], 'display_name' => $user['displayname']];
                    $contactId = $this->connect->addresscontacts()->create($user['uid'], $fields, 0);
                    $this->connect->addressRelContacts()->create($groupId, $contactId);
                }
            }

            // Create addresses for users without group
            if($ungroupsUsers) {
                $groupId = $this->connect->addressgroups()->create($bookId, 'without_group', 0);
                foreach($ungroupsUsers as $user){
                    // Disable an denied users
                    if(in_array($user['uid'], $deniedUsers)) continue;
                    // Create user contact for $group
                    $fields = ['email1' => $user['email'], 'display_name' => $user['displayname']];
                    $contactId = $this->connect->addresscontacts()->create($user['uid'], $fields, 0);
                    $this->connect->addressRelContacts()->create($groupId, $contactId);
                }
            }
        }
        $this->connect->db->commit();
    }

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


    public function getProjectContacts()
    {
        $addressBook = $this->connect->select('*', $this->connect->addressbook()->getTableName(),
            'is_project = ?', [1]);

        if($addressBook) {
            $data['book'] = $addressBook[0];

            $data['groups'] = $this->connect->select('*', $this->connect->addressgroups()->getTableName(),
                'id_book = ?', [$data['book']['id_book']]);

            $data['contacts'] = $this->connect->addresscontacts()->getContactsByAddressbook($data['book']['id_book'], true);
            //

            return $data;
        }

        return false;
    }

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

    public function getAllCustomAddressBooks($uid)
    {
        $data = $this->connect->select('*', $this->connect->addressbook()->getTableName(),
            'is_project = 0 AND uid = ?', [$uid]);

        return $data;
    }

    /**
     * Streams the vcard to the browser client.
     */
    public function download()
    {

    }

    /**
     * Show the vcard.
     */
    public function show()
    {

    }

    /** @var  \OC\User\Manager $userManager */
    private $userManager;
    /** @var \OC\Group\Manager $groupManager */
    private $groupManager;
    /** @var  Memory $session */
    private $session;
    /** @var  \OC\User\Session $userSession */
    private $userSession;

    public function enableTriggers()
    {
        $this->userManager  = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();
        $this->session      = new \OC\Session\Memory('');
        $this->userSession  = new \OC\User\Session($this->userManager, $this->session);

        $this->triggersListeners();
    }

/**function($gid){
Helper::appLoger('postCreate $gid: '. $gid->getGID());
//\OC_Hook::emit('OC_User', 'post_createGroup', array('gid' => $gid->getGID()));
}*/
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
        $this->connect->db->commit();
        */

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
        $error = $this->connect->addresscontacts()->removeByUidWithRelations($uid);
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
        $pcBook = $this->connect->addressbook()->getProjectContactBook();
        $this->connect->addressgroups()->removeGroup($pcBook['id_book'], $gid);

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
        $email1 = $user->getEMailAddress();

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
