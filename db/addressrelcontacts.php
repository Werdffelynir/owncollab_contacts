<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;



class AddressRelContacts
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /** Table: oc_collab_address_rel_contacts
     * @var string
     */
    private $tableName;

    /** @var string $fields table fields name in database */
    private $fields = [
        'id_rel_contact',
        'id_group',
        'id_contact',
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
    public function getAllByUid($uid)
    {
        $sql = "SELECT r.*
                FROM *PREFIX*collab_address_rel_contacts r
                LEFT JOIN *PREFIX*collab_addresscontacts c ON (c.id_contact = r.id_contact)
                WHERE c.uid = ?";

        return $this->connect->queryAll($sql, [$uid]);
    }

    public function removeAllIn($ids)
    {
        if(!empty($ids)) {
            $idsString = join(', ', $ids);
            return $this->connect->delete($this->tableName, 'id_rel_contact IN ('.$idsString.')');
        }
    }


    public function getAllByContactId()
    {

    }

    public function getAllByGroupId()
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
     * @param $id_group
     * @param $id_contact
     * @return bool|int
     */
    public function create($id_group, $id_contact)
    {
        $sql = "INSERT INTO $this->tableName (`id_group`, `id_contact`)
                VALUES (:id_group, :id_contact)";

        $PDOStatement = $this->connect->db->executeQuery($sql, [
            ':id_group' => $id_group,
            ':id_contact' => $id_contact,
        ]);

        return $PDOStatement ? $this->connect->db->lastInsertId($this->tableName) : false;
    }

}