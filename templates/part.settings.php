<?php

/**
 * @var array $_
 */

$url = \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/apps/owncollab_contacts');
$contacts = !empty($_['contacts']) ? $_['contacts'] : [];

?>
<div id="app-settings">
	<div id="app-settings-header">
		<button class="settings-button" data-apps-slide-toggle="#app-settings-content">Addressbook</button>
	</div>
	<div id="app-settings-content" style="">

        <?php foreach($contacts as $id => $contact):?>
            <div class="oneline">
                <input id="contact_<?php p($id)?>" data-id="<?php p($id)?>" type="checkbox" checked>
                <label for="contact_<?php p($id)?>"> <span></span> <?php p($contact['book']['name'])?> </label>
            </div>
        <?php endforeach;?>

        <h4>&nbsp;</h4>

		<div class="download_button">
			<div id="export_vcard" class="icon-download">Export contacts</div>
		</div>

        <!--			<a href="--><?//=$url?><!--/getvcard" target="_blank" class="icon-download">Export contacts</a>-->
<!--		<h4><label for="webdavurl">Remote</label></h4>
		<input id="webdavurl" readonly="readonly" value="<?/*=$url*/?>/vcard" type="text">-->

	</div>
</div>