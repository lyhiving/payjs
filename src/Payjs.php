<?php
namespace Payjs;

class Payjs
{
    private $AK, $SK, $error;
    private $notify_url = null;
    private $attach = null;

    /**
     * Payjs constructor.
     * @param $config
     */
    public function __construct($config = null){
        if(!is_array($config)) return false;
        $this->init($config);
    }


    public function init($config){
        if($config['AK'])   $this->AK = $config['AK'];
        if($config['SK'])   $this->SK = $config['SK'];
        return $this;
    }

    /*
     * 修改通知地址
     */
    public function notify($url){
        $this->notify_url = $url;
        return $this;
    }


    /*
     * 增加附加返回的内容
     */
    public function attach($data){
        $this->attach = $data;
        return $this;
    }

    /*
    * 返回错误信息
    */
    public function error(){
        return $this->error;
    }

    /*
     * 扫码支付
     */
    public function qrpay($orderid = null, $total_fee = 1, $body = '订单'){
        $url = 'https://payjs.cn/api/native';
        $data =  array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid,
            'attach'    => $attach,
            'callback_url'    => $callback_url,
        );
        return $this->post($url,$data);
    }

    /*
     * 收银台模式
     */
    public function cashier($orderid = null,$total_fee = 1,$body = '订单', $callback_url=''){
        $url = 'https://payjs.cn/api/cashier';
        $data =  array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid,
            'attach'    => $attach,
            'callback_url'    => $callback_url,
        ); 
        return $this->post($url,$data);
    }

    /*
     * JSpay
     * 注：无测试条件
     */
    public function jspay($orderid = null,$total_fee = 1, $callback_url=''){
        $url = 'https://payjs.cn/api/jspay';
        $data =  array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid,
            'attach'    => $attach,
            'callback_url'    => $callback_url,
        ); 
        return $this->post($url,$data);
    }

    /*
     * 订单查询
     */
    public function get($orderid = null){
        if (!$orderid){
            $this->error = '必须指定payjs订单id';
            return false;
        }
        $url = 'https://payjs.cn/api/check';

        return $this->post($url,array(
            'payjs_order_id' => $orderid
        ));
    }

    /*
     * 生成随机数字
     */
    protected static function orderid($length = 6){
        $rand =  rand(pow(10,($length - 1)), pow(10,$length) -1);
        return time() . $rand;
    }

    /*
     * 签名
     */
    protected function sign(array $data) {
        ksort($data);
        return strtoupper(md5(urldecode(http_build_query($data)).'&key='.$this->SK));
    }

    /*
     * 预处理数据
     */
    protected function post($url,$data){
        if(!$data['sign'])   $data['sign']=$this->sign($data);
        if(!isset($data['payjs_order_id'])){ //非查询状态下
            if($this->notify_url) $data['notify_url'] = $this->notify_url;
            if($this->attach) $data['attach'] = $this->attach;
            if(!$data['out_trade_no']) $data['out_trade_no'] =self::orderid();
            if(!$data['mchid'])  $data['mchid'] = $this->AK;
            if(!$data['notify_url']) unset($data['notify_url']);
            if(!$data['attach']) unset($data['attach']);
            if(!$data['callback_url']) unset($data['callback_url']);
        }

        return $this->curl($Url,$Arrry);
    }

    /*
     * curl
    */
    protected function curl($Url,$Arrry){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Arrry);
        $cexecute = curl_exec($ch);
        curl_close($ch);
        return $cexecute;
    }
}
