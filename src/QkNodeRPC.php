<?php
/**
 * Created by PhpStorm.
 * Date: 2018/12/12
 * Time: 18:28
 */
namespace quarkblockchain;

class QkNodeRPC extends \EthereumRPC\EthereumRPC
{
    /** @var QKI */
    private $qki;

    /**
     * EthereumRPC constructor.
     * @param string $host
     * @param int|null $port
     */
    public function __construct(string $host, ?int $port = null, ?bool $ssl = true)
    {
        parent::__construct($host,$port,$ssl);
        $this->qki = new QKI($this);
    }

    public function QKI()
    {
        return $this->qki;
    }
}