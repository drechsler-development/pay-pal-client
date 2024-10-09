<?php

namespace DD;

class PayPalPaymentSource {

	public readonly string $payment_method_preference;
	public string          $brand_name;
	public string          $return_url;
	public string          $cancel_url;
	public string          $locale;
	public string          $landing_page;
	public string          $shipping_preference;
	public string          $user_action;

	public function __construct (string $brand_name, string $return_url = "/returnUrl", string $cancel_url = "/cancelUrl", string $locale = "de-DE", string $landing_page = "LOGIN", string $shipping_preference = "SET_PROVIDED_ADDRESS", string $user_action = "PAY_NOW") {
		$this->payment_method_preference = "IMMEDIATE_PAYMENT_REQUIRED";
		$this->brand_name                = $brand_name;
		$this->locale                    = $locale;
		$this->landing_page              = $landing_page;
		$this->shipping_preference       = $shipping_preference;
		$this->user_action               = $user_action;
		$this->return_url                = $return_url;
		$this->cancel_url                = $cancel_url;

		return $this;
	}

	public function Export (): array {
		return [
			"paypal" => [
				"experience_context" => [
					"payment_method_preference" => $this->payment_method_preference,
					"brand_name"                => $this->brand_name,
					"locale"                    => $this->locale,
					"landing_page"              => $this->landing_page,
					"shipping_preference"       => $this->shipping_preference,
					"user_action"               => $this->user_action,
					"return_url"                => $this->return_url,
					"cancel_url"                => $this->cancel_url
				]
			]
		];
	}
}
