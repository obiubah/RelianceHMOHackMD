<?php

class ReserveAccount
{
    public $accountReference; //String
    public $accountName; //String
    public $currencyCode; //String
    public $contractCode; //String
    public $customerEmail; //String

    /**
     * ReserveAccountRequest constructor.
     * @param $accountReference
     * @param $accountName
     * @param $currencyCode
     * @param $contractCode
     * @param $customerEmail
     */
    public function __construct($accountReference, $accountName, $currencyCode, $contractCode, $customerEmail)
    {
        $this->accountReference = $accountReference;
        $this->accountName = $accountName;
        $this->currencyCode = $currencyCode;
        $this->contractCode = $contractCode;
        $this->customerEmail = $customerEmail;
    }


}
