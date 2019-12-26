<?php
/**
 * Created by PhpStorm.
 * User: woshi
 * Date: 2018/12/12
 * Time: 18:28
 */
namespace quarkblockchain;

class QkNodeRPC extends \EthereumRPC\EthereumRPC
{
    /**
     * 获取区块
     */
    public function getBlocks()
    {
        //初始区块高度为0
        $lastBlock = 0;

        //一次性获取500个区块
        $num = 500;
        for ($i = 0; $i < $num; $i++) {
            //组装参数
            if ($lastBlock < 10) {
                $blockArray[$i] = ['0x' . $lastBlock, true];
            } else {
                //区块高度10以上，需要将区块高度数字从10进制转为16进制
                $blockArray[$i] = ['0x' . base_convert($lastBlock, 10, 16), true];
            }

            $lastBlock++;
        }
        //获取区块，调用RpcService里面的方法
        $rpcService = new \RpcService();
        $blocks = $rpcService->getBlockByNumber($blockArray);
        //todo  区块获取成功后，循环遍历区块，保存交易到数据库
    }

    /**
     * QKI转账
     * @param $num //转账数量，1个就是1，100个就是100
     * @param $address //接收地址
     * @return bool
     */
    public function transfer($num, $address)
    {
        $rpc = new \RpcService();
        $system_address = "xxxxxxxxxxxxxx";//转出方钱包地址
        $system_password = "xxxxxxxxxxxxxxx";//转出方钱包密码
        //判断托管账号余额
        $params = array(
            [$system_address,"latest"]
        );
        //获取转出方地址余额信息
        $res_data = $rpc->rpc("eth_getBalance",$params);
        $res_data = isset($res_data[0])?$res_data[0]:array();
        $qki_balance = bcdiv(gmp_strval($res_data['result']) ,gmp_pow(10,18),8);

        //判断转出方余额是否足够
        if(bccomp($qki_balance,bcadd($num,1,8),8) < 0)
        {
            echo "转出地址余额不足";
            return false;
        }

        //转出方地址解锁
        $unlock_address_data = $rpc->rpc('personal_unlockAccount', [[$system_address, $system_password, 2]]);
        if (isset($unlock_address_data[0]['result']) && $unlock_address_data[0]['result']) {
            //余额格式处理，乘以位数，再转为16进制
            $amount = bcmul($num, 1000000000000000000, 0);
            $final_amount = base_convert($amount, 10, 16);
            //转账
            $data = [[[
                'from' => $system_address,
                'to' => $address,
                'value' => '0x' . $final_amount
            ]]];

            $result = $rpc->rpc('eth_sendTransaction', $data);
            //转账成功，返回交易HASH
            if (strlen($result[0]['result']) == 66) {
                return $result[0]['result'];
            } else {
                echo "转账失败";
                return false;
            }
        }else{
            echo "转出方地址解锁失败";
            return false;
        }
    }
}