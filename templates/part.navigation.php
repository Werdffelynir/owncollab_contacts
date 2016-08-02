<?php

/**
 * @var array $_
 */

$userContacts = !empty($_['userContacts']) && is_array($_['userContacts']) ? $_['userContacts'] : [];

$valIn = function ($key) use ($userContacts) {
    foreach ($userContacts as $cont)
        if($cont['configkey'] == $key) {
            return $cont['configvalue']; break;
        }
    return '';
};

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

