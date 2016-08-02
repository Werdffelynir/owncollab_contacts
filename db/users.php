<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

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
        $this->tableName = '*PREFIX*' . $tableName;
    }


    /**
     * @return mixed
     */
    public function getAll()
    {
        $users = $this->connect->queryAll("SELECT * FROM " . $this->tableName . " ORDER BY displayname, uid");
        return $users;
    }


    public function getResourcesOwncollab()
    {
        $groupsUsers = ['users'=>[],'groups'=>[]];
        $resGroupsUsers = $this->connect->queryAll("SELECT users FROM *PREFIX*collab_tasks");
        if ($resGroupsUsers) {
            for ($i = 0; $i < count($resGroupsUsers); $i++) {
                if (!empty($resGroupsUsers[$i]['users'])) {
                    try {
                        $gu = json_decode($resGroupsUsers[$i]['users'], true);

                        if(!empty($gu['users']))
                            $groupsUsers['users'] = array_merge($groupsUsers['users'], $gu['users']);

                        if(!empty($gu['groups']))
                            $groupsUsers['groups'] = array_merge($groupsUsers['groups'], $gu['groups']);

                    } catch (\Exception $e) {
                    }
                }
            }
        }
        return $groupsUsers['users'] || $groupsUsers['groups'] ? $groupsUsers : null;
    }

    public function getResourcesOwncollabAllUsersOnly()
    {
        $uids = [];
        $gu = $this->getResourcesOwncollab();

        if($gu) {
            $uids = !empty($gu['users']) ? $gu['users'] : [];
            if(!empty($gu['groups'])) {
                $ins =  "'" . join("', '", $gu['groups']) . "'";
                $sql = "SELECT u.uid
                        FROM oc_users u
                        LEFT JOIN oc_group_user gu ON (gu.uid = u.uid)
                        WHERE gu.gid IN ($ins);";
                $result = $this->connect->queryAll($sql);
                if($result)
                    $uids = array_merge(array_map(function ($rec) { return $rec['uid']; }, $result), $gu['users']);
            }
        }

        return array_unique($uids);
    }


    public function getAllIn($ids)
    {
        $ins =  "'" . join("', '", $ids) . "'";
        $sql = "SELECT
                    u.uid,
                    u.displayname,
                    gu.gid,
                    p.configvalue as email
                FROM oc_users u
                LEFT JOIN oc_group_user gu ON (gu.uid = u.uid)
                LEFT JOIN oc_preferences p ON (p.userid = u.uid AND appid = 'settings' AND configkey = 'email')
                WHERE u.uid IN ($ins)";

        $resultFormatted = [];
        $result = $this->connect->queryAll($sql);
        if($result) {
            foreach($result as $rec) {
                if(isset($resultFormatted[$rec['uid']])) {
                    $resultFormatted[$rec['uid']]['gid'][] = $rec['gid'];
                } else {
                    $resultFormatted[$rec['uid']] = $rec;
                    if(!is_array($resultFormatted[$rec['uid']]['gid']))
                        $resultFormatted[$rec['uid']]['gid'] = [];
                    $resultFormatted[$rec['uid']]['gid'][] = $rec['gid'];
                }
            }
        }

        return $resultFormatted;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $user = $this->connect->select("*", $this->tableName, "uid = :id", [':id' => $id]);
        return $user;
    }


    public function getByEmail($email)
    {
        $sql = "SELECT *
                FROM *PREFIX*preferences
                WHERE appid = 'settings' AND configkey = 'email' AND configvalue = :email";

        return $this->connect->query($sql, [':email'=>$email]);
    }


    /**
     * Retrieve all records from Users
     *
     * @return mixed
     */
    public function getGroupsUsers()
    {
        $sql = "SELECT gu.uid, gu.gid, u.displayname, p.configvalue as email
                FROM *PREFIX*group_user gu
                LEFT JOIN *PREFIX*users u ON (u.uid = gu.uid)
                LEFT JOIN *PREFIX*preferences p ON (p.userid = gu.uid AND p.appid = 'settings' AND p.configkey = 'email')";

        return $this->connect->queryAll($sql);
    }


    /**
     * Retrieve all registered resource
     *
     * @param bool $refresh
     * @return array|null [0][gid]=>[gid,uid,displayname,email]
     */
    public function getGroupsUsersList($refresh = false)
    {
        $result = [];
        static $records = null;
        if($records === null || $refresh)
            $records = $this->getGroupsUsers();

        // Operation iterate and classify users into groups
        foreach ($records as $record) {
            $result[$record['gid']][] = [
                'email' => $record['email'],
                'gid' => $record['gid'],
                'uid' => $record['uid'],
                'displayname' => ($record['displayname']) ? $record['displayname'] : $record['uid']
            ];
        }
        return $result;
    }


    /**
     * @param $uid
     * @param bool $refresh
     * @return bool
     */
    public function getUserData($uid, $refresh = false)
    {
        static $usersData = null;

        if($usersData === null || $refresh)
            $usersData = $this->getGroupsUsers();

        if(is_array($usersData)) {
            for($i=0;$i<count($usersData);$i++) {
                if($usersData[$i]['uid'] == $uid) {
                    if(empty($usersData[$i]['displayname'])) $usersData[$i]['displayname'] = $uid;
                    return $usersData[$i];
                }
            }
        }
        return false;
    }

    /**
     * @param bool $refresh
     * @return mixed|null
     */
    public function getUngroupUsers($refresh = false)
    {
        static $usersData = null;

        if($usersData === null || $refresh) {
            $sql = "SELECT u.uid, u.displayname, p.configvalue as email
                    FROM *PREFIX*users u
                    LEFT OUTER JOIN *PREFIX*group_user gu ON (gu.uid = u.uid)
                    LEFT JOIN *PREFIX*preferences p ON (p.userid = u.uid AND p.appid = 'settings' AND p.configkey = 'email')
                    WHERE gu.uid IS NULL";

            $usersData = $this->connect->queryAll($sql);
        }
        return $usersData;
    }

    public function getUngroupUsersList($refresh = false) {}

    public function getUserContacts($uid) {
        $sql = "SELECT *
                FROM *PREFIX*preferences
                WHERE appid = 'owncollab_contacts' AND userid = ? OR (appid = 'settings' AND configkey = 'email' AND userid = ?)";
        return $this->connect->queryAll($sql, [$uid,$uid]);
    }

/*Table: oc_preferences
Columns:
userid	varchar(64) PK
appid	varchar(32) PK
configkey	varchar(64) PK
configvalue	longtext*/

    public function updateUserEmail($uid, $email)
    {
        return $this->connect->update('*PREFIX*preferences',
            ["configvalue" => $email], "userid = ? AND appid = 'settings' AND configkey = 'email'",
            [$uid]
        );
    }

    public function insertOrUpdateUserContact($uid, $key, $value)
    {
        $tbl = '*PREFIX*preferences';
        $select = $this->connect->select('*', $tbl, "userid = ? AND appid = 'owncollab_contacts' AND configkey = ?",
            [$uid, $key]);

        if($select) {
            return $this->connect->update($tbl,
                ["configvalue" => $value], "userid = ? AND appid = 'owncollab_contacts' AND configkey = ?",
                [$uid, $key]
            );
        }else{
            $this->connect->insert($tbl,[
                'userid' => $uid,
                'appid' => 'owncollab_contacts',
                'configkey' => $key,
                'configvalue' => $value,
            ]);
            return $this->connect->db->lastInsertId($tbl);
        }
    }


}