<?php

use Kyt\MVC\AbstractController;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class ControllerBase
 */
class ControllerBase extends AbstractController
{
    /**
     * The name of the default layout to use
     * @var string
     */
    protected $layout = MVC_DEFAULT_LAYOUT;

    /**
     * ControllerBase constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->view = new ViewBase();
        $this->model = new FrontendModel();
    }


    public function not_found()
    {
        $this->view->render( '__globals/not_found', $this->layout, [
            'page_title' => 'Not Found'
        ] );
    }

    public function forbidden()
    {
        $this->view->render( '__globals/forbidden', $this->layout, [
            'page_title' => 'Forbidden'
        ] );
    }

}
