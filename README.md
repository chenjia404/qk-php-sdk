### 说明
需要有一个夸克区块链的全节点，并且同步到最新区块链。

节点搭建教程：https://qki.dev/#/qk-node

### 安装
```
composer require chenjia404/qk-php-sdk
```

### 查询qki余额
注意需要等待区块同步到最新区块，如果区块没有同步到最新区块，那么获取的余额是不准确的，只是在当时区块的余额。
```php
<?php
use quarkblockchain\QkNodeRPC;
use quarkblockchain\ERC20;

//xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
$url = "http://127.0.0.1:xxxx";
$url_arr = parse_url($url);
//实例化节点对象
$qk_node = new QkNodeRPC($url_arr['host'], $url_arr['port']);
$qk_node->QKI()->getBalance("0x000000000000000000000000000000000000dead");
```

### QKI转账代码
余额不足无法发起交易，注意需要等待区块打包成功，
调用本接口前，请先提交数据库事务，避免本接口异常导致的数据异常。
```php
<?php
use quarkblockchain\QkNodeRPC;
use quarkblockchain\ERC20;
    /**
     * QKI转账
     * @param $num //转账数量，1个就是1，100个就是100
     * @param $address //接收地址
     * @return bool
     */
    public function transfer($num, $address)
    {
        //xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
        $url = "http://127.0.0.1:xxxx";
        $url_arr = parse_url($url);
        //实例化节点对象
        $qk_node = new QkNodeRPC($url_arr['host'], $url_arr['port']);
        //托管地址（发送方）
        $payer = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
        //转账
        $transaction = $qk_node->personal()->transaction($payer, $address)
            ->amount($num)
            ->data("");
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
```

### 查询通证余额
注意需要等待区块同步到最新区块，如果区块没有同步到最新区块，那么获取的余额是不准确的，只是在当时区块的余额。
```php
<?php
use quarkblockchain\QkNodeRPC;
use quarkblockchain\ERC20;

//xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
$url = "http://127.0.0.1:xxxx";
$url_arr = parse_url($url);

//合约地址
$contract_address = "";
//实例化节点对象
$qk_node = new QkNodeRPC($url_arr['host'], $url_arr['port']);
$ERC20 = new ERC20($qk_node);
$token = $ERC20->token($contract_address);
$token->balanceOf('0x000000000000000000000000000000000000dead);
```


### 通证转账
注意需要等待区块打包成功，即使区块打包成功，也会有合约内部失败，也会出现转账失败。
合约内部失败的情况一般是余额不足，矿工费不足。
调用本接口前，请先提交数据库事务，避免本接口异常导致的数据异常。
```php
<?php
use quarkblockchain\QkNodeRPC;
use quarkblockchain\ERC20;
    /**
     * 通证转账
     * @param $num //转账数量，1个就是1，100个就是100
     * @param $address //接收地址
     * @param $contract_address  //token合约地址，适用于所有erc20的token
     */
    public function transfer($num, $address, $contract_address)
    {
        
        //xxxx为服务器运行的夸克区块链节点端口号，如果不是调用的当前服务器的节点，请填写所调用的服务器IP地址
        $url = "http://127.0.0.1:xxxx";
        $url_arr = parse_url($url);
        //实例化节点对象
        $qk_node = new QkNodeRPC($url_arr['host'], $url_arr['port']);
        $ERC20 = new ERC20($qk_node);
        $token = $ERC20->token($contract_address);
        //托管地址（发送方）
        $payer = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
        //转账
        $data = $token->encodedTransferData($address, $num);
        $transaction = $qk_node->personal()->transaction($payer, $contract_address)
            ->amount("0")
            ->data($data);
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
```

### 新建钱包
```php
<?php
$qk_node->personal()->newAccount("123456");

```