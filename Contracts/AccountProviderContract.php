<?php

interface AccountProviderContract
{
    public function reserveAccount(ReserveAccount $reserveAccountRequest);

    public function deactivateReservedAccount($accountReference);

    public function getTransactionStatus($transactionReference);

}