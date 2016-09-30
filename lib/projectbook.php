<?php

namespace OCA\Owncollab_Contacts;


use OCA\DAV\CardDAV\Converter;
use OCA\Owncollab_Contacts\Db\Connect;
use OCA\DAV\CardDAV\CardDavBackend;
use OCA\DAV\Connector\Sabre\Principal;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property\Text;
use Sabre\VObject\Reader;

class ProjectBook
{
    /**
     * @var Connect
     */
    private $connect;

    /**
     * @var CardDavBackend
     */
    private $cardDavBackend;

    /**
     * @var \OCP\IURLGenerator
     */
    private $urlGenerator;

    /**
     * @var \OC\User\Manager
     */
    private $userManager;

    /**
     * @var \OC\Group\Manager
     */
    private $groupManager;

    /**
     * @var Principal
     */
    private $principal;

    /**
     * @var string
     */
    private $fakeUser = 'collab_user';

    /**
     * @var ConverterUser
     */
    private $converterUser;


    /**
     * ProjectBook constructor.
     */
    public function __construct()
    {
        $db = \OC::$server->getDatabaseConnection();
        $dispatcher = \OC::$server->getEventDispatcher();

        $this->connect = new Connect($db);
        $this->urlGenerator = \OC::$server->getURLGenerator();
        $this->userManager = \OC::$server->getUserManager();
        $this->groupManager = \OC::$server->getGroupManager();
        $this->principal = new Principal($this->userManager, $this->groupManager);
        $this->cardDavBackend = new CardDavBackend($db, $this->principal, $dispatcher);
        $this->converterUser = new ConverterUser();
    }


    public function getProjectBook()
    {
        $principal = 'principals/users/' . $this->fakeUser;
        return $this->cardDavBackend->getAddressBooksByUri($principal, 'project_contacts');
    }


    public function createProjectBook()
    {
        $principal = 'principals/users/' . $this->fakeUser;
        $book = $this->getProjectBook();

        if (empty($book)) {
            try {
                $this->cardDavBackend->createAddressBook($principal, 'project_contacts',
                    [
                        '{DAV:}displayname' => 'Project Contacts'
                    ]
                );
                $projectBook = $this->getProjectBook();
                $users = $this->connect->users()->getAllIds();
                if (!empty($projectBook['id']) && is_array($users)) {
                    foreach ($users as $user) {
                        if ($user['uid'] == $this->fakeUser)
                            continue;
                        $this->insertCard($projectBook['id'], $user['uid']);
                    }
                }

            } catch (\Exception $ex) {
                \OC::$server->getLogger()->logException($ex);
            }
        }
    }


    public function insertCard($addressBookId, $uid)
    {
        $user = $this->userManager->get($uid);
        $userId = $user->getUID();
        $cardId = md5($userId).".vcf";
        $vCard = $this->converterUser->createCardFromUser($user);
        $this->cardDavBackend->createCard($addressBookId, $cardId, $vCard->serialize());
    }


    public function shareProjectBookWith($uid)
    {
        $projectBook = $this->getProjectBook();
        if (!empty($projectBook['id']) && $uid != $this->fakeUser) {
            return $this->connect->addressbook()->shareProjectContact($projectBook['id'], $uid);
        }
    }

    public function updateProjectBook()
    {
        $projectBook = $this->getProjectBook();
        $users = $this->connect->users()->getAllIds();

        if (!empty($projectBook['id']) && is_array($users)) {

            // 0. not exist (contact) - insert      +
            // 1. not exist (user) - delete         -
            // 2. change - update/delete-insert     +
            // 3. no change - no action             +

            foreach ($users as $user) {
                if ($user['uid'] == $this->fakeUser)
                    continue;

                $this->updateCard($projectBook['id'], $user['uid']);
            }

        }
    }

    public function updateCard($addressBookId, $uid)
    {
        /**
         * @param \Sabre\VObject\Component\VCard $vCard
         * @param \OC\User\User $user
         */
        $user = $this->userManager->get($uid);
        $userId = $user->getUID();
        $cardId = md5($userId).".vcf";
        $card = $this->cardDavBackend->getCard($addressBookId, $cardId);
        if (!$card) {
            $this->insertCard($addressBookId, $uid);
        }
        else {
            $vCard = Reader::read($card['carddata']);
            $needsUpdate = $this->converterUser->updateCard($vCard, $user);

            //var_dump('Needs Update: ' . ($needsUpdate ? 'Yes':'No'));
            //$this->cardDavBackend->updateCard($addressBookId, $cardId, )

            if ($needsUpdate) {
                $this->cardDavBackend->deleteCard($addressBookId, $cardId);
                $this->insertCard($addressBookId, $uid);
            }
        }
    }


}