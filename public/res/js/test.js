jQuery( function ($) {
    "use strict";

    var locale = ( typeof ( window.TestLocale ) !== 'undefined' ? window.TestLocale : null );
    if ( !locale ) {
        console.error( 'TestLocale not found' );
        return;
    }

    console.info( locale.text );
} );
