<?php

namespace Kyt\MVC;

if ( ! defined( 'APP_DIR' ) ) {
	exit( 'No direct access please.' );
}

/**
 * Class View
 * @package Kyt\MVC
 */
abstract class AbstractView
{
	/**
	 * Render a view
	 * @param string $viewFilePath The partial path to the view file
	 * @param string|false $layout The name of the layout file to load
	 * @param array $data
	 */
	abstract public function render( $viewFilePath, $layout = false, array $data = [] );

	/**
	 * AbstractView constructor.
	 */
	public function __construct(  )
	{
		//
	}

}
