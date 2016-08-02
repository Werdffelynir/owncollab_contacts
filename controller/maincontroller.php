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
    private $connect;
    private $projectname = "Base project";
    private $mailDomain = null;

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

    }

    /**
     * CAUTION: the @Stuff turns off security checks; for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        return $this->showList();
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
     * @return TemplateResponse
     */
    public function showList()
    {
        //$r = $this->connect->users()->getResourcesOwncollab();
        //var_dump($usersProject);
        //var_dump($ruo);
        //exit;

        $resIds = $this->connect->users()->getResourcesOwncollabAllUsersOnly();
        $projectUsers = $this->connect->users()->getAllIn($resIds);

        $userContacts = $this->connect->users()->getUserContacts($this->userId);

        $data = [
            'menu' => 'begin',
            'content' => 'list',
            'projectUsers' => $projectUsers,
            'userContacts' => $userContacts,
        ];

        return new TemplateResponse($this->appName, 'main', $data);

    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getvcard()
    {
        $resIds = $this->connect->users()->getResourcesOwncollabAllUsersOnly();
        $projectUsers = $this->connect->users()->getAllIn($resIds);


        $resIds = array_values($resIds);
        $vCardData = '';

        for($i=0; $i<count($resIds);$i++) {

            $vcard = new vCard();

            $vcard->set('data', [
                'first_name' => $resIds[$i],
                'last_name' => $projectUsers[$resIds[$i]['last_name']],
                'display_name' => $projectUsers[$resIds[$i]['display_name']],
                'email1' => $projectUsers[$resIds[$i]['email']],
                'office_tel' => $projectUsers[$resIds[$i]['office_tel']],
                'home_tel' => $projectUsers[$resIds[$i]['office_tel']],
            ]);

            $vCardData .= $vcard->show();
        }
        
        header("Content-type: text/directory");
        header("Content-Disposition: attachment; filename=contacts.vcf");
        header("Pragma: public");
        echo $vCardData;
       die;

    }


}