<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;



class Addressgroups
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /** Table: oc_collab_addressgroups
     * @var string
     */
    private $tableName;


    /** @var string $fields table fields name in database */
    private $fields = [
        'id_group',
        'id_book',
        'name',
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
     * Create Group for id_book
     *
     * @param $id_book
     * @param $name
     * @param bool $is_private
     * @return bool|int
     */
    public function create($id_book, $name, $is_private = true)
    {
        $sql = "INSERT INTO $this->tableName (`id_book`, `name`, `is_private`)
                VALUES (:id_book, :name, :is_private)";

        $PDOStatement = $this->connect->db->executeQuery($sql, [
            ':id_book' => $id_book,
            ':name' => $name,
            ':is_private' => $is_private,
        ]);

        return $PDOStatement ? $this->connect->db->lastInsertId($this->tableName) : false;
    }


}