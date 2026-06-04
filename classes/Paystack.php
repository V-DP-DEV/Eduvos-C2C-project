<?php
class Paystack{

	// Refund a transaction via Paystack API
	public static function refundTransaction($transactionReference){
		$url = "https://api.paystack.co/refund";

    	$data = [
        	"transaction" => $transactionReference
    	];

    	$ch = curl_init($url);

    	curl_setopt_array($ch, [
        	CURLOPT_RETURNTRANSFER => true,
        	CURLOPT_POST => true,
        	CURLOPT_HTTPHEADER => [
            	"Authorization: Bearer ". $_ENV['paystackSk'],
            	"Content-Type: application/json"
        	],
        	CURLOPT_POSTFIELDS => json_encode($data),
    	]);

    	$response = curl_exec($ch);

    	if (curl_errno($ch)) {
        	throw new Exception(curl_error($ch));
    	}

    	curl_close($ch);

    	return json_decode($response, true);
	}

	// Resolve bank account details
    public static function resolveAccount($bankCode,$accountNumber){
        $url = "https://api.paystack.co/bank/resolve?account_number=" . urlencode($accountNumber) ."&bank_code=".urlencode($bankCode)."&currency=NGN";
    	$ch = curl_init();

    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, [
        	"Authorization: Bearer ". $_ENV['paystackSk'],
        	"Cache-Control: no-cache"
    	]);

    	$response = curl_exec($ch);
    	curl_close($ch);
        
        return json_decode($response, true);  
    }
    
   	// Mock payout (for testing)
    public static function mockPayout($transactionReference) {
        return [
            "status" => true,
            "data" => [
                "id" => rand(10000,99999),
                "status"=> "success",
                "transaction" => [
                    "id" => rand(10000,99999)
                ]
            ]
        ];
    }

	// Get transaction details
    public static function getTransaction($transactionReference){
        $ch = curl_init();
        $url = "https://api.paystack.co/transaction/verify/" . urlencode($transactionReference);

    	curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, [
        	"Authorization: Bearer ". $_ENV['paystackSk'],
        	"Cache-Control: no-cache"
		]);

    	$response = curl_exec($ch);
    	curl_close($ch);

    	return json_decode($response, true);
    }
    
	// Get refund details (NOTE: possible bug in original URL logic)
    public static function getRefund($refundId){
        $ch = curl_init();
        $url="https://api.paystack.co/refund/".urlencode($refundId);

    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, [
        	"Authorization: Bearer ". $_ENV['paystackSk'],
        	"Cache-Control: no-cache"
    	]);

    	$response = curl_exec($ch);
    	curl_close($ch);

    	return json_decode($response, true);
    }

	// Initialize payment
    public static function createPayment($amount,$reference){
        $ch = curl_init("https://api.paystack.co/transaction/initialize");

		curl_setopt_array($ch, [
    		CURLOPT_POST => true,
    		CURLOPT_POSTFIELDS => json_encode([
        		'email' => 'buyer@example.com',
        		'amount' => $amount*100,
        		'reference' => $reference,
        		"callback_url" => "https://myc2c.gamer.gd/index.php?page=yourOrders"
    		]),
    		CURLOPT_HTTPHEADER => [
        		"Authorization: Bearer ". $_ENV['paystackSk'],
        		"Content-Type: application/json"
    		],
    		CURLOPT_RETURNTRANSFER => true,
		]);

		$response = curl_exec($ch);
		return json_decode($response, true);
    }

	// Create transfer recipient on Paystack
    public static function createRecepientOnPaystack($name,$number,$bankCode){

	$curl = curl_init();

	curl_setopt_array($curl, array(
    	CURLOPT_URL => "https://api.paystack.co/transferrecipient",
    	CURLOPT_RETURNTRANSFER => true,
    	CURLOPT_CUSTOMREQUEST => "POST",
    	CURLOPT_POSTFIELDS => json_encode([
        	"type" => "nuban",
        	"name" => $name,
        	"account_number" => $number,
        	"bank_code" => $bankCode,
        	"currency" => "NGN"
    	]),
    	CURLOPT_HTTPHEADER => [
        	"Authorization: Bearer ". $_ENV['paystackSk'],
        	"Content-Type: application/json"
    	],
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
    	return null;
	} else {
    	$result = json_decode($response, true);

        // Debug output (should be removed in production)
        var_dump($result);
        var_dump($bankCode);

    	if ($result['status']) {
        	return $result['data'];
		}
        return null;
	}
}
}
?>