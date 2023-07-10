<?php

namespace Kalinger;

class Ajax {

    public $route;

    public function __construct($route) {

        $this->route = $route;

    }

    public static function sendResponse($response, $type = false) {

        if ($type == 'json') {

            header('Content-Type: application/json');

        }

        exit(json_encode($response));

    }

    public static function redirect($url) {

        exit(json_encode($url));

    }

    public static function getAllPost($type = false) {

        if ($type == 'json') {

            return json_decode(file_get_contents('php://input'), true);

        } elseif ($type == 'form-data') {

            return file_get_contents('php://input');

        } else {

            return $_POST;

        }

    }

    public static function getAllPut($type = false) {

        if ($type == 'json') {

            return json_decode(file_get_contents('php://input'), true);

        } else {

            return $_POST;

        }

    }

    public static function getAllGet($type = false) {

        if ($type == 'json') {

            return json_decode(file_get_contents('php://input'), true);

        } else {

            return $_GET;

        }

    }

    public static function getElementGet($route, $element) {

        return isset($route[$element]) ? $route[$element] : NULL;

    }

    public static function getElementPost($element) {

        return isset($_POST[$element]) ? $_POST[$element] : NULL;

    }

    public static function getFilePost($element) {

        return isset($_FILES[$element]) ? $_FILES[$element] : NULL;

    }

}