<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class AdminController
 */
class AdminController extends ControllerBase
{
    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->view = new ViewBase();
        $this->model = new FrontendModel();
    }

    /**
     * The administration home page
     */
    public function index()
    {
        $viewData = [
            'page_title' => 'Dashboard'
        ];
        $this->set( $viewData );

        $this->view->render( 'admin/dashboard', $this->layout, $this->vars );
    }

    /**
     * The administration home page. This method is an alias for index()
     */
    public function dashboard()
    {
        $this->index();
    }
}
