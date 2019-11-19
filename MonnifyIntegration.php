<?php

class MonnifyIntegration
{
    public $apiKey;
    private $authToken;
    private $authTokenExpiryTime;
    public $clientSecret;

    public function __construct($apiKey, $clientSecret)
    {
        $this->apiKey = $apiKey;
        $this->clientSecret = $clientSecret;
    }


    public function getAuthToken()
    {
        if ($this->authTokenExpiryTime > time()) {
            echo "Using existing authToken";
            return $this->authToken;
        }

        $loginEndPoint = "https://sandbox.monnify.com/api/v1/auth/login";
        $concatenateSecretAndApiKey = $this->apiKey . ":" . $this->clientSecret;
        $basicAuth = base64_encode($concatenateSecretAndApiKey);
        $curl = curl_init();

        $header = array();
        $header[] = 'Authorization: Basic ' . $basicAuth;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $loginEndPoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $jsonResponse = curl_exec($curl);

        if ($jsonResponse === false) {
            print_r('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $response = json_decode($jsonResponse, true);

        if (!$response['requestSuccessful']) {
            echo "Throw Exception Here!";
        }

        $this->authToken = $response['responseBody']['accessToken'];
        $this->authTokenExpiryTime = time() + ($response['responseBody']['expiresIn'] - 10);

        return $this->authToken;
    }
}

$monnifyIntegration = new MonnifyIntegration("MK_TEST_WD7TZCMQV7", "H5EQMQSHSURJNQ7UH2R78YAH6UN54ZP7");
print_r($monnifyIntegration->getAuthToken());

print_r($monnifyIntegration->getAuthToken());



