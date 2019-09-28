<?php
/*
 * Frontend functions.
 *
 * This file is loaded only on the application's frontend.
 */

use Kyt\Web\Script;
use Kyt\Web\Style;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

Style::enqueue( 'theme-styles', PUBLIC_URI.'/res/css/theme-styles.css' );

/*
 * Enqueue frontend scripts
 */
Script::enqueue( 'jquery', '//code.jquery.com/jquery-3.4.1.min.js', false, [
    'integrity' => 'sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=',
    'crossorigin' => 'anonymous',
] );
Script::enqueue( 'test-js', PUBLIC_URI . '/res/js/test.js' );
Script::localizeFrontendScript( 'test-js', 'TestLocale', [
    'text' =>  'Hello World'
] );

