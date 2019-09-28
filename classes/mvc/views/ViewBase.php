<?php

use Kyt\MVC\AbstractView;

if ( ! defined( 'APP_DIR' ) ) {
	exit( 'No direct access please.' );
}


/**
 * Class ViewBase
 */
class ViewBase extends AbstractView
{
	/**
	 * Render a view
	 * @param string $viewFilePath The partial path to the view file
	 * @param string|bool $layout The name of the layout file to load
	 * @param array $data
	 */
	public function render( $viewFilePath, $layout = false, array $data = [] )
	{
		$filePath = PUBLIC_DIR . "/views/{$viewFilePath}.php";
		if ( ! is_file( $filePath ) ) {
			return;
		}

		extract( $data );
		ob_start();
		require( $filePath );
		//#! Global var that layouts can use to render the view content
		$content_for_layout = ob_get_contents();
		ob_end_clean();

		if ( $layout ) {
			$filePath = PUBLIC_DIR . "/views/layouts/{$layout}.php";
			if ( is_file( $filePath ) ) {
				require_once( $filePath );
			}
		}
	}
}
