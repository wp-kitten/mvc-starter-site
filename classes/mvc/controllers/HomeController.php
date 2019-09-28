<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class HomeController
 */
class HomeController extends ControllerBase
{
    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->view = new ViewBase();
        $this->model = new FrontendModel();
    }

    /**
     * The application's home page
     */
    public function index()
    {
        $viewData = [
            'page_title' => 'Home'
        ];
        $this->set( $viewData );

        $this->view->render( 'frontend/home/index', $this->layout, $this->vars );
    }

    /**
     * The application's home page. This method is an alias for index()
     */
    public function home()
    {
        $this->index();
    }
}
