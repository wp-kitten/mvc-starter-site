<?php if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

/**
 * Class Router
 */
class Router
{
    /**
     * Holds the reference to the instance of the Request class
     * @var Request|null
     */
    public $request = null;

    /**
     * Router constructor.
     */
    public function __construct(  )
    {
        $this->request = new Request();
    }

    /**
     * Dispatch a request to the appropriate controller.
     * Emits two events:
     *      request/before-dispatch
     *      request/after-dispatch
     */
    public function dispatch()
    {
        do_action( 'request/before-dispatch', $this->request );
        call_user_func( [ $this->request->controller, $this->request->action ], $this->request->params );
        do_action( 'request/after-dispatch', $this->request );
        exit;
    }
}
