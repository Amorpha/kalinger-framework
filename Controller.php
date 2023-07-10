<?php

namespace Kalinger;

use Core\Core;
use Kalinger\View;
use Kalinger\Ajax;

abstract class Controller {

    public $route;
    public $view;
    public $model;
    public $ajax;
    public $optionsTemplate;

    public function __construct($route, $config, $user_info) {

        $this->route = $route;

        $this->view = new View($route, $config, $user_info);

        $this->model = $this->loadModel($this->route['controller']);

        $this->ajax = new Ajax($route);

    }

    public function loadModel($name) {

        $path = 'Application\models\\' . ucfirst($name) . 'Model';

        if (class_exists($path)) {

            return new $path;

        }

    }

    public function error($code) {

        http_response_code($code);

        $this->view->render($code, $this->optionsTemplate, 'errors');

        exit();

    }

}