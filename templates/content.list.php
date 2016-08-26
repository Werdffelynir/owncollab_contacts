<?php
/**
 * @var array $_
 */

//$projectUsers = !empty($_['projectUsers']) && is_array($_['projectUsers']) ? $_['projectUsers'] : [];
//$editableClass = ($_['isAdmin']) ? 'contacteditable' : '';


?>

<div id="users_list">

    <div class="tbl ul_header">
        <div class="tbl_cell">Full name</div>
        <div class="tbl_cell">E-mail</div>
        <div class="tbl_cell">Telephone</div>
        <div class="tbl_cell">Address</div>
        <div class="tbl_cell">Groups</div>
    </div>

    <div class="tbl ul_item" data-uid="">
        <div class="tbl_cell " data-key="displayname">&nbsp;</div>
        <div class="tbl_cell " data-key="email">&nbsp;</div>
        <div class="tbl_cell " data-key="office_tel">&nbsp;</div>
        <div class="tbl_cell " data-key="address">&nbsp;</div>
        <div class="tbl_cell"><strong>&nbsp;</strong></div>
    </div>


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