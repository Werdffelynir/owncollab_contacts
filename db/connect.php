<?php

namespace OCA\Owncollab_Contacts\Db;


use \OCP\IDBConnection;

class Connect
{
    /** @var IDBConnection  */
    public $db;

    /** @var Users  database table */
    private $users;
    private $addressbook;
    private $addresscontacts;
    private $addressgroups;
    private $addressRelContacts;
    private $addressShare;

    /**
     * Connect constructor.
     * @param IDBConnection $db
     */
    public function __construct(IDBConnection $db) {
        $this->db = $db;

        // Register tables models
        $this->users = new Users($this, '*PREFIX*users');
        $this->addressbook = new Addressbook($this, '*PREFIX*collab_addressbook');
        $this->addresscontacts = new Addresscontacts($this, '*PREFIX*collab_addresscontacts');
        $this->addressgroups = new Addressgroups($this, '*PREFIX*collab_addressgroups');
        $this->addressRelContacts = new AddressRelContacts($this, '*PREFIX*collab_address_rel_contacts');
        $this->addressShare = new AddressShare($this, '*PREFIX*collab_address_share');
    }

    /**
     * Execute prepare SQL string $query with binding $params, and return one record
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function query($query, array $params = []) {
        return $this->db->executeQuery($query, $params)->fetch();
    }

    /**
     * Execute prepare SQL string $query with binding $params, and return all match records
     * @param $query
     * @param array $params
     * @return mixed
     */
    public function queryAll($query, array $params = []) {
        return $this->db->executeQuery($query, $params)->fetchAll();
    }

    /**
     * Quick selected records
     * @param $fields
     * @param $table
     * @param null $where
     * @param array $params
     * @return mixed
     */
    public function select($fields, $table, $where = null, $params = []) {
        $sql = "SELECT " . $fields . " FROM " . $table . ($where ? " WHERE " . $where : "") . ";";
        return  $this->queryAll($sql, (array) $params);
    }

    /**
     * Quick insert record
     * @param $table
     * @param array $columnData
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function insert($table, array $columnData) {
        $columns = array_keys($columnData);
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s);",
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columnData), '?'))
        );
        $this->db->executeQuery($sql, array_values($columnData));
        return $this->db->lastInsertId($table);
    }

    /**
     * Quick delete records
     * @param $table
     * @param $where
     * @param null $bind
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function delete($table, $where, $bind=null) {
        $sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
        return $this->db->executeQuery($sql, $bind);
    }

    /**
     * Quick update record
     * @param $table
     * @param $where
     * @param null $bind
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function update($table, array $columnData, $where, $bind=null) {
        $columns = array_keys($columnData);
        $where = preg_replace('|:\w+|','?', $where);
        if(empty($bind)) $bind = array_values($columnData);
        else $bind = array_values(array_merge($columnData, (array) $bind));
        $sql = sprintf("UPDATE %s SET %s WHERE %s;", $table, implode('=?, ', $columns) . '=?', $where);
        return $this->db->executeQuery($sql, $bind);
    }


    /**
     * Retry instance of class working with database
     * Table of collab_users
     * @return Users
     */
    public function users() {
        return $this->users;
    }

    /**
     * @return Addressbook
     */
    public function addressbook() {
        return $this->addressbook;
    }

    /**
     * @return Addresscontacts
     */
    public function addresscontacts() {
        return $this->addresscontacts;
    }

    /**
     * @return Addressgroups
     */
    public function addressgroups() {
        return $this->addressgroups;
    }

    /**
     * @return AddressRelContacts
     */
    public function addressRelContacts() {
        return $this->addressRelContacts;
    }

    /**
     * @return AddressShare
     */
    public function addressShare() {
        return $this->addressShare;
    }

}