<?php

require('Models/RequestModels/ReserveAccount.php');
require('Contracts/AccountProviderContract.php');

class MonnifyIntegration implements AccountProviderContract
{
    public $apiKey;
    private $authToken;
    private $authTokenExpiryTime;
    public $clientSecret;
    public $apiBaseEndPoint;

    public function __construct($apiKey, $clientSecret, $baseEndPoint)
    {
        $this->apiKey = $apiKey;
        $this->clientSecret = $clientSecret;
        $this->apiBaseEndPoint = $baseEndPoint;
    }

    public function getAuthToken()
    {
        if ($this->authTokenExpiryTime > time()) {
            echo "Using existing authToken ---- <br />";
            return $this->authToken;
        }

        echo "<--- Fetching new token ---> <br />";
        $basicAuth = base64_encode($this->apiKey . ":" . $this->clientSecret);
        $ch = curl_init($this->apiBaseEndPoint . "/api/v1/auth/login");

        $header = array();
        $header[] = 'Authorization: Basic ' . $basicAuth;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $jsonResponse = curl_exec($ch);

        $response = $this->handleMonnifyResponse($jsonResponse, $ch);

        $this->authToken = $response['responseBody']['accessToken'];
        $this->authTokenExpiryTime = time() + ($response['responseBody']['expiresIn'] - 10);

        return $this->authToken;
    }

    public function reserveAccount(ReserveAccount $monnifyReserveAccountRequest)
    {
        $data_string = json_encode($monnifyReserveAccountRequest);
        $reserveAccountUrl = $this->apiBaseEndPoint . "/api/v1/bank-transfer/reserved-accounts";

        $ch = curl_init($reserveAccountUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->getAuthToken(),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $jsonResponse = curl_exec($ch);

        $response = $this->handleMonnifyResponse($jsonResponse, $ch);

        print_r("Reserve Account Request Was Successful \n" . $response['responseBody'] . "<br>");

        return $this->buildReservedAccountObject($response['responseBody']);

    }

    public function deactivateReservedAccount($accountReference)
    {
        echo "--- Deactivating account with reference ::: " . $accountReference . "--- <br>";
        $deactivateReserveAccountUrl = $this->apiBaseEndPoint . "/api/v1/bank-transfer/reserved-accounts/" . $accountReference;

        $ch = curl_init($deactivateReserveAccountUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->getAuthToken())
        );

        $jsonDeactivateAccountResponse = curl_exec($ch);

        $response = $this->handleMonnifyResponse($jsonDeactivateAccountResponse, $ch);

        echo "Deactivate Reserved Account Request Was Successful <br>";

        return $response['responseBody']['requestSuccessful'];
    }

    public function getTransactionStatus($transactionReference)
    {
        echo "--- Fetching status of transaction with reference ::: " . $transactionReference . "--- <br>";

        $transactionReferenceUrl = $this->apiBaseEndPoint . "/api/v1/merchant/transactions/query?transactionReference=" . $transactionReference;

        $ch = curl_init($transactionReferenceUrl);

        $basicAuth = base64_encode($this->apiKey . ":" . $this->clientSecret);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Basic ' . $basicAuth)
        );

        $jsonResponse = curl_exec($ch);
        $response = $this->handleMonnifyResponse($jsonResponse, $ch);


        print_r("Deactivate Reserved Account Request Was Successful \n" . $response['responseBody'] . "<br>");

        if (!$response['responseBody']['paymentStatus']) {
            throw new Exception("Payment Status can not be empty!");
        }

        return $response['responseBody']['paymentStatus'];
    }

    private function handleMonnifyResponse($jsonResponse, $ch)
    {
        if ($jsonResponse === false) {
            print_r('Curl error: ' . curl_error($ch));
        }

        $response = json_decode($jsonResponse, true);

        if ($response['error']) {
            throw new Exception($response['error_description']);
        }

        print_r($response);

        if (!$response['requestSuccessful']) {
            throw new Exception("Unsuccessful Request! Please take appropriate action");
        }

        if (!$response['responseBody']) {
            throw new Exception("Empty Response Body! Please take appropriate action");
        }

        return $response;

    }

    private function buildReservedAccountObject($response)
    {
        $reservedAccount = new ReserveAccount($response['accountReference'], $response['accountName'], $response['currencyCode'], $response['customerEmail'], $response['accountNumber']);

        return $reservedAccount;
    }
}

//$monnifyIntegration = new MonnifyIntegration("MK_TEST_WD7TZCMQV7", "H5EQMQSHSURJNQ7UH2R78YAH6UN54ZP7", 'https://sandbox.monnify.com');

//$monnifyReserveAccount = new ReserveAccountRequest("RandyOrton", "Obinna", "NGN", "2957982769", "slayer@gmail.com");

//$monnifyIntegration->reserveAccount($monnifyReserveAccount);

//$monnifyIntegration->deactivateReservedAccount("RandyOrton");

//$monnifyIntegration->getTransactionStatus("RandyOrton");

//print_r($monnifyIntegration->getAuthToken());



