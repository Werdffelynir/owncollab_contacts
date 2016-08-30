<?php
/**
 * Created by PhpStorm.
 * User: olexiy
 * Date: 10.02.16
 * Time: 14:49
 */

namespace OCA\Owncollab_Contacts\Db;


use OCA\Owncollab_Contacts\vCard;

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
     * @return array|null
     */
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

    /**
     * @return array

    public function getResourcesOwncollabInProjectOnly()
    {
        $uids = [];
        $gu = $this->getResourcesOwncollab();

        if($gu) {
            $uids = !empty($gu['users']) ? $gu['users'] : [];
            if(!empty($gu['groups'])) {
                $ins =  "'" . join("', '", $gu['groups']) . "'";

                $sql = "SELECT u.uid
                        FROM *PREFIX*users u
                        LEFT JOIN *PREFIX*group_user gu ON (gu.uid = u.uid)
                        WHERE gu.gid IN ($ins);";

                $result = $this->connect->queryAll($sql);
                if($result)
                    $uids = array_merge(array_map(function ($rec) { return $rec['uid']; }, $result), $gu['users']);
            }
        }

        return array_unique($uids);
    } */


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
}