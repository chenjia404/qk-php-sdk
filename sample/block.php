<?php
include "../vendor/autoload.php";

use ERC20\ERC20;
use quarkblockchain\BcMath;
use quarkblockchain\QkNodeRPC;
use quarkblockchain\QkToken;

$url = "http://localhost:8545";
$url_arr = parse_url($url);
//实例化节点对象
$qk_node = new QkNodeRPC('127.0.0.1', '8545');
$latest_block_number = $qk_node->QKI()->blockNumber();
echo "当前区块高度:" .  $latest_block_number . "\n";
$block_2225016 = $qk_node->QKI()->getBlock(2225016);
echo "区块hash:" . $block_2225016->hash . "\n";
echo "区块高度:" . $block_2225016->number() . "\n";
echo "使用gas:" . BcMath::HexDec($block_2225016->gasUsed) . "\n";
echo "打包时间:" .  date("Y-m-d H:i:s",BcMath::HexDec($block_2225016->timestamp)) . "\n";
echo "区块2225016里面有" . count($block_2225016->transactions) . "笔交易\n";
foreach($block_2225016->transactions as $tx_id)
{
    echo "交易hash:" . $tx_id . "\n";
    $tx = $qk_node->QKI()->getTransaction($tx_id);
    echo "转出:" .  $tx->from . "\n";
    echo "转入:" .  $tx->to . "\n";
    $token_tx = $tx->input();
    if($token_tx != null)
    {
        echo "通证转账:\n";
        echo "接收方:{$token_tx->payee}\n"; 
        $qk_token = new ERC20($qk_node);
        $tx_token = $qk_token->token($tx->to);
        $decimals = $tx_token->decimals();
        $token_tx_amount = bcdiv(BcMath::HexDec($token_tx->amount),gmp_pow(10, $decimals),18);
        echo "数量{$token_tx_amount}" . $tx_token->symbol() . "\n";
    }
    $tx_receipt = $qk_node->QKI()->getTransactionReceipt($tx_id);
    echo "转账状态:" . BcMath::HexDec($tx_receipt->status) . "\n";//1成功 0失败
}
