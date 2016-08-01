<?php

namespace OCA\Owncollab_Contacts\Controller;

use OC\Files\Filesystem;
use OCA\Owncollab_Contacts\Helper;
use OCA\Owncollab_Contacts\Db\Connect;
use OCA\Owncollab_Contacts\vCard;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Files;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\Template;

class ApiController extends Controller {

    private $userId;
    private $isAdmin;
    private $l10n;
    private $connect;
    /** @var \OCP\IURLGenerator */
    private $urlGenerator;


    /**
     * ApiController constructor.
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
    ){
        parent::__construct($appName, $request);
        $this->userId = $userId;
        $this->isAdmin = $isAdmin;
        $this->l10n = $l10n;
        $this->connect = $connect;
        $this->urlGenerator = \OC::$server->getURLGenerator();

    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {

        $key = Helper::post('key');
        $data = Helper::post('data',false);
        $pid = Helper::post('pid');
        $uid = Helper::post('uid');

        if(method_exists($this, $key)) {
            return $this->$key($data);
        } else
            return new DataResponse(['error'=>'Api key not exist']);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $data
     * @return DataResponse
     */
    public function getproject($data) {
        return new DataResponse($data);
    }

    public function getvcard() {

        $ruo = $this->connect->users()->getResourcesOwncollabAllUsersOnly();
        $projectUsers = $this->connect->users()->getAllIn($ruo);

        $vcard = new vCard();

        $vcard->set('data', [
            'display_name' 	=> 'Vasia Vasilev',
            'first_name' 	=> 'Vasia',
            'last_name' 	=> 'Vasilev',
            'role' 			=> 'Manager',
            'email1' 		=> 'manager@admin.com',
            'office_tel'	=> '+0123456789',
            'company' 		=> 'My Company',
        ]);

        $vcard->download();
        exit;
    }
}