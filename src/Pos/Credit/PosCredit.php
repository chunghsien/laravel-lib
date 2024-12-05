<?php

namespace Chopin\Pos\Credit;

abstract class PosCredit
{
    /**
     * @var int 輸出總長度
     */
    protected $trascation_length = 400;

    /**
     * @var int 資料格式，交易別長度
     */
    protected $trans_type_length;

    /**
     * @var int 資料格式，交易金額長度
     */
    protected $trans_amount_length;

    /**
     * @var int 資料格式，交易金額位置；從0開始，如果文件是從1開始要記得減1
     */
    protected $trans_amount_offset;


    /**
     * @var int  資料格式，分期期數長度
     */
    protected $period_length;

    /**
     * @var int  資料格式，分期期數位置；從0開始，如果文件是從1開始要記得減1
     */
    protected $period_offset;


    /**
     * @var string  資料格式
     */
    protected $trascation_format;

    /**
     * @var string 簡介標籤(安裝列表抓這個跟類別完整名稱) 
     */
    protected $introduction;

    /**
     * @param string $trans_type 交易方式(01:付款, 02:退款)
     * @param number $trans_amount 交易金額
     */
    public function __construct($trans_type, $trans_amount)
    {
        $this->trascation_format = str_repeat(' ', $this->trascation_length);
        $offset1 = $this->trans_type_offset;
        $length1 = $this->trascation_length - $this->trans_type_length;
        $this->trascation_format = $trans_type .
            substr($this->trascation_format, $offset1, $length1);
            $trans_amount = (string)$trans_amount . '00';
        $trans_amount = str_pad(
            $trans_amount,
            $this->trans_amount_length,
            '0',
            STR_PAD_LEFT
        );
        $part1Offset = intval($this->trans_amount_offset) + 1;
        $part1 = substr($this->trascation_format, 0, $part1Offset);
        $part2 = $trans_amount;
        $part3Offset = $this->trans_amount_offset + $this->trans_amount_length;
        $part3Length = $this->trascation_length - $part3Offset;
        $part3 = substr($this->trascation_format, $part3Offset, $part3Length);
        $this->trascation_format = $part1.$part2.$part3;
        if(false !== array_search($trans_type, ['03', '04'])) {

        }
    }

    /**
     * 
     * 
     * @param string $flow 'in'|'out'
     * @throws \ErrorException
     * @return string
     */
    protected function getTransFile($flow)
    {
        $configPath = storage_path('pos-credit/pos.dat');
        if (!is_file($configPath)) {
            throw new \ErrorException('找不到刷卡機的設定檔');
        }
        $contents = file_get_contents($configPath);
        $split = explode(PHP_EOL, $contents);
        $indexArr = ['in' => 5, 'out' => 6];
        return $split[$indexArr[$flow];
    }

    public function putInDat(): bool|int
    {
        $filename = $this->getTransFile('in');
        $pathname = storage_path('pos-credit' . '/' . $filename);
        return file_put_contents(
            $pathname,
            $this->trascation_format
        );
    }

    public function runECR()
    {
        $pathname = storage_path('pos-credit/ecr.exe');
        if (!is_file($pathname)) {
            throw new \ErrorException('尚未安裝任何信用卡模組');
        }
        exec($pathname);
    }

    public function resolveOut()
    {
        $filename = $this->getTransFile('out');
        $pathname = storage_path('pos-credit' . '/' . $filename);
        $content = file_get_contents($pathname);
        $trans_type = substr($content, 0, 2);
        $host_id = substr($content, 2, 2);
        $invoice_no = substr($content, 4, 5);
        $card_no = substr($content, 10, 19);
        $trans_amount = substr($content, 33, 12);
        $trans_date = substr($content, 45, 6);
        $trans_time = substr($content, 51, 6);
        $approval_no = substr($content, 57, 9);
        $ecr_response_code = substr($content, 78, 4);
        $edc_terminal_id = substr($content, 82, 8);
        $refernce_no = substr($content, 90, 12);
        $card_type = substr($content, 137, 1);
        //分期付款
        if($trans_type == '03'){

        }
    }
}
