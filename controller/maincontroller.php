<?php
/**
 * ownCloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Your Name <mail@example.com>
 * @copyright Your Name 2016
 */

namespace OCA\Owncollab_Contacts\Controller;

use OC\Files\Filesystem;
use OCA\DAV\CardDAV\AddressBookImpl;
use OCA\DAV\CardDAV\CardDavBackend;
//use OCA\Owncollab_Contacts\AddressBookHandler;
use OCA\Owncollab_Contacts\Db\Connect;
use OCA\Owncollab_Contacts\Helper;
use OCA\Owncollab_Contacts\ProjectBook;
use OCP\Files;
use OCP\IRequest;
//use OCA\Owncollab_Contacts\vCard;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Share;

class MainController extends Controller
{

    /** @var string current auth user id */
    private $userId;
    private $l10n;
    private $isAdmin;
    /** @var Connect  */
    private $connect;
    /** @var CardDavBackend  */
    private $cardDavBackend;

    /**
     * MainController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param $userId
     * @param $isAdmin
     * @param $l10n
     * @param Connect $connect
     * @param CardDavBackend $cardDavBackend
     */
    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        $isAdmin,
        $l10n,
        Connect $connect,
        CardDavBackend $cardDavBackend
    )
    {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->cardDavBackend = $cardDavBackend;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        // Create PjCn if not exist
        // $result = $this->connect->addressbook()->shareProjectContact($this->userId);
        // var_dump($this->cardDavBackend->createCard());
        // IURLGenerator $urlGenerator

        /*
        $addressBooks = $this->cardDavBackend->getAddressBooksForUser("principals/users/{$this->userId}");
        $addressBookInfo = $addressBooks[0];
        $addressBook = new \OCA\DAV\CardDAV\AddressBook($this->cardDavBackend, $addressBookInfo);
        $urlGenerator = \OC::$server->getURLGenerator();

        $addressBookImpl = new AddressBookImpl(
            $addressBook,
            $addressBookInfo,
            $this->cardDavBackend,
            $urlGenerator
        );

        $properties = [
            'URI' => '33684496-3fe7-443e-a547-b2695a18caf4.vcf',
            'FN' => 'Change Name',
            'EMAIL' => 'EMAIL@EMAIL.EMAIL',
        ];

        var_dump($addressBookImpl->createOrUpdate($properties));

        array (size=23)
          0 =>
            array (size=3)
              'uid' => string 'aaam3' (length=5)
              'displayname' => null
              'email' => null

        */

//        $all = $this->connect->users()->getAllWithEmail();
//        var_dump($all);


        $projectBook = new ProjectBook();
        $projectBookInfo = $projectBook->getProjectBook();


        $projectBook->updateCard($projectBookInfo['id'], 'aam2');


        //var_dump($pb->getProjectBook());
//        $users = $this->connect->users()->getAllIds();
//        foreach ($users as $user) {
//            if ($user['uid'] == 'collab_user') continue;
//            //var_dump($pbInfo['id'], $user['uid']);
//            //$pb->insertCard($pbInfo['id'], $user['uid']);
//        }


        //

        exit;
    }



    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function test()
    {

        $userManager  = \OC::$server->getUserManager();
        $userSession  = \OC::$server->getUserSession();
        $groupManager = \OC::$server->getGroupManager();

        //$userManager->userExists()
        //$userManager->search('admin')
        //$userManager->search('admin')['admin']->getDisplayName()
        //var_dump($userSession->getUser()->getAvatarImage(100));
        //var_dump($userSession->login());
        //$groupManager->search(''));

        var_dump($userManager->search('collab_user'));
        exit;
    }

}