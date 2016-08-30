<?php

/**
 * @var array $_
 */
/*
$userContacts = !empty($_['userContacts']) && is_array($_['userContacts']) ? $_['userContacts'] : [];

$valIn = function ($key) use ($userContacts) {
    foreach ($userContacts as $cont)
        if($cont['configkey'] == $key) {
            return $cont['configvalue']; break;
        }
    return '';
};*/

//var_dump($_['projectContacts']['book']);

//var_dump($_['customContacts'][0]['book']);

?>

<style>
.add_block{}
.add_group{}
.add_group_item{}
.add_group_item input{}


</style>



<div class="sb_block">
    <div id="add_contact" class="ico_add">Add Contact</div>
    <div id="add_contact_item">
        <input type="text" placeholder="Contact Name" value="">
    </div>
</div>

<div class="sb_block">
    <div id="add_group" class="ico_add">Add Group</div>
    <div id="add_group_item">
        <input type="text" placeholder="Group Name" value="">
    </div>
</div>

<div id="ads_groups">

    <ul>
        <li>Admin</li>
        <li>Developers</li>
        <li>Managers</li>
    </ul>

</div>




























<!--
<div class="vcontact">
    <div class="add_vcontact ico_add">First Name</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="First Name" name="first_name"
                     value="<?/*= $valIn('first_name')*/?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Last Name</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Last Name" name="last_name"
                     value="<?/*= $valIn('last_name')*/?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Office Telephone</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Telephone" name="office_tel"
                     value="<?/*= $valIn('office_tel')*/?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Home Telephone</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Telephone" name="home_tel"
                     value="<?/*= $valIn('home_tel')*/?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Email</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Email" name="email"
                     value="<?/*= $valIn('email')*/?>"></form>
    </div>
</div>-->

