<?php

namespace WpDepends;

use WpDepends\Attributes\GenericAttribute;

class ValidatorResult {

    public GenericAttribute $origin;
    public bool $result;

    public function __construct(GenericAttribute $origin, bool $result) {
        $this->origin = $origin;
        $this->result = $result;
    }

    public function describe(): string {
        return $this->origin->describe();
    }
}