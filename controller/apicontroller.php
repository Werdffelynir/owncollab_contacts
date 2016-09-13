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
    public function index()
    {
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

    public function getcontact($data)
    {
        $fieldsTypes = $this->connect->addresscontacts()->getFormFieldsTypes();
        $bookId = $data['book'];
        $contactId = $data['contact'];

        $contact = $this->connect->addresscontacts()->getOne($contactId);
        if($contact) {
            try {
                $contact['fields'] = json_decode($contact['fields'], true);
            } catch (\Exception $e) {}
        }

        $html = Helper::renderPartial($this->appName, 'tpl.contact', [
            'bookId' => $bookId,
            'contact' => $contact,
            'fieldsTypes' => $fieldsTypes
        ]);

        return new DataResponse($html);
    }


    public function getcontacttpl($data)
    {
        $fieldsTypes = $this->connect->addresscontacts()->getFormFieldsTypes();
        $contact = [
            'fields' => [
                'id_contact' => '',
                'display_name' => '&nbsp;',
                'email1' => '&nbsp;',
            ]
        ];
        $html = Helper::renderPartial($this->appName, 'tpl.contact', [
            'bookId' => false,
            'contact' => $contact,
            'fieldsTypes' => $fieldsTypes
        ]);

        return new DataResponse($html);
    }


    // Object { fields: "{"display_name":"Mr. Forest"}", id_book: "18", id_group: "35", id_contact: "", is_private: true }
    public function savecontact($data)
    {
        $requestData = [
            'uid' => $this->userId,
            //'data' => $data,
            'fields' => null,
            'result' => null,
            'error' => null,
            'error_info' => null,
        ];

        $is_private = (bool) $data['is_private'];
        $id_book = $data['id_book'];
        $id_group = $data['id_group'];
        $id_contact = $data['id_contact'];
        $id_rel_contact = $data['id_rel_contact'];
        $fieldsSource = $data['fields'];
        $fields = [];

        if(!empty($fieldsSource) && !empty($id_book) && !empty($id_group)) {

            try {
                $fieldsSource = json_decode($fieldsSource, true);
                $fields = $this->connect->addresscontacts()->defaultFieldsTypes($fieldsSource, true);
            }catch(\Exception $e){}

            if(empty($id_contact)) {
                // Insert new contact
                $this->connect->db->beginTransaction();

                $id = (int) $this->connect->addresscontacts()->create($this->userId, $fields, $is_private);
                $rel_id = $this->connect->addressRelContacts()->create($id_group, $id);
                $requestData['rel_id'] = $rel_id;
                $requestData['insert_id'] = $id;

                $this->connect->db->commit();
            }
            else if(is_numeric($id_contact)){

                // Update contact
                $this->connect->db->beginTransaction();

                $fields = json_encode($fields);
                $update_id = (int) $this->connect->addresscontacts()->updateContactFields($id_contact, $fields);

                $rel = $this->connect->addressRelContacts()->getOneById($id_rel_contact);
                if ($is_private && $rel['id_group'] != $id_group) {
                    $this->connect->addressRelContacts()->removeById($id_rel_contact);
                    $this->connect->addressRelContacts()->create($id_group, $id_contact);
                }

                $this->connect->db->commit();

                $requestData['update_id'] = $update_id;

            }
        }

        $requestData['fields'] = $fields;
        return new DataResponse($requestData);
    }



    public function deletecontact($data)
    {
        //name_group id_book
        $requestData = [
            'id_book' =>  $data['id_book'],
            'id_contact' => $data['id_contact'],
            'id_rel_contact' => $data['id_rel_contact'],
            'result' => null,
            'error' => null,
            'error_info' => null,
        ];

        $this->connect->db->beginTransaction();
        $result = $this->connect->addresscontacts()->removeById($requestData['id_contact']);
        if (!$result)
            $this->connect->addressRelContacts()->removeById($requestData['id_rel_contact']);
        else
            $requestData['result'] = $result;

        $this->connect->db->commit();

        return new DataResponse($requestData);
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return DataResponse
     */
    public function savegroup()
    {
        //name_group id_book
        $requestData = [
            'id_book' =>  Helper::post('id_book'),
            'name_group' => Helper::post('name_group'),
            'result' => null,
            'error' => null,
            'error_info' => null,
        ];

        if(!empty($requestData['id_book']) && !empty($requestData['name_group'])) {
            $result = $this->connect->addressgroups()->create($requestData['id_book'], $requestData['name_group'], true);
            $requestData['result'] = $result;
        }

        header('Location: /index.php/apps/owncollab_contacts');
        exit;
    }



/*
    public function addcontacts($data)
    {
        $params = [
            'data' => $data,
            'error' => null,
            'error_info' => null,
            'result' => null,
        ];

        $availableKeys = ['displayname', 'first_name', 'last_name', 'office_tel', 'home_tel', 'email',];

        if(!empty($data['key']) && !empty($data['value']) && in_array($data['key'], $availableKeys)) {

            if(isset($data['uid']) && $this->isAdmin)
                $userId = $data['uid'];
            else
                $userId = $this->userId;

            $key = $data['key'];
            $value = $data['value'];

            if($key == 'email') {

                $params['result'] = $this->connect->users()->insertOrUpdateUserEmail($userId, $value);

            } else if ($key == 'displayname') {

                $_parts = explode(' ', $value);

                if(count($_parts) > 1){
                    $firstName = array_shift($_parts);
                    $params['result'] = $this->connect->users()->insertOrUpdateUserContact($userId, 'first_name', $firstName);
                    $lastName = join(' ', $_parts);
                    $params['result'] = $this->connect->users()->insertOrUpdateUserContact($userId, 'last_name', $lastName);
                } else {
                    $params['result'] = $this->connect->users()->insertOrUpdateUserContact($userId, 'first_name', $value);
                }

            } else {
                $params['result'] = $this->connect->users()->insertOrUpdateUserContact($userId, $key, $value);
            }

            if(!$params['result']) {
                $params['error'] = true;
                $params['error_info'] = 'Failed update/insert. key: '. $key .' value: '. $value;
            }

        }

        return new DataResponse($params);
    }
*/

}