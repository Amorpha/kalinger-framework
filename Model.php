<?php

namespace  Kalinger;

use Kalinger\DB;

abstract class Model {

    public $DB;
    public $core;

    public function __construct($core = false) {

        $this->DB = new DB;
        $this->core = $core;

    }

}