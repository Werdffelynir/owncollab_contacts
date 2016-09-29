<?php

namespace OCA\Owncollab_Contacts;

use OCP\IImage;
use OCP\IUser;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property\Text;

class ConverterUser {

    /**
     * @param IUser $user
     * @return VCard
     */
    public function createCardFromUser(IUser $user) {

        $uid = $user->getUID();
        $displayName = $user->getDisplayName();
        $displayName = empty($displayName ) ? $uid : $displayName;
        $emailAddress = $user->getEMailAddress();
        $cloudId = $user->getCloudId();
        $image = $this->getAvatarImage($user);
        $groups = \OC::$server->getGroupManager()->getUserGroups($user);

        $vCard = new VCard();
        $vCard->add(new Text($vCard, 'UID', $uid));
        if (!empty($displayName)) {
            $vCard->add(new Text($vCard, 'FN', $displayName));
            $vCard->add(new Text($vCard, 'N', $this->splitFullName($displayName)));
        }
        if (!empty($emailAddress)) {
            $vCard->add(new Text($vCard, 'EMAIL', $emailAddress, ['TYPE' => 'OTHER']));
        }
        if (!empty($cloudId)) {
            $vCard->add(new Text($vCard, 'CLOUD', $cloudId));
        }
        if ($image) {
            $vCard->add('PHOTO', $image->data(), ['ENCODING' => 'b', 'TYPE' => $image->mimeType()]);
        }
        if ($groups) {
            foreach ($groups as $groupName=>$groupInfo) {
                $vCard->add(new Text($vCard, 'CATEGORIES', $groupName));
            }
        }

        $vCard->validate();

        return $vCard;
    }

    /**
     * @param VCard $vCard
     * @param IUser $user
     * @return bool
     */
    public function updateCard(VCard $vCard, IUser $user) {
        $uid = $user->getUID();
        $displayName = $user->getDisplayName();
        $displayName = empty($displayName ) ? $uid : $displayName;
        $emailAddress = $user->getEMailAddress();
        $cloudId = $user->getCloudId();
        $image = $this->getAvatarImage($user);
        $groups = \OC::$server->getGroupManager()->getUserGroups($user);

        $updated = false;
        if($this->propertyNeedsUpdate($vCard, 'FN', $displayName)) {
            $vCard->FN = new Text($vCard, 'FN', $displayName);
            unset($vCard->N);
            $vCard->add(new Text($vCard, 'N', $this->splitFullName($displayName)));
            $updated = true;
        }
        if($this->propertyNeedsUpdate($vCard, 'EMAIL', $emailAddress)) {
            $vCard->EMAIL = new Text($vCard, 'EMAIL', $emailAddress);
            $updated = true;
        }
        if($this->propertyNeedsUpdate($vCard, 'CLOUD', $cloudId)) {
            $vCard->CLOUD = new Text($vCard, 'CLOUD', $cloudId);
            $updated = true;
        }

        if($this->propertyNeedsUpdate($vCard, 'PHOTO', $image)) {
            $vCard->add('PHOTO', $image->data(), ['ENCODING' => 'b', 'TYPE' => $image->mimeType()]);
            $updated = true;
        }

        if (empty($emailAddress) && !is_null($vCard->EMAIL)) {
            unset($vCard->EMAIL);
            $updated = true;
        }
        if (empty($cloudId) && !is_null($vCard->CLOUD)) {
            unset($vCard->CLOUD);
            $updated = true;
        }
        if (empty($image) && !is_null($vCard->PHOTO)) {
            unset($vCard->PHOTO);
            $updated = true;
        }

        /**
         * Sabre\VObject\Property\Text $categories
         */
        $categoriesProperty = $vCard->CATEGORIES;

        if (method_exists($categoriesProperty, 'count')) {
            $categoriesCount = $categoriesProperty->count();
            $categories = [];
            for ($i = 0; $i < $categoriesCount; $i ++) {
                $categories[] = $categoriesProperty->getValue();
                $categoriesProperty->getIterator()->next();
                $categoriesProperty = $categoriesProperty->getIterator()->current();
            }

//            var_dump(array_keys($groups),$categories);
//            var_dump(array_diff(array_keys($groups), $categories));
//            exit;

            if (count(array_keys($groups)) != count($categories)
                ||
                !empty(array_diff(array_keys($groups), $categories)))
            {
                unset($vCard->CATEGORIES);
                $updated = true;
            }
        } else if ($groups) {
            $updated = true;
        }

        return $updated;
    }

    /**
     * @param VCard $vCard
     * @param string $name
     * @param string|IImage $newValue
     * @return bool
     */
    private function propertyNeedsUpdate(VCard $vCard, $name, $newValue) {
        if (is_null($newValue)) {
            return false;
        }
        $value = $vCard->__get($name);
        if (!is_null($value)) {
            $value = $value->getValue();
            $newValue = $newValue instanceof IImage ? $newValue->data() : $newValue;

            return $value !== $newValue;
        }
        return true;
    }

    /**
     * @param string $fullName
     * @return string[]
     */
    public function splitFullName($fullName) {
        // Very basic western style parsing. I'm not gonna implement
        // https://github.com/android/platform_packages_providers_contactsprovider/blob/master/src/com/android/providers/contacts/NameSplitter.java ;)

        $elements = explode(' ', $fullName);
        $result = ['', '', '', '', ''];
        if (count($elements) > 2) {
            $result[0] = implode(' ', array_slice($elements, count($elements)-1));
            $result[1] = $elements[0];
            $result[2] = implode(' ', array_slice($elements, 1, count($elements)-2));
        } elseif (count($elements) === 2) {
            $result[0] = $elements[1];
            $result[1] = $elements[0];
        } else {
            $result[0] = $elements[0];
        }

        return $result;
    }

    /**
     * @param IUser $user
     * @return null|IImage
     */
    private function getAvatarImage(IUser $user) {
        try {
            $image = $user->getAvatarImage(-1);
            return $image;
        } catch (\Exception $ex) {
            return null;
        }
    }

}
