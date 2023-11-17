<?php

namespace WpDepends\Traits;
use WpDepends\ValidatorResult;

trait Validatable {
    abstract public function validate(): ValidatorResult;
}