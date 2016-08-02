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
    public function getproject($data)
    {
        return new DataResponse($data);
    }


    public function getvcard()
    {
        $resIds = $this->connect->users()->getResourcesOwncollabAllUsersOnly();
//      $projectUsers = $this->connect->users()->getAllIn($ruo);

        // запрос выбрать необходимые данные по каждому ИД
        // в цикле сгенерировать vCard объекты

        $vCardData = '';

        foreach ($resIds as $as) {

            $vcard = new vCard();

            $vcard->set('data', [
                'first_name' 	=> 'Vasia',
                'last_name' 	=> 'Vasilev',
                'display_name' 	=> 'Vasia Vasilev',
                'email1' 		=> 'manager@admin.com',
                'office_tel'	=> '+0123456789',
                'home_tel' 		=> 'My Company',
            ]);

            $vCardData .= $vcard->show();


        }

        // $vCardData

        exit;
    }


    public function addcontacts($data)
    {
        $params = [
            'data' => $data,
            'error' => null,
            'error_info' => null,
            'result' => null,
        ];
        $availableKeys = ['first_name', 'last_name', 'office_tel', 'home_tel', 'email',];

        if(!empty($data['key']) && !empty($data['value']) && in_array($data['key'], $availableKeys)) {

            $key = $data['key'];
            $value = $data['value'];

            if($key == 'email') {
                $params['result'] = $this->connect->users()->insertOrUpdateUserEmail($this->userId, $value);
            } else {
                $params['result'] = $this->connect->users()->insertOrUpdateUserContact($this->userId, $key, $value);
            }
            if(!$params['result']) {
                $params['error'] = true;
                $params['error_info'] = 'Failed update/insert. key: '. $key .' value: '. $value;
            }

        }

        return new DataResponse($params);
    }


}