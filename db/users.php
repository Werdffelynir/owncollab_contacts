<?php

namespace OCA\Owncollab_Contacts\Db;


class Users
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /**
     * @var string
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
     * @return mixed
     */
    public function getAll()
    {
        $users = $this->connect->queryAll("SELECT * FROM " . $this->tableName . " ORDER BY displayname, uid");

        return $users;
    }

    public function getAllWithEmail()
    {
        $sql = "SELECT u.uid, u.displayname, p.configvalue as email
                FROM *PREFIX*users u
                LEFT JOIN *PREFIX*preferences p ON ( p.userid = u.uid AND p.appid = 'settings' AND p.configkey = 'email')";
        $users = $this->connect->queryAll($sql);
        return $users;
    }


    public function getAllIds()
    {
        $users = $this->connect->queryAll("SELECT uid FROM " . $this->tableName);
        return is_array($users) ? $users : [];
    }




}