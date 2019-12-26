# QKI网络的通证接入教程（PHP版）

## 关于本文档

> 本文档是基于PHP的CCT同步、转账功能实现的代码教程，使用PHP框架为laravel，


## 前置准备工作

> $\color{#FF3030}{请在你的服务器上运行夸克区块链节点}$


## 参考资料

$\color{#FF3030}{夸克区块链RPC接口兼容以太坊RPC接口}$

[以太坊JSON RPC API](http://cw.hubwiz.com/card/c/ethereum-json-rpc-api/)

[Geth JSON-RPC管理API](http://cw.hubwiz.com/card/c/geth-rpc-api/)

## 我自己封装的一个类，通过调用RPC接口获取区块相关数据


    <?php
        
    /**
     * rpc
     * @param $method
     * @param $params
     * @return mixed
     */
    public function rpc($method,$params)
    {
        $param = array();
        foreach ($params as $key => $item)
        {
            $id = rand(1,100);
            $param[$key] = [
                'jsonrpc'=>"2.0",
                "method"=>$method,
                "params"=>$item,
                "id"=>$id
            ];
        }

        $param = json_encode($param);
        $data_str = $this->curlPost($param);
        $data = json_decode($data_str,true);

        return $data;
    }

    /**
     * 获得区块
     * @param $param
     * @return mixed
     */
    public function getBlockByNumber($param)
    {
        $block = $this->rpc('eth_getBlockByNumber',$param);
        return $block;
    }

    /**
     * 获取最后一个区块的高度
     * @return mixed
     */
    public function lastBlockHeightNumber()
    {
        $params = array(
            ['latest',true]
        );
        $blockHeight = $this->rpc('eth_getBlockByNumber',$params);

        return $blockHeight[0]['result']['number'];
    }

    /**
     * 获取区块数组
     * @param $lastBlock
     * @return array
     */
    public function getBlockString($lastBlock)
    {
        $blockArray = array();
        for($i=0;$i<20;$i++)
        {
            $blockArray[$i] = ['0x'.base_convert($lastBlock--,10,16),true];
        }
        return $blockArray;
    }

    /**
     * 根据hash获取区块详情
     * @param $hash
     * @return mixed
     */
    public function getBlockByHash($hash)
    {
        $method = 'eth_getBlockByHash';
        $param = array(
            [$hash,true]
        );
        $blockInfo = $this->rpc($method,$param);
        return $blockInfo[0];
    }

    /**
     * post请求
     * @param $data
     * @return mixed
     */
    public function curlPost($data)
    {
        //todo  xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
        $url = "http://127.0.0.1:xxxx";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
    ?>

## 同步功能代码

获取区块

    <?php

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
        $rpcService = new RpcService();
        $blocks = $rpcService->getBlockByNumber($blockArray);
        //todo  区块获取成功后，循环遍历区块，保存交易到数据库
    ?>

## QKI转账代码
    <?php
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
    ?>


## 通证转账代码

三方库依赖：[furqansiddiqui/erc20-php](https://github.com/furqansiddiqui/erc20-php)、[furqansiddiqui/ethereum-rpc](https://github.com/furqansiddiqui/ethereum-rpc)
        

    <?php
        /**
         * 通证转账
         * @param $num //转账数量，1个就是1，100个就是100
         * @param $address //接收地址
         * @param $contract_address  //token合约地址，适用于所有erc20的token
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
    ?>