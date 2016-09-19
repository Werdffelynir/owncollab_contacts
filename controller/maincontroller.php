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
        $projectContacts = $this->addressBookHandler->getProjectContacts();
        if(!$projectContacts) {
            $this->addressBookHandler->createProjectContacts();
        }
        else if ($projectContacts['book']['last_update'] <  time() - (60 * 1) ) {
            //exit('Time Update!');
            $this->addressBookHandler->updateProjectContacts($projectContacts);
        }

        if(!$this->addressBookHandler->getAllCustomAddressBooks($this->userId)) {
            $this->addressBookHandler->createPrivateContacts($this->userId, 'Contact', ['Home', 'Work', 'Business']);
        }

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
        $contacts = Helper::post('contacts', false);
        $contactsData = [];

        try {
            $contactsData = json_decode($contacts, true);
        } catch (\Exception $e) {}

        $fields = [];
        foreach ($contactsData as $contact) {
            $fields[] = $contact['fields'];
        }

        $data = $this->vCardGenerate($fields);

        header("Content-type: text/directory");
        header("Content-Disposition: attachment; filename=contacts.vcf");
        header("Pragma: public");
        exit($data);
    }

    public function vCardGenerate($fields)
    {
        $result = '';
        foreach($fields as $field) {

            $vcard = new vCard();
            $first_name = '';
            $last_name = '';

            if($display_name_array = explode(' ', $field['display_name']) AND $display_name_array >= 2) {
                $first_name = array_shift($display_name_array);
                $last_name = join(' ', $display_name_array);
            }else{
                $first_name = $field['display_name'];
            }

            $vcard->set('data', [
                'display_name' => $field['display_name'],
                'first_name' => $first_name,
                'last_name' => $last_name,
                'department' => $field['department'],
                'company' => $field['company'],
                'work_country' => $field['work_country'],
                'work_city' => $field['work_city'],
                'work_address' => $field['work_address'],
                'office_tel' => $field['office_tel'],
                'home_tel' => $field['home_tel'],
                'home_address' => $field['home_address'],
                'birthday' => $field['birthday'],
                'email1' => $field['email1'],
                'email2' => $field['email2'],
                'note' => $field['note'],
                'url' => $field['url'],
            ]);

            $result .= $vcard->show();
        }

        return $result;
    }

    /**
     * @PublicPage
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function publicgetvcard()
    {
        //exit($this->connect->users()->vCardGenerate());
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
        //$groupManager->search(''))
        var_dump($userManager->search('vasia'));
        exit;
    }

}