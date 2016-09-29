<?php

namespace OCA\Owncollab_Contacts\AppInfo;

    //ini_set('display_errors', 1);

use OCA\DAV\CardDAV\CardDavBackend;
use \OCA\Owncollab_Contacts\Helper;
use OCA\Owncollab_Contacts\Controller\ApiController;
use OCA\Owncollab_Contacts\Controller\MainController;
use OCA\Owncollab_Contacts\Db\Connect;
use OCA\DAV\Connector\Sabre\Principal;
use \OCP\AppFramework\App;
use \OCP\AppFramework\IAppContainer;
use \OCP\IContainer;
use \OC\AppFramework\DependencyInjection\DIContainer;

class Application extends App {

    public function __construct ( array $urlParams = [] ) {

        // Static saved the application name
        $appName = Helper::setAppName('owncollab_contacts');
        parent::__construct($appName, $urlParams);
        $container = $this->getContainer();

        /**
         * Core for application registers service
         */
        $container->registerService('UserId', function(IContainer $c) {
            /** @var \OC\Server $server */
            /** @var \OCP\IUser  $user */
            $server = $c->query('ServerContainer');
            $user = $server->getUserSession()->getUser();
            return ($user) ? $user->getUID() : '';
        });

        $container->registerService('isAdmin', function(DIContainer $c) {
            /** @var \OC\Server $server */
            /** @var \OCP\IUser  $user */
            $server = $c->query('ServerContainer');
            $user = $server->getUserSession()->getUser();
            if($user)
                return $c->getServer()->getGroupManager()->isAdmin($user->getUID());
            else
                return false;
        });

        $container->registerService('L10N', function (IAppContainer $c) use ($appName) {
            return $c->getServer()->getL10N($appName);
        });


        /**
         * Database Layer
         */
        $container->registerService('Connect', function(DIContainer $c) {
            return new Connect(
                \OC::$server->getDatabaseConnection()
            );
        });


        $container->registerService('CardDavBackend', function($c) {
            /** @var IAppContainer $c */
            $db = $c->getServer()->getDatabaseConnection();
            $dispatcher = $c->getServer()->getEventDispatcher();
            $principal = new Principal(
                $c->getServer()->getUserManager(),
                $c->getServer()->getGroupManager()
            );
            return new CardDavBackend($db, $principal, $dispatcher);
        });


        /**
         * Controllers
         */
        $container->registerService('ApiController', function(DIContainer $c) {

            return new ApiController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect')
            );
        });


        $container->registerService('MainController', function(DIContainer $c) {

            return new MainController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect'),
                $c->query('CardDavBackend')
            );
        });

    }




}
