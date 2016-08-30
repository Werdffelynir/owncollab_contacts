<?php


namespace OCA\Owncollab_Contacts;

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
     * The constructor.
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


}
