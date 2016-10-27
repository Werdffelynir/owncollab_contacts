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


use OCA\Owncollab_Contacts\Helper;
use OCA\Owncollab_Contacts\ProjectBook;
use OCA\Owncollab_Talks\Configurator;

if (\OC::$server->getUserManager()->search('collab_user')) {

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

} else {
    $configurator = new Configurator();
    $user = $configurator->get('collab_user');
    $userPassword = $configurator->get('collab_user_password');

    # create user if not exist
    if (!\OC_User::userExists($user)) {
        $userManager = \OC::$server->getUserManager();
        $userManager->createUser($user, $userPassword);
        $user = new \OC\User\User($user, null);
        $group =\OC::$server->getGroupManager()->get('admin');
        $group->addUser($user);
    }
}