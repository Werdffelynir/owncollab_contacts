<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;



class Addresscontacts
{
    /**
     * @var  Connect $connect
     */
    private $connect;


    /** Table: oc_collab_addresscontacts
     * @var string
     */
    private $tableName;

    /** @var string $fields table fields name in database */
    private $fields = [
        'id_contact',
        'uid',
        'fields',
        'is_private',
    ];

    // name => type
    private $fieldsTypes = [
        'display_name' => 'Name',
        'department' => 'Group',
        'company' => 'Company',
        'work_country' => 'Country',
        'work_city' => 'City',
        'office_tel' => 'Phone',
        'work_address' => 'Address',
        'home_tel' => 'Home Phone',
        'home_address' => 'Home Address',
        'birthday' => 'Birthday',
        'email1' => 'Email',
        'email2' => 'Email 2',
        'note' => 'Notes',
        'url' => 'Website',
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

    public function getFormFieldsTypes()
    {
        return $this->fieldsTypes;
    }

    public function defaultFields(array $rec = [], $clean = true)
    {
        $fields = array_flip($this->fields);
        if ($clean) array_walk($fields, function(&$value){$value = '';});
        $fieldsData = array_merge($fields, $rec);
        if(empty($rec)) return $fieldsData;
        else {
            return array_diff_key($fieldsData, array_diff_key($fieldsData, $fields));
        }
    }

    public function defaultFieldsTypes(array $rec = [], $clean = false)
    {
        $fields = $this->fieldsTypes;
        if ($clean)
            array_walk($fields, function(&$value){$value = '';});
        $fieldsData = array_merge($fields, $rec);

        if(empty($rec)) return $fieldsData;
        else {
            return array_diff_key($fieldsData, array_diff_key($fieldsData, $fields));
        }
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->connect->select('*', $this->tableName);
    }


    /**
     * @param $id_contact
     * @return null|array
     */
    public function getOne($id_contact)
    {
        $result = $this->connect->select('*', $this->tableName, 'id_contact = ?', [$id_contact]);
        return $result ? $result[0] : null;
    }

    /**
     * Обновляет поля одного контакта
     * @param $contactId
     * @param $fields
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function updateContactFields($contactId, $fields)
    {
        $result = $this->connect->update($this->tableName, [
            'fields' => $fields
        ], 'id_contact = ?', [$contactId]);

        return $result->errorCode();
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param $uid
     * @param $fields
     * @param $is_private
     * @return bool|int
     */
    public function create($uid, $fields, $is_private = true)
    {
        $fields = $this->defaultFieldsTypes((array) $fields, true);

        $sql = "INSERT INTO $this->tableName (`uid`, `fields`, `is_private`)
                VALUES (:uid, :fields, :is_private)";

        $PDOStatement = $this->connect->db->executeQuery($sql, [
            ':uid' => $uid,
            ':fields' => json_encode($fields),
            ':is_private' => $is_private,
        ]);

        return $PDOStatement ? $this->connect->db->lastInsertId($this->tableName) : false;
    }

    public function getContactsByAddressbook($id_book, $format = false)
    {
        $sql = "SELECT c.*, g.name as groupname
                    FROM *PREFIX*collab_addresscontacts c
                    LEFT JOIN *PREFIX*collab_address_rel_contacts r ON (r.id_contact = c.id_contact)
                    LEFT JOIN *PREFIX*collab_addressgroups g ON (g.id_group = r.id_group)
                    WHERE g.id_book = ?";
        $result =  $this->connect->queryAll($sql, [$id_book]);

        if($format && $result) {
            $_new_result = [];
            for($i=0;$i<count($result);$i++){
                try{
                    $result[$i]['fields'] = json_decode($result[$i]['fields'], true);
                }catch(\Exception $e){}
                $_new_result[$result[$i]['groupname']][] = $result[$i];
            }
            $result = $_new_result;
        }


        return $result;

    }

}