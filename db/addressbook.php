<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;



class Addressbook
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /**
     * Table: oc_collab_addressbook
     * @var string
     */
    private $tableName;

    /** @var string $fields table fields name in database */
    private $fields = [
        'id_book',
        'name',
        'uid',
        'is_project',
        'is_private',
    ];

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

    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param $name
     * @param $uid
     * @param $is_project
     * @param $is_private
     * @return mixed
     */
    public function create($name, $uid, $is_project = false, $is_private = true)
    {
        $sql = "INSERT INTO $this->tableName (`name`, `uid`, `is_project`, `is_private`)
                VALUES (:name, :uid, :is_project, :is_private)";

        $PDOStatement = $this->connect->db->executeQuery($sql, [
            ':name' => $name,
            ':uid' => $uid,
            ':is_project' => $is_project,
            ':is_private' => $is_private,
        ]);

        return $PDOStatement ? $this->connect->db->lastInsertId($this->tableName) : false;
    }




}