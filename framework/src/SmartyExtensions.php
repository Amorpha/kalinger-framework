<?php

namespace  Kalinger;

class SmartyExtensions {

    public $prefix;

    public function __construct($prefix) {

        $this->prefix = $prefix;

    }

    public function URL($url) {

        return $this->prefix.$url['uri'];

    }

}