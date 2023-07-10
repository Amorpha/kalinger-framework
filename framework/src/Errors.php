<?php

namespace Kalinger;

use Kalinger\View;

abstract class Errors {

    public $route;
    public $view;

    public function __construct($route, $config, $user_info) {

        $this->route = $route;

        $this->view = new View($route, $config, $user_info);

    }

}