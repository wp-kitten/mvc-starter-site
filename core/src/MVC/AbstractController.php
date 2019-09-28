<?php

namespace Kyt\MVC;

if ( ! defined( 'APP_DIR' ) ) {
	exit( 'No direct access please.' );
}

/**
 * Class AbstractController
 * @package Kyt\MVC
 */
abstract class AbstractController
{
	/**
	 * The variables to pass to the view
	 * @var array
	 */
	protected $vars = [];

	/**
	 * Holds the reference to the instantiated view class
	 * @var null|AbstractView
	 * @see AbstractController::__construct()
	 */
	protected $view = null;

	/**
	 * Holds the reference to the instantiated model class
	 * @var null|AbstractModel
	 * @see AbstractController::__construct()
	 */
	protected $model = null;

	/**
	 * AbstractController constructor.
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Setup view data
	 * @param array $data
	 * @return $this
	 */
	public function set( array $data = [] )
	{
		$this->vars = array_merge( $this->vars, $data );
		return $this;
	}
}
