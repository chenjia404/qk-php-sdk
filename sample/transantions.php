<?php
include "../vendor/autoload.php";

use quarkblockchain\QkNodeRPC;

$url = "http://localhost:8545";
$url_arr = parse_url($url);
//实例化节点对象
$qk_node = new QkNodeRPC('127.0.0.1', '8545');

//托管地址（发送方）
$payer = "0x72b7065b33e6ff40b79c068e0e1ac5ca82d94256";
$fo = "0xc30a1348a96b4b8d779b845644e3b35399a9e968";
//转账
$transaction = $qk_node->personal()->transaction($payer, '0xc30a1348a96b4b8d779b845644e3b35399a9e968')
    ->amount(100)
    ->data("");
//XXXXXXXXX为发送方钱包密码
$txId = $transaction->send("123456");
echo "转账hash:" . $txId . "\n";

$contract_address = "0x4175aa5d372015b67ef58514414086f0f36caa7a";
$ERC20 = new \ERC20\ERC20($qk_node);
$token = $ERC20->token($contract_address);
$data = $token->encodedTransferData($fo, 1);
$transaction = $qk_node->personal()->transaction($payer, $contract_address)
    ->amount("0")
    ->data($data);
//XXXXXXXXX为发送方钱包密码
$txId = $transaction->send("123456");
echo "token $txId\n";
