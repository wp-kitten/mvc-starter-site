<?php

namespace Kyt\Helpers;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Util
 * @package Kyt
 *
 * Utility class
 */
class Util
{
    private static $localhostIps = array(
        '127.0.0.1',
        '::1'
    );

    /**
     * Check to see whether or not we're running on localhost
     * @return bool
     */
    public static function isLocalhost()
    {
        return in_array( getenv( 'REMOTE_ADDR' ), self::$localhostIps );
    }

    /**
     * Sanitizes the controller name
     * @param string $controllerName
     * @return string
     */
    public static function sanitizeControllerName( $controllerName )
    {
        $controllerName = str_replace( 'Controller', '', $controllerName );
        return ucwords( wp_kses( sanitize_file_name( $controllerName ), [] ) ) . 'Controller';
    }

    /**
     * Sanitizes the action name
     * @param string $actionName
     * @return string
     */
    public static function sanitizeActionName( $actionName )
    {
        return wp_kses( strtolower( $actionName ), [] );
    }

    /**
     * Get the user IP address
     * @return string
     */
    public static function getIP()
    {
        $ip = '0.0.0.0';
        if ( !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
            //#! from share internet
            $ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
        }
        elseif ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
            //#! pass from proxy
            $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        }
        elseif ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
            $ip = $_SERVER[ 'REMOTE_ADDR' ];
        }
        return $ip;
    }
}
