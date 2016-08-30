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
use OCA\Owncollab_Contacts\AddressBookHandler;
use OCA\Owncollab_Contacts\Db\Connect;
use OCA\Owncollab_Contacts\Helper;
use OCP\Files;
use OCP\IRequest;
use OCA\Owncollab_Contacts\vCard;
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
    /** @var AddressBookHandler  */
    private $addressBookHandler;

    /**
     * MainController constructor.
     * @param string $appName
     * @param IRequest $request
     * @param $userId
     * @param $isAdmin
     * @param $l10n
     * @param Connect $connect
     */
    public function __construct(
        $appName,
        IRequest $request,
        $userId,
        $isAdmin,
        $l10n,
        Connect $connect
    )
    {
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->addressBookHandler = new AddressBookHandler($this->connect);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        // $this->addressBookHandler->createProjectContacts();
        // $this->addressBookHandler->createPrivateContacts($this->userId, 'My Contact', ['My Home', 'My Work', 'My Business']);
        // exit;

        return $this->showList();
    }



    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function showList()
    {
        $contacts = [];
        $customBooks = $this->addressBookHandler->getAllCustomAddressBooks($this->userId);
        $projectContacts = $this->addressBookHandler->getProjectContacts();
        foreach($customBooks as $book) {
            $contacts[$book['id_book']] = $this->addressBookHandler->getContactsByAddressBook($book['id_book']);
        }

        $contacts['project_contacts'] = $projectContacts;
        $frontendData = [
            'userId' => $this->userId,
            'isAdmin' => $this->isAdmin,
            'contacts' => $contacts
        ];

        $data = [
            'menu' => 'begin',
            'content' => 'list',
            'userId' => $this->userId,
            'isAdmin' => $this->isAdmin,
            'contacts' => $contacts,
            'frontend_data' => json_encode($frontendData),
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return TemplateResponse
     */
    public function showContact()
    {
        $formFieldsTypes = $this->connect->addresscontacts()->getFormFieldsTypes();

        $data = [
            'menu' => 'begin',
            'content' => 'contact',
            'formFieldsTypes' => $formFieldsTypes,
            'isAdmin' => $this->isAdmin,
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }



































    /**
     * Blocked application and show error message
     * @param $error_message
     * @return TemplateResponse
     */
    public function pageError($error_message)
    {

        $data = [
            'menu' => '',
            'content' => 'error',
            'user_id' => $this->userId,
            'error_message' => $error_message,
        ];

        return new TemplateResponse($this->appName, 'main', $data);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getvcard()
    {
        header("Content-type: text/directory");
        header("Content-Disposition: attachment; filename=contacts.vcf");
        header("Pragma: public");
        exit($this->connect->users()->vCardGenerate());
    }

    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function publicgetvcard()
    {
        exit($this->connect->users()->vCardGenerate());
    }

}