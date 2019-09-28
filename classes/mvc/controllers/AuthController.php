<?php if ( ! defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}


/**
 * Class AuthController
 */
class AuthController extends ControllerBase
{
    /**
     * AuthController constructor.
     */
	public function __construct()
	{
		parent::__construct();

		$this->model = new AuthModel();
	}

    /**
     * The application's login page
     */
	public function login()
	{
		$viewData = [
			'page_title' => 'Login'
		];
		$this->set( $viewData );

		$this->view->render( 'frontend/auth/login', $this->layout, $this->vars );
	}

    /**
     * The application's logout page
     */
	public function logout()
	{
        $viewData = [
			'page_title' => 'Log out'
		];
		$this->set( $viewData );

		$this->view->render( 'frontend/auth/logout', $this->layout, $this->vars );
	}

    /**
     * The application's register page
     */
	public function register()
	{
		$viewData = [
            'page_title' => 'Register'
		];
		$this->set( $viewData );

		$this->view->render( 'frontend/auth/register', $this->layout, $this->vars );
	}

    /**
     * The application's recover password page
     */
	public function lost_password()
	{
		$viewData = [
            'page_title' => 'Recover password'
		];
		$this->set( $viewData );

		$this->view->render( 'frontend/auth/lost_password', $this->layout, $this->vars );
	}

    /**
     * The application's register page
     */
	public function confirm_email()
	{
		$viewData = [
            'page_title' => 'Confirm email'
		];
		$this->set( $viewData );

		$this->view->render( 'frontend/auth/confirm_email', $this->layout, $this->vars );
	}


}
