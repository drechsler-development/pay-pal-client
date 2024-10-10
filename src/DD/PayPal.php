<?php

namespace DD;

use Exception;

class PayPal {

	const string PAYPAL_API_URL_PROD = "https://api.paypal.com";
	const string PAYPAL_API_URL_TEST = "https://api.sandbox.paypal.com";

	public string $clientId;
	public string $clientSecret;
	public string $url;

	public string $accessToken;

	public function __construct (string $clientId, string $clientSecret, bool $isSandbox = false) {

		$this->clientId     = $clientId;
		$this->clientSecret = $clientSecret;
		$this->url          = $isSandbox ? self::PAYPAL_API_URL_TEST : self::PAYPAL_API_URL_PROD;

	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public function Authenticate (): void {

		$ch = curl_init ();

		curl_setopt ($ch, CURLOPT_URL, $this->url . '/v1/oauth2/token');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

		$headers = [
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: Basic ' . base64_encode ($this->clientId . ':' . $this->clientSecret)
		];

		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec ($ch);

		curl_close ($ch);

		$this->accessToken = json_decode ($result, true)['access_token'] ?? throw new Exception('Could not authenticate with PayPal');

	}

	/**
	 * @param PayPalOrder $order
	 *
	 * @return array
	 * @throws Exception
	 */
	public function PostCreateOrder (PayPalOrder $order): array {

		$this->Authenticate ();

		$ch = curl_init ();

		curl_setopt ($ch, CURLOPT_URL, $this->url . '/v2/checkout/orders');
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode ($order));

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $this->accessToken
		];

		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec ($ch);

		curl_close ($ch);

		return json_decode ($result, true);

	}

	/**
	 * @param array $response
	 *
	 * @return string
	 * @throws Exception
	 */
	public function GetApprovalLink (array $response): string {

		$approveLink = '';

		if(empty($response['links'])){
			throw new Exception('No links found in response');
		}

		foreach ($response['links'] as $link) {
			if ($link['rel'] === 'payer-action') {
				$approveLink = $link['href'];
				break;
			}
		}

		return $approveLink;

	}

	/**
	 * @param array $response
	 *
	 * @return string
	 * @throws Exception
	 */
	public function GetOrderId (array $response) : string {

		$orderId = $response['id'] ?? '';

		if(empty($orderId)){
			throw new Exception('No order id found in response');
		}

		return $orderId;
	}

	/**
	 * @param $orderId
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function IsOrderApproved ($orderId) : bool {

		$this->Authenticate ();

		$ch = curl_init ();

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $this->accessToken
		];

		curl_setopt ($ch, CURLOPT_URL, $this->url . '/v2/checkout/orders/'.$orderId);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "GET");

		$response = curl_exec ($ch);

		if (curl_errno ($ch)) {
			$error_msg = curl_error ($ch);
			curl_close ($ch);
			throw new Exception($error_msg);
		}

		curl_close ($ch);

		$array = json_decode ($response, true);

		if ($array['intent'] !== 'CAPTURE') {
			throw new Exception('Order intent is not CAPTURE');
		}

		if(empty($array['status'])){
			throw new Exception('No status found in response');
		}

		return $array['status'] == 'APPROVED';
	}

	/**
	 * @param $orderId
	 *
	 * @return string
	 * @throws PayPalAlreadyCaputeredException
	 * @throws Exception
	 */
	public function CaptureOrder ($orderId): string {

		$this->Authenticate ();

		$ch = curl_init ();

		$headers = [
			'Content-Type: application/json',
			'Prefer: return=representation',
			'Authorization: Bearer ' . $this->accessToken
		];

		curl_setopt ($ch, CURLOPT_URL, $this->url . '/v2/checkout/orders/' . $orderId . "/capture");
		curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, ''); // Leerer Body

		$response = curl_exec ($ch);

		if (curl_errno ($ch)) {
			$error_msg = curl_error ($ch);
			throw new Exception($error_msg);
		}

		curl_close ($ch);

		$array = json_decode (json_encode (json_decode ($response, true)), true);

		$intend = $array['intent'] ?? '';

		if ($intend === 'CAPTURE') {
			$status = $array['status'] ?? '';
			if ($status === 'COMPLETED') {

				$captureId = $array['purchase_units'][0]['payments']['captures'][0]['id'] ?? '';
				if(empty($captureId)){
					throw new Exception('No capture id found in response');
				}

				return $captureId;

			} else {
				throw new Exception('Order status is not COMPLETED');
			}
		} else {

			$details          = $array['details'] ?? [];
			$detailsFirstNode = $details[0] ?? '';
			$issue            = $detailsFirstNode['issue'] ?? '';

			if ($issue == 'ORDER_ALREADY_CAPTURED') {
				throw new PayPalAlreadyCaputeredException('Order already captured');
			} else {
				throw new Exception('Order could not be captured for an unknown reason (yet)');
			}

		}

	}


}
