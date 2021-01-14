<?php
/**
 * Created by PhpStorm.
 * User: woshi
 * Date: 2018/12/12
 * Time: 18:30
 */
namespace quarkblockchain;

use EthereumRPC\BcMath;
use EthereumRPC\EthereumRPC;
use EthereumRPC\Exception\GethException;

class QKI extends \EthereumRPC\API\Eth
{


    /**
     * @param string $account
     * @param string $scope
     * @return string
     * @throws GethException
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBalance(string $account, string $scope = "latest"): string
    {
        $request = $this->client->jsonRPC("eth_getBalance", null, [$account, $scope]);
        $balance = $request->get("result");
        if (!is_string($balance) || !preg_match('/^(0x)?[a-f0-9]+$/', $balance)) {
            throw GethException::unexpectedResultType("eth_getBalance", "hexdec", gettype($balance));
        }

        $balance = str_replace('0x','',$balance);
        $balance = strval(BcMath::HexDec($balance));
        return bcdiv($balance, bcpow("10", "18", 0), EthereumRPC::SCALE);
    }
}