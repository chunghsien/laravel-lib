<?php

namespace Chopin\Pos\Credit\MegaBank;

class S920
{
    const TRASCATION_LENGTH = 400;

    const TRANS_TYPE_LENGTH = 2;

    const TRANS_AMOUNT_LENGTH = 12;

    const TRANS_AMOUNT_OFFSET = 34;

    /**
     * @var string 
     */
    protected $trascation_format;

    /**
     * @var string
     */
    protected $introduction = "【兆豐】S920";

    /**
     * @param string $trans_type 交易方式(01:付款, 02:退款)
     * @param number $trans_amount 交易金額
     */
    public function __construct($trans_type, $trans_amount)
    {
        $this->trascation_format = str_repeat(' ', self::TRASCATION_LENGTH);
        $this->trascation_format = $trans_type .
            substr($this->trascation_format, 2, 398);
        $trans_amount = (string)$trans_amount . '00';
        $trans_amount = str_pad(
            $trans_amount,
            self::TRANS_AMOUNT_LENGTH,
            '0',
            STR_PAD_LEFT
        );
        $this->trascation_format = substr($this->trascation_format, 0, 33) .
            $trans_amount .
            substr($this->trascation_format, 45, 355);
    }

    public function putInDat(): bool|int
    {
        return file_put_contents(__DIR__ . '/in.dat', $this->trascation_format);
    }
}
