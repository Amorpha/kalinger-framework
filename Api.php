<?php

namespace Kalinger;

class Api {

    public $ajax;
    private $config;

    public function __construct($config) {

        $this->config = $config;

    }

    public function autorizationApi() {

        $result = true;

        if (!isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_PW'] == $this->config['api']['password']) && (strtolower($_SERVER['PHP_AUTH_USER']) == $this->config['api']['login'])) {

            $result = false;

        }

        return $result;

    }

    public function autorizationFalse() {

        header('WWW-Authenticate: Basic realm="Backend"');
        header('HTTP/1.0 401 Unauthorized');

        echo 'Authenticate required!';

    }

}