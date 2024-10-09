<?php

namespace DD;

class PayPalOrder {

	public string $intent         = 'CAPTURE';
	public array  $purchase_units = [];
	public array  $payment_source;

	/**
	 * @param PayPalPurchaseUnit       $purchaseUnit
	 * @param PayPalPaymentSource|null $paymentSource
	 */
	public function __construct (PayPalPurchaseUnit $purchaseUnit, PayPalPaymentSource $paymentSource = null) {

		$this->purchase_units[] = $purchaseUnit->Export ();
		$this->payment_source   = $paymentSource->Export ();

		return [
			'intent'         => $this->intent,
			'purchase_units' => $this->purchase_units,
			'payment_source' => $this->payment_source
		];
	}
}
