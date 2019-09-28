<?php

namespace Kyt\Web;

use Kyt\Core\App;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Script
 * @package Web
 */
class Script
{
    /**
     * Holds the list of all enqueued scripts
     * @var array
     */
    private static $_scripts = [
        'frontend' => [
            'head' => [],
            'footer' => [],
        ],
        'backend' => [
            'head' => [],
            'footer' => [],
        ],
    ];
    /**
     * Holds the list of all localized scripts
     * @var array
     */
    private static $_localized = [
        'frontend' => [
            'head' => [],
            'footer' => [],
        ],
        'backend' => [
            'head' => [],
            'footer' => [],
        ],
    ];

    /**
     * Register the events
     */
    public static function registerEvents()
    {
        add_action( 'app/frontend/head', [ __CLASS__, 'printScripts' ] );
        add_action( 'app/frontend/footer', [ __CLASS__, 'printScripts' ] );
    }

    /**
     * Enqueue a script in the frontend area
     * @param string $id
     * @param string $url
     * @param bool|false $inFooter
     * @param array $atts
     */
    public static function enqueue( $id, $url, $inFooter = false, array $atts = [] )
    {
        self::__enqueue( 'frontend', $id, $url, $inFooter, $atts );
    }

    /**
     * Enqueue a script in the administration area
     * @param string $id
     * @param string $url
     * @param bool|false $inFooter
     * @param array $atts
     */
    public static function adminEnqueue( $id, $url, $inFooter = false, array $atts = [] )
    {
        self::__enqueue( 'backend', $id, $url, $inFooter, $atts );
    }

    /**
     * Localize a frontend script
     * @param string $id
     * @param string $localeID
     * @param array $data
     */
    public static function localizeFrontendScript( $id, $localeID, $data = [] )
    {
        self::$_localized[ 'frontend' ][ $id ][] = [
            $localeID => $data
        ];
    }

    /**
     * Localize a backend script
     * @param string $id
     * @param string $localeID
     * @param array $data
     */
    public static function localizeBackendScript( $id, $localeID, $data = [] )
    {
        self::$_localized[ 'backend' ][ $id ][] = [
            $localeID => $data
        ];
    }

    /**
     * Render frontend || backend scripts
     * @param null|string $location The location this callback is triggered from: head || footer
     */
    public static function printScripts( $location = null )
    {
        global $request;
        if ( is_null( $location ) ) {
            $location = 'head';
        }
        if ( $request->isAdmin ) {
            self::__printScripts( 'backend', $location );
        }
        else {
            self::__printScripts( 'frontend', $location );
        }
    }

    /**
     * utility method to enqueue scripts
     * @param string $where The location for the enqueued script: frontend || backend
     * @param string $id
     * @param string $url
     * @param bool $inFooter
     * @param array $atts
     */
    private static function __enqueue( $where, $id, $url, $inFooter = false, array $atts = [] )
    {
        $where = ( 'frontend' == $where ? 'frontend' : 'backend' );
        $section = ( $inFooter ? 'footer' : 'head' );
        self::$_scripts[ $where ][ $section ][ $id ] = [
            'url' => $url,
            'atts' => $atts,
        ];

        $host = parse_url( $url, PHP_URL_HOST );
        if ( $host && false === stripos( SITE_URL, $host ) ) {
            App::getInstance()->addPrefetchDomain( $host );
        }
    }

    /**
     * Utility method to print scripts based on the section provided
     * @param string $section The section to process" frontend || backend
     * @param string $location The location this callback is triggered from: head || footer
     */
    private static function __printScripts( $section = 'frontend', $location = 'head' )
    {
        if ( !empty( self::$_scripts[ $section ][ $location ] ) ) {
            foreach ( self::$_scripts[ $section ][ $location ] as $id => $info ) {
                //#! Print the localized script first
                if ( isset( self::$_localized[ $section ][ $id ] ) ) {
                    $scriptData = [];
                    foreach ( self::$_localized[ $section ][ $id ] as $entries ) {
                        foreach ( $entries as $localeID => $data ) {
                            if ( !isset( $scriptData[ $localeID ] ) ) {
                                $scriptData[ $localeID ] = [];
                            }
                            $scriptData[ $localeID ] = array_merge( $scriptData[ $localeID ], $data );
                        }
                    }
                    if ( !empty( $scriptData ) ) {
                        echo '<script>';
                        foreach ( $scriptData as $localeID => $data ) {
                            echo $localeID . '=' . json_encode( $data ) . ';';
                        }
                        echo '</script>';
                    }
                }

                $id = wp_kses( $id, [] );
                $atts = $info[ 'atts' ];
                $attrs = [];
                $atts[ 'id' ] = $id;
                $atts[ 'src' ] = $info[ 'url' ];
                foreach ( $atts as $k => $v ) {
                    $k = wp_kses( $k, [] );
                    $v = wp_kses( $v, [] );
                    $attrs[] = $k . '="' . $v . '"';
                }
                echo '<script ' . implode( ' ', $attrs ) . '></script>';
            }
        }
    }
}
