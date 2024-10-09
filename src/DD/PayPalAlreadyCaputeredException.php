<?php

namespace DD;

use Exception;

class PayPalAlreadyCaputeredException extends Exception {

	public function errorMessage (): string {

		return $this->getMessage ();

	}

}
