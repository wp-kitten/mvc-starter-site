<?php if ( ! defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}
/*
 * [Admin] [Frontend]
 *
 * Global functions
 */

use Kyt\Helpers\Logger;
use Kyt\Helpers\Util;

/**@var Logger $logger */
global $logger;

/**
 * Log any requests longer than the maximum allowed length
 */
add_action( 'request/url-too-long', function ( $eventArg = null ) use ( $logger ) {
	/**@var Request|null $eventArg */
	if ( $eventArg && $logger) {
		$logger->write( 'Request url too long.', [
			'IP' => Util::getIP(),
			'REQUEST METHOD' => getenv( 'REQUEST_METHOD' ),
			'URL' => $eventArg->url,
		], LOG_LEVEL_WARN );
	}
} );
