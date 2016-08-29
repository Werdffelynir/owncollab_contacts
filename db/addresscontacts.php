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
        'name',
        'fields',
        'is_private',
    ];

    // name => type
    private $formFieldsTypes = [
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
        'email2' => 'Email',
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
        return $this->formFieldsTypes;
    }

    /**
     * @return mixed
     */
    public function getAll()
    {

    }



}