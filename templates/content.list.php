<?php
/**
 * @var array $_
 */

$projectUsers = !empty($_['projectUsers']) && is_array($_['projectUsers']) ? $_['projectUsers'] : [];


//var_dump($projectUsers);

?>

<div id="users_list">

    <div class="tbl ul_header">
        <div class="tbl_cell">Имя пользователя</div>
        <div class="tbl_cell">Полное имя</div>
        <div class="tbl_cell">E-mail</div>
        <div class="tbl_cell">Office tel</div>
        <div class="tbl_cell">Home tel</div>
        <div class="tbl_cell">Группы</div>
    </div>

    <?php foreach($projectUsers as $urs):

        $displayname = $urs['first_name'].' '.$urs['last_name'];
        if(empty($displayname))
            $displayname = !empty($urs['displayname']) ? $urs['displayname'] : $urs['uid'];

        $usrGroups = join(", ", $urs['gid']);
        ?>
        <div class="tbl ul_item">
            <div class="tbl_cell"><?php p($urs['uid']) ?></div>
            <div class="tbl_cell"><?php p($displayname) ?></div>
            <div class="tbl_cell"><?php p($urs['email']) ?></div>
            <div class="tbl_cell"><?php p($urs['office_tel']) ?></div>
            <div class="tbl_cell"><?php p($urs['home_tel']) ?></div>
            <div class="tbl_cell"><strong><?php p($usrGroups) ?></strong></div>
        </div>
    <?php endforeach; ?>

</div>

<?php
/*
array (size=7)
  'admin' =>
    array (size=8)
      'uid' => string 'admin' (length=5)
      'displayname' => null
      'gid' =>
        array (size=1)
          0 => string 'admin' (length=5)
      'email' => string 'admin@admincopy.com' (length=19)
      'first_name' => string 'Toter' (length=5)
      'last_name' => string 'Fredrix' (length=7)
      'office_tel' => string '+123456789' (length=10)
      'home_tel' => null
*/
?>