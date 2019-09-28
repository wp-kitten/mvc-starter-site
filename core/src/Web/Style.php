<?php

namespace Kyt\Web;

use Kyt\Core\App;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Style
 * @package Web
 */
class Style
{
    /**
     * Holds the list of all enqueued styles
     * @var array
     */
    private static $_styles = [
        'frontend' => [],
        'backend' => [],
    ];

    /**
     * Register the events
     */
    public static function registerEvents()
    {
        add_action( 'app/frontend/head', [ __CLASS__, 'printStyles' ] );
    }

    /**
     * Enqueue a stylesheet in the frontend area
     * @param string $id
     * @param string $url
     * @param array $atts
     */
    public static function enqueue( $id, $url, array $atts = [] )
    {
        self::__enqueue( 'frontend', $id, $url, $atts );
    }

    /**
     * Enqueue a stylesheet in the administration area
     * @param string $id
     * @param string $url
     * @param array $atts
     */
    public static function adminEnqueue( $id, $url, array $atts = [] )
    {
        self::__enqueue( 'backend', $id, $url, $atts );
    }

    /**
     * Render frontend || backend styles
     */
    public static function printStyles()
    {
        global $request;
        if ( $request->isAdmin ) {
            self::__printStyles( 'backend' );
        }
        else {
            self::__printStyles( 'frontend' );
        }
    }

    /**
     * utility method to enqueue styles
     * @param string $where The location for the enqueued stylesheet: frontend || backend
     * @param string $id
     * @param string $url
     * @param array $atts
     */
    private static function __enqueue( $where, $id, $url, array $atts = [] )
    {
        $where = ( 'frontend' == $where ? 'frontend' : 'backend' );
        self::$_styles[ $where ][ $id ] = [
            'url' => $url,
            'atts' => $atts,
        ];

        $host = parse_url( $url, PHP_URL_HOST );
        if ( $host && false === stripos( SITE_URL, $host ) ) {
            App::getInstance()->addPrefetchDomain( $host );
        }
    }

    /**
     * Utility method to print styles based on the section provided
     * @param string $section The section to process" frontend || backend
     */
    private static function __printStyles( $section = 'frontend' )
    {
        if ( !empty( self::$_styles[ $section ] ) ) {
            foreach ( self::$_styles[ $section ] as $id => $info ) {
                $id = wp_kses( $id, [] );
                $atts = $info[ 'atts' ];
                $attrs = [];
                if ( !isset( $atts[ 'rel' ] ) ) {
                    $atts[ 'rel' ] = 'stylesheet';
                }
                if ( !isset( $atts[ 'type' ] ) ) {
                    $atts[ 'type' ] = 'text/css';
                }
                $atts[ 'id' ] = $id;
                $atts[ 'href' ] = $info[ 'url' ];
                foreach ( $atts as $k => $v ) {
                    $k = wp_kses( $k, [] );
                    $v = wp_kses( $v, [] );
                    $attrs[] = $k . '="' . $v . '"';
                }
                echo '<link ' . implode( ' ', $attrs ) . '/>';
            }
        }
    }
}
