<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;



class AddressShare
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /**
     * Table: oc_collab_address_share
     * @var string
     */
    private $tableName;


    /** @var string $fields table fields name in database */
    private $fields = [
        'id_share',
        'id_book',
        'uid_owner',
        'uid_with',
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

    public function shareWith($id_book, $uid_owner, $uid_with, $is_private = true)
    {
        $sql = "INSERT INTO $this->tableName (`id_book`, `uid_owner`, `uid_with`, `is_private`)
                VALUES (:id_book, :uid_owner, :uid_with, :is_private);";

        return $this->connect->query($sql, [
            ':id_book' => $id_book,
            ':uid_owner' => $uid_owner,
            ':uid_with' => $uid_with,
            ':is_private' => $is_private?1:0,
        ]);
    }

}