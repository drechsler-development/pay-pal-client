<?php

namespace DD;

class PayPalPurchaseUnit {

	public string $reference_id;
	public string $description;
	public string $custom_id;
	public string $invoice_id;
	public string $soft_descriptor;
	public array  $items = [];
	public array  $amount;

	/**
	 * @param string $reference_id
	 * @param string $description
	 * @param array  $amount
	 * @param string $custom_id
	 * @param string $invoice_id
	 * @param string $soft_descriptor
	 */
	public function __construct (string $reference_id, string $description, array $amount, string $custom_id = '', string $invoice_id = '', string $soft_descriptor = '') {
		$this->reference_id    = $reference_id;
		$this->description     = $description;
		$this->custom_id       = $custom_id;
		$this->invoice_id      = $invoice_id;
		$this->soft_descriptor = $soft_descriptor;
		$this->amount          = $amount;

		return $this;
	}

	/**
	 * @param PayPalItem $item
	 *
	 * @return void
	 */
	public function AddItem (PayPalItem $item): void {
		$this->items[] = $item->Export ();
	}

	public function Export (): array {
		return [

			'reference_id'    => $this->reference_id,
			'description'     => $this->description,
			'custom_id'       => $this->custom_id,
			'invoice_id'      => $this->invoice_id,
			'soft_descriptor' => $this->soft_descriptor,
			'items'           => $this->items,
			'amount'          => $this->amount,

		];
	}

}
