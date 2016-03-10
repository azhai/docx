<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;

use Docx\Common;
use Docx\Application;
use Docx\Web\Response;


/**
 * 基本控制器.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Handler
{
    protected $app = null;
    protected $backend = null;
    protected $template = '';
    protected $context = [];
    public $globals = ['method' => 'get'];

    public function __construct(Application& $app, & $backend = null)
    {
        $this->app = $app;
        $this->backend = $backend;
    }

    public function __toString()
    {
        $response = $this->app->response;
        $response->addFrameFile($this->template);
        $response->addGlobals($this->globals);
        return $response->render($this->context);
    }

    public function __invoke()
    {
        $this->prepare();
        $action = $this->globals['method'] . 'Action';
        $args = func_get_args();
        Common::execMethodArray($this, $action, $args);
        $this->finish();
        return $this;
    }
    
    public function prepare()
    {
    }
    
    public function finish()
    {
    }
    
    public function except($error)
    {
        return Response::abort(500);
    }
}
