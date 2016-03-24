<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;

use Docx\Common;
use Docx\Application;


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
    public $method = 'get';
    public $args = [];
    public $globals = [];

    public function __construct(Application& $app,
                & $backend = null, $method = 'get')
    {
        $this->app = $app;
        $this->backend = $backend;
        $this->method = $method;
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
        $this->args = func_get_args();
        $this->prepare();
        $action = $this->method . 'Action';
        Common::execMethodArray($this, $action, $this->args);
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
