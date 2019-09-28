<?php

use Kyt\Helpers\Logger;
use Kyt\Helpers\Util;
use Kyt\MVC\AbstractController;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Request
 */
class Request
{
    public $url = '';
    /**
     * @var null|AbstractController
     */
    public $controller = null;
    public $controllerName = 'null';
    public $action = '';
    public $params = [];
    public $isAdmin = false;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->url = getenv( "REQUEST_URI" );

        //#! Refuse parsing incredibly long urls
        if ( strlen( $this->url ) >= 2048 ) {
            $controllerName = Util::sanitizeControllerName( MVC_DEFAULT_CONTROLLER );
            $actionName = 'not_found';
            $controllerFilePath = MVC_DIR_PATH . "/controllers/{$controllerName}.php";
            require_once( $controllerFilePath );
            $this->controller = new $controllerName;
            $this->action = $actionName;
            do_action( 'request/url-too-long', $this );
        }
        else {
            $this->__parseUrl();
        }
    }

    /**
     * Parse url and populate class fields
     */
    private function __parseUrl()
    {
        $url = trim( $this->url );
        $url = str_ireplace( MVC_BASE_URL, '', $url );

        $controllerName = Util::sanitizeControllerName( MVC_DEFAULT_CONTROLLER );
        $actionName = Util::sanitizeActionName( MVC_DEFAULT_ACTION );
        $args = [];

        /**@var Logger $logger */
        global $logger;

        if ( !empty( $url ) ) {
            $parts = explode( '/', $url );
            $parts = array_map( 'strtolower', $parts );

            //#! Parse url
            if ( !empty( $parts ) ) {
                //#! Check to see if this is a request to the admin area
                if ( $parts[ 0 ] == MVC_ADMIN_DIR_NAME ) {
//  /admin
//  /admin/action
//  /admin/action/param
                    $this->isAdmin = true;
                    //#! check to see if the next param is a controller or an action
                    if ( isset( $parts[ 1 ] ) ) {
                        if ( isset( $parts[ 2 ] ) ) {
                            // parts[1] => must be a controller
                            if ( $this->__isValidController( $parts[ 1 ], MVC_ADMIN_DIR_NAME ) ) {
                                $controllerName = Util::sanitizeControllerName( $parts[ 1 ] );
                                $actionName = Util::sanitizeActionName( $parts[ 2 ] );
                            }
                        }
                        else {
                            // parts[0] => must be a controller
                            if ( $this->__isValidController( $parts[ 0 ], MVC_ADMIN_DIR_NAME ) ) {
                                $controllerName = Util::sanitizeControllerName( $parts[ 0 ] );
                                $actionName = Util::sanitizeActionName( $parts[ 1 ] );
                            }
                        }
                    }
                    //#! /admin
                    else {
                        if ( $this->__isValidController( $parts[ 0 ], MVC_ADMIN_DIR_NAME ) ) {
                            $controllerName = Util::sanitizeControllerName( $parts[ 0 ] );
                        }
                    }
                }
                else {
//  action
//  action/param/param/param
//  controller/action
//  controller/action/param
                    //#! Check to see how many parts we have
                    $partsCount = count( $parts );
                    // if 1 - controller + default action
                    if ( 1 == $partsCount ) {
                        if ( $this->__isValidController( $parts[ 0 ] ) ) {
                            $controllerName = Util::sanitizeControllerName( $parts[ 0 ] );
                        }
                        else {
                            $actionName = Util::sanitizeActionName( $parts[ 0 ] );
                        }
                    }
                    // if 2 - controller + action || [default controller] + action + param
                    elseif ( 2 == $partsCount ) {
                        if ( $this->__isValidController( $parts[ 0 ] ) ) {
                            $controllerName = Util::sanitizeControllerName( $parts[ 0 ] );
                            $actionName = Util::sanitizeActionName( $parts[ 1 ] );
                        }
                        else {
                            $actionName = Util::sanitizeActionName( $parts[ 0 ] );
                            $args = $parts[ 1 ];
                        }
                    }
                    // if >=3 - controller + action = args || [default controller] + action + args
                    elseif ( $partsCount >= 3 ) {
                        if ( $this->__isValidController( $parts[ 0 ] ) ) {
                            $controllerName = Util::sanitizeControllerName( $parts[ 0 ] );
                            $actionName = Util::sanitizeActionName( $parts[ 1 ] );
                            unset( $parts[ 0 ], $parts[ 1 ] );
                            $args = $parts;
                        }
                        else {
                            $actionName = Util::sanitizeActionName( $parts[ 0 ] );
                            unset( $parts[ 0 ] );
                            $args = $parts;
                        }
                    }
                }
            }
        }

        if ( $this->isAdmin ) {
            $adminDir = trailingslashit( MVC_ADMIN_DIR_NAME );
            $controllerFilePath = MVC_DIR_PATH . "/controllers/{$adminDir}{$controllerName}.php";
        }
        else {
            $controllerFilePath = MVC_DIR_PATH . "/controllers/{$controllerName}.php";
        }

        //#! Instantiate the controller
        require_once( $controllerFilePath );
        $this->controller = new $controllerName;

        if ( $actionName != MVC_DEFAULT_ACTION ) {
            if ( !$this->__isCallableAction( $this->controller, $actionName ) ) {
                $actionName = 'not_found';
            }
        }

        $this->controllerName = $controllerName;
        $this->action = $actionName;
        $this->params = $args;
    }

    /**
     * Check to see if the specified $name is a controller
     * @param string $name
     * @param string $subDir
     * @return bool
     */
    private function __isValidController( $name, $subDir = '' )
    {
        $controllerName = Util::sanitizeControllerName( $name );
        if ( !empty( $subDir ) ) {
            $subDir = trailingslashit( $subDir );
        }
        $filePath = MVC_DIR_PATH . "/controllers/{$subDir}{$controllerName}.php";
        return is_file( $filePath );
    }

    /**
     * Check to see if the specified $actionName is a valid action on the specified controller
     * @param Kyt\MVC\AbstractController $controllerInstance
     * @param $actionName
     * @return bool
     */
    private function __isCallableAction( $controllerInstance, $actionName )
    {
        return is_callable( [ $controllerInstance, $actionName ] );
    }
}
