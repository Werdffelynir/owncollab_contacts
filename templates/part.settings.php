<?php

/**
 * @var array $_
 */

$url = \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/apps/owncollab_contacts')

?>
<div id="app-settings">
	<div id="app-settings-header">
		<button class="settings-button" data-apps-slide-toggle="#app-settings-content">Addressbook</button>
	</div>
	<div id="app-settings-content" style="">

<!--        <h4>Addressbook</h4>-->
        <div class="oneline">
            <input id="private_contacts" name="show_contacts" type="checkbox">
            <label for="private_contacts"> <span></span>Contacts </label>
        </div>
        <div class="oneline">
            <input id="project_contacts" name="show_project_contacts" type="checkbox">
            <label for="project_contacts"> <span></span>Project Contacts </label>
        </div>

        <h4>&nbsp;</h4>

		<div class="download_button">
			<a href="<?=$url?>/getvcard" target="_blank" class="icon-download">Export contacts</a>
		</div>

		<h4><label for="webdavurl">Remote</label></h4>

		<input id="webdavurl" readonly="readonly" value="<?=$url?>/vcard" type="text">
		<br>
	</div>
</div>