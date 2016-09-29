<?php

namespace OCA\Owncollab_Contacts\Db;


class Addressbook
{
    /**
     * @var  Connect $connect
     */
    private $connect;

    /**
     * oc_addressbooks
     * @var
     */
    private $tableName;


    /**
     * Users constructor.
     * @param $connect
     * @param $tableName
     */
    public function __construct($connect, $tableName)
    {
        $this->connect = $connect;
        $this->tableName = $tableName;
    }


    /**
     * Simple checked record of project contacts book, and insert it if need
     * @param $addressBookId
     * @param $uid
     * @return int
     */
    public function shareProjectContact($addressBookId, $uid)
    {
        $result = $this->connect->db->insertIfNotExist('*PREFIX*dav_shares', [
            'principaluri' => "principals/users/$uid",
            'type' => "addressbook",
            'access' => 2,
            'resourceid' => $addressBookId,
        ]);
        return $result;
    }



}