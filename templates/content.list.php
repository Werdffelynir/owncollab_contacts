<?php
/**
 * @var array $_
 */

$projectUsers = !empty($_['projectUsers']) && is_array($_['projectUsers']) ? $_['projectUsers'] : [];



?>

<div id="users_list">

    <div class="tbl ul_header">
        <div class="tbl_cell">Имя пользователя</div>
        <div class="tbl_cell">Полное имя</div>
        <div class="tbl_cell">E-mail</div>
        <div class="tbl_cell">Группы</div>
    </div>

    <?php foreach($projectUsers as $urs):
        $displayname = !empty($urs['displayname']) ? $urs['displayname'] : $urs['uid'];
        $usrGroups = join(", ", $urs['gid']);
        ?>
        <div class="tbl ul_item">
            <div class="tbl_cell"><?php p($urs['uid']) ?></div>
            <div class="tbl_cell"><?php p($displayname) ?></div>
            <div class="tbl_cell"><?php p($urs['email']) ?></div>
            <div class="tbl_cell"><strong><?php p($usrGroups) ?></strong></div>
        </div>
    <?php endforeach; ?>

</div>

<?php
/*
  'dev_man' =>
    array (size=4)
      'uid' => string 'dev_man' (length=7)
      'displayname' => null
      'gid' =>
        array (size=2)
          0 => string 'developers' (length=10)
          1 => string 'managers' (length=8)
      'email' => null
  'werd' => */
?>