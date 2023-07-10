<?php

namespace Kalinger;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;

class Auth {

    public $capsule;
    public $sentinel;
    public $db;

    public function __construct() {

        $this->db = require 'application/config/db_config.php';

        $this->capsule = new Capsule;

        $this->capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $this->db['host'],
            'database'  => $this->db['dbname'],
            'username'  => $this->db['user'],
            'password'  => $this->db['password'],
            'charset'   => $this->db['charset'],
            'collation' => 'utf8_unicode_ci',
        ]);

        $this->capsule->bootEloquent();

        $this->sentinel = new Sentinel;

    }

    public function register($data) {

        if ($user = $this->sentinel::registerAndActivate($data) ) {

            return $user;

        }

        return false;

    }

    public function authenticate($data) {

        if ($user = $this->sentinel::authenticateAndRemember($data) ) {

            return $user;

        }

        return false;

    }

    public function login($user) {

        return $this->sentinel::loginAndRemember($user);

    }

    public function logout() {

        $this->sentinel::logout();

    }

}