<?php
/**
 * @var array $_
 */


$ftypes = isset($_['fieldsTypes']) ? $_['fieldsTypes'] : [];
$contact = isset($_['contact']) ? $_['contact'] : [];
$idBook = isset($_['bookId']) ? $_['bookId'] : false;
$idGroup = isset($_['id_group']) ? $_['id_group'] : '';
$idContact = !empty($contact['id_contact']) ? $contact['id_contact'] : '';



function addField ($key, $value, $label) {
    $html = '
        <div class="ads_field">
            <label for="find_%s">%s</label>
            <input id="find_%s" name="%s" type="text" value="%s">
        </div>
    ';
    ob_start();
    printf($html, $key, $label, $key, $key, $value);
    return ob_get_clean();
}

$addressbook_name = 'Addressbook';

?>


<style>

</style>
<div id="app-content-dox-close">&nbsp;</div>
<div class="tbl">
    
    <div class="tbl_cell valign_top ads_contact_left">
        <div class="ads_avatar"></div>
    </div>

    <div class="tbl_cell valign_top ads_contact_center">

        <input type="text" name="id_contact" hidden="hidden" value="<?php p($idContact)?>">

        <div class="ads_field">
            <label for="id_book">Addressbook</label>
            <input id="id_book" name="id_book" type="button" data-id="<?php p($idBook)?>" value=" ">
            <div id="id_book_list" style="display: none">
                <ul></ul>
            </div>
        </div>

        <div class="ads_field">
            <label for="id_group">Group</label>
            <input id="id_group" name="id_group" type="button" data-id="<?php p($idGroup)?>" value=" ">
            <div id="id_group_list" style="display: none">
                <ul></ul>
            </div>
        </div>


        <?php
        foreach($contact['fields'] as $key => $value):
            if(!empty($value)) {
                echo addField($key, $value, $ftypes[$key]);
                unset($ftypes[$key]);
            }
        endforeach;
        ?>


        <div id="ads_dynamic_fields"></div>

        <br>

        <div class="ads_field">
            <input id="add_field" type="button" value="Add field">
            <div id="add_fields_list" style="display: none">
                <ul>
                    <?php foreach($ftypes as $tkey => $tval): ?>
                        <li data-id="<?php p($tkey)?>"><?php p($tval)?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>


    <div class="tbl_cell valign_top ads_contact_right">
        <div id="ads_btn_delete" class="inline_btns">
            <label for="btn_delete">Delete</label><span class="btn_delete"></span>
        </div>
    </div>
    
</div>

<div class="text_right">
    <div id="ads_btn_save" class="inline_btns">
        <label for="btn_save">Save changes</label><span class="btn_save"></span>
    </div>
</div>