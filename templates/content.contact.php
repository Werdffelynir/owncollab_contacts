<?php
/**
 * @var array $_
 */


$formFieldsTypes = $_['formFieldsTypes'];

$display_name = array_shift($formFieldsTypes);
$department = array_shift($formFieldsTypes);
$addressbook_name = 'Addressbook';

?>

<style>

</style>
<div class="tbl">
    
    <div class="tbl_cell valign_top ads_contact_left">

        <div class="ads_avatar">
<!--            <img src="/apps/owncollab_contacts/img/drafts.png" alt="">-->
        </div>
        ...
    </div>





    <div class="tbl_cell valign_top ads_contact_center">

        <div class="ads_field">
            <label for="display_name">Name</label>
            <input id="display_name" type="text" value="<?php p($display_name)?>">
        </div>

        <div class="ads_field">
            <label for="department">Group</label>
            <input id="department" type="text" value="<?php p($department)?>">
        </div>

        <div class="ads_field">
            <label for="addressbook_name">Addressbook</label>
            <input id="addressbook_name" type="text" value="<?php p($addressbook_name)?>">
        </div>

        <div id="ads_dynamic_fields">


        </div>

        <br>
        <div class="ads_field">
            <input id="add_field" type="button" value="Add field">
            <div id="add_fields_list" style="display: none">
                <ul>
                    <?php foreach($formFieldsTypes as $tkey => $tval): ?>
                        <li data-id="<?php p($tkey)?>"><?php p($tval)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>




    <div class="tbl_cell ads_contact_right">
        <div id="ads_btn_delete" class="inline_btns">
            <label for="btn_delete">Delete</label> <span id="btn_delete"></span>
        </div>
        <div id="ads_btn_save" class="inline_btns">
            <label for="btn_save">Save changes</label> <span id="btn_save"></span>
        </div>
    </div>
    
</div>
