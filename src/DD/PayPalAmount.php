<?php

namespace DD;

class PayPalAmount {

	public string $value;
	public string $currency_code;

	/**
	 * @param string $value
	 * @param string $currency_code
	 */
	public function __construct (string $value, string $currency_code = 'EUR') {
		$this->value         = $value;
		$this->currency_code = $currency_code;
	}

	public function Export (): array {
		return [
			'value'         => $this->value,
			'currency_code' => $this->currency_code
		];
	}

}
