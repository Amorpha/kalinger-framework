<?php

namespace Kalinger;

abstract class BaseCore {

    public $model;

    public function __construct() {

        $this->model = $this->loadModel('Core');

    }

    public function loadModel($name) {

        $path = 'Application\models\\' . ucfirst($name) . 'Model';

        if (class_exists($path)) {

            return new $path;

        }

    }

}