<?php

/**
 * @var array $_
 */

$userContacts = !empty($_['userContacts']) && is_array($_['userContacts']) ? $_['userContacts'] : [];

/*Table: oc_preferences
Columns:
userid	varchar(64) PK
appid	varchar(32) PK
configkey	varchar(64) PK
configvalue	longtext*/
// if(!empty($userContacts[''])) echo $userContacts['']

$valIn = function ($key) use ($userContacts) {
    foreach ($userContacts as $cont)
        if($cont['configkey'] == $key) {
            return $cont['configvalue']; break;
        }
    return '';
};

//var_dump($valIn('first_name'));
//var_dump($valIn('last_name'));
//var_dump($valIn('office_tel'));
//var_dump($valIn('home_tel'));
?>

<div class="vcontact">
    <div class="add_vcontact ico_add">First Name</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="First Name" name="first_name"
                     value="<?= $valIn('first_name')?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Last Name</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Last Name" name="last_name"
                     value="<?= $valIn('last_name')?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Office Telephone</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Telephone" name="office_tel"
                     value="<?= $valIn('office_tel')?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Home Telephone</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Telephone" name="home_tel"
                     value="<?= $valIn('home_tel')?>"></form>
    </div>
</div>


<div class="vcontact">
    <div class="add_vcontact ico_add">Email</div>
    <div class="vcontact_item">
        <form><input type="text" placeholder="Email" name="email"
                     value="<?= $valIn('email')?>"></form>
    </div>
</div>








