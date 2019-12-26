<?php
/**
 * Created by PhpStorm.
 * User: woshi
 * Date: 2018/12/12
 * Time: 18:30
 */
namespace quarkblockchain;

class QkToken extends \ERC20\ERC20
{

    /**
     * 通证转账
     * @param $num //转账数量，1个就是1，100个就是100
     * @param $address //接收地址
     * @param $contract_address //token合约地址，适用于所有erc20的token
     * @return bool
     */
    public function transfer($num, $address, $contract_address)
    {

        //todo  xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
        $url = "http://127.0.0.1:xxxx";
        //合约地址
        $url_arr = parse_url($url);
        //实例化通证
        $geth = new EthereumRPC($url_arr['host'], $url_arr['port']);
        $erc20 = new ERC20($geth);
        $token = $erc20->token($contract_address);
        //托管地址（发送方）
        $payer = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
        //转账
        $data = $token->encodedTransferData($address, $num);
        $transaction = $geth->personal()->transaction($payer, $contract_address)
            ->amount("0")
            ->data($data);
        //设置gas
        $transaction->gas(90000,"0.000000001");
        //XXXXXXXXX为发送方钱包密码
        $txId = $transaction->send("XXXXXXXXXXXX");

        if ($txId && strlen($txId) == 66) {
            //返回交易hash
            return $txId;
        } else {
            return false;
        }
    }
}