<?php
/**
 * ownCloud - owncollab_contacts
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Werdffelynir <mail@example.com>
 * @copyright Werdffelynir 2016
 */

namespace OCA\Owncollab_Contacts\AppInfo;

use OCA\Owncollab_Contacts\Db\Connect;
use OCA\Owncollab_Contacts\Helper;
use OCA\Owncollab_Contacts\ProjectBook;
use OCP\AppFramework\App;
use OCP\Util;


//$appName = 'owncollab_contacts';
//$app = new App($appName);
//$container = $app->getContainer();


if (\OC::$server->getUserManager()->search('collab_user')) {

    /**
     * Navigation menu settings

    $container->query('OCP\INavigationManager')->add(function () use ($container, $appName) {
        $urlGenerator = $container->query('OCP\IURLGenerator');
        $l10n = \OC::$server->getL10N($appName);

        return [
            'id' => $appName,
            'order' => 10,
            'href' => $urlGenerator->linkToRoute($appName.'.main.index'),
            'icon' => $urlGenerator->imagePath($appName, 'app.svg'),
            'name' => $l10n->t('Contacts')
        ];
    });
     */

    /**
     * Loading translations
     * The string has to match the app's folder name
     */
//    Util::addTranslations($appName);


    /**
     * Common styles and scripts
     */
//    if(Helper::isAppPage($appName)) {
//        Util::addStyle($appName, 'common');
//        Util::addScript($appName, 'libs/ns.application');
//        Util::addScript($appName, 'application/init');
//    }


    /**
     * A listen the events "create new users" and "create new group"

    function initTriggers(){
        static $abh = null;

        if($abh === null) {
            Helper::appLoger('initTriggers');
            $connect = new Connect(Helper::getConnection());
        }
    }


    if( Helper::isAppSettingsUsers()) {
        initTriggers();
    } */



    if( Helper::isApp('contacts')) {

        $user = \OC::$server->getUserSession()->getUser();
        $uid = ($user) ? $user->getUID() : false;

        if ($uid) {
            $projectBook = new ProjectBook();
            $projectBookInfo = $projectBook->getProjectBook();

            if (!$projectBookInfo) {
                $projectBook->createProjectBook();
            }

            $isShared = $projectBook->shareProjectBookWith($uid);

            if ($projectBookInfo && !$isShared) {
                $projectBook->updateProjectBook();
            }

        }

    }


}




