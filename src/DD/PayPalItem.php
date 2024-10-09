<?php

namespace DD;

class PayPalItem {

	public string $name;
	public string $quantity;
	public array  $unitAmount;

	public string $description;
	public string $sku;
	public string $url;
	public array  $tax;

	public function __construct (string $name, string $quantity, PayPalAmount $unitAmount, PayPalAmount $tax, string $sku = '', string $description = '', string $url = '') {

		$this->name        = $name;
		$this->quantity    = $quantity;
		$this->unitAmount  = $unitAmount->Export ();
		$this->tax         = $tax->Export ();
		$this->sku         = $sku;
		$this->description = $description;
		$this->url         = $url;
	}

	public function Export (): array {
		return [
			'name'        => $this->name,
			'quantity'    => $this->quantity,
			'unit_amount' => $this->unitAmount,
			'tax'         => $this->tax,
			'sku'         => $this->sku,
			'description' => $this->description,
			'url'         => $this->url
		];
	}

}
