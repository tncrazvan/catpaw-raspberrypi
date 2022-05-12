<?php

namespace CatPaw\RaspberryPI\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class GPIOException extends Exception {
// Redefine the exception so message isn't optional
	#[Pure] public function __construct($message, $code = 0, Throwable $previous = null) {
		// some code

		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__.": [{$this->code}]: {$this->message}\n";
	}
}