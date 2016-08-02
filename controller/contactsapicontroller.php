<?php

namespace OCA\Owncollab_Contacts\Controller;

use \OCP\AppFramework\ApiController;
use \OCP\IRequest;

class ContactsApiController extends ApiController {

    public function __construct($appName, IRequest $request) {


//    ['name' => 'contactsapi#preflighted_cors', 'url' => '/remote/{path}', 'verb' => 'GET', 'requirements' => ['path' => '.+']],
        parent::__construct(
            $appName,
            $request,
            'PUT, POST, GET, DELETE, PATCH, OPTIONS',
            'Authorization, Content-Type, Accept',
            1728000);
    }

}