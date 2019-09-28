<?php

namespace Kyt\Core;

use Kyt\Web\Script;
use Kyt\Web\Style;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class App
 * @package Kyt\Core
 *
 * Standard Singleton
 */
class App
{
    /**
     * Holds the list of all domains to prefetch
     * @var array
     */
    public $dnsPrefetchLinks = [];

    /**
     * Holds the reference to the instance of this class
     * @var null|\Kyt\Helpers\Logger
     * @see Logger::getInstance()
     */
    private static $_instance = null;

    /**
     * App constructor.
     */
    public function __construct()
    {
        add_action( 'app/frontend/head', [ $this, 'printDnsPrefetchLinks' ] );

        /*
         * Register Scripts & Styles events
         */
        Style::registerEvents();
        Script::registerEvents();
    }

    /**
     * Retrieve the reference to the instance of this class
     * @return App|null
     */
    public static function getInstance()
    {
        if ( !self::$_instance || !( self::$_instance instanceof self ) ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Add a domain to the internal list of domains to prefetch
     * @param string $domain
     * @return $this
     */
    public function addPrefetchDomain( $domain )
    {
        if ( !in_array( $domain, $this->dnsPrefetchLinks ) ) {
            array_push( $this->dnsPrefetchLinks, $domain );
        }
        return $this;
    }

    /**
     * Print the dns-prefetch link tags
     */
    public function printDnsPrefetchLinks()
    {
        if ( !empty( $this->dnsPrefetchLinks ) ) {
            foreach ( $this->dnsPrefetchLinks as $domain ) {
                echo '<link rel="dns-prefetch" href="//' . $domain . '" />';
            }
        }
    }
}
