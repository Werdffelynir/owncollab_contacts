<?php
/**
 * @var array $_
 */


$ftypes = isset($_['fieldsTypes']) ? $_['fieldsTypes'] : [];
$contact = isset($_['contact']) ? $_['contact'] : [];
$bookId = isset($_['bookId']) ? $_['bookId'] : false;
$params = isset($_['params']) ? $_['params'] : [];

//var_dump($contact);
//$display_name = array_shift($fieldsTypes);
//$department = array_shift($fieldsTypes);


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

        <div class="ads_avatar">
<!--            <img src="/apps/owncollab_contacts/img/drafts.png" alt="">-->
        </div>
        ...
    </div>





    <div class="tbl_cell valign_top ads_contact_center">

        <?php if($bookId):?>
            <input type="text" name="book" hidden="hidden" value="<?php p($bookId)?>">
        <?php endif; ?>

        <?php if(!empty($contact['id_contact'])):?>
            <input type="text" name="id" hidden="hidden" value="<?php p($contact['id_contact'])?>">
        <?php endif; ?>

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
            <label for="btn_delete">Delete</label><span id="btn_delete"></span>
        </div>
    </div>
    
</div>

<div class="text_right">
    <div id="ads_btn_save" class="inline_btns">
        <label for="btn_save">Save changes</label><span id="btn_save"></span>
    </div>
</div>