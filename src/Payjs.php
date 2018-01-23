<?php

namespace Payjs;

use GuzzleHttp\Client;

class Payjs
{
    private $AK, $SK, $error;
    private $notify_url = null;
    private $attach = null;

    /**
     * Payjs constructor.
     * @param $config
     */
    public function __construct($config = null)
    {
        if (!is_array($config)) return false;
        $this->init($config);
    }

    /*
     * 初始化
     */
    public function init($config)
    {
        if ($config['AK']) $this->AK = $config['AK'];
        if ($config['SK']) $this->SK = $config['SK'];
        return $this;
    }

    /*
     * 修改通知地址
     */
    public function notify($url)
    {
        $this->notify_url = $url;
        return $this;
    }


    /*
     * 增加附加返回的内容
     */
    public function attach($data)
    {
        $this->attach = $data;
        return $this;
    }

    /*
     * 返回错误信息
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * 扫描支付
     *
     * @param [type] $orderid 订单ID
     * @param integer $total_fee 费用，单位分
     * @param string $body 支付标题
     * @return void 数组
     */
    public function qrpay($orderid = null, $total_fee = 1, $body = '订单')
    {
        $url = 'https://payjs.cn/api/native';
        $data = array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid
        );
        return $this->post($url, $data);
    }

    /**
     * 收银台模式
     *
     * @param [type] $orderid 订单ID
     * @param integer $total_fee 费用，单位分
     * @param string $body  订单标题
     * @param string $callback_url  回跳地址，暂时不支持，如果填写会出错。以后支持的话就是支付完成要跳转的目标地址
     * @return void 返回跳转的代码，直接引用即可。如果返回的是false，那么就用error来判断错误
     */
    public function cashier($orderid = null, $total_fee = 1, $body = '订单', $callback_url = '')
    {
        $url = 'https://payjs.cn/api/cashier';
        $data = array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid,
            'callback_url' => $callback_url,
        );
        $r = $this->post($url, $data, false);
        if($r && strpos($r,'return_msg')){
            $r = json_decode($r,'true');
            $this->error = $r['return_msg'];
            return false;
        } 
        return $r;
    }

    /**
     * jspay，貌似已取消
     * @param [type] $orderid 订单ID
     * @param integer $total_fee 费用，单位分
     * @param string $body  订单标题
     * @param string $callback_url  回跳地址，暂时不支持，如果填写会出错。以后支持的话就是支付完成要跳转的目标地址
     * @return void
     */
    public function jspay($orderid = null, $total_fee = 1, $callback_url = '')
    {
        $url = 'https://payjs.cn/api/jspay';
        $data = array(
            'total_fee' => $total_fee,
            'body' => $body,
            'out_trade_no' => $orderid,
            'attach' => $attach,
            'callback_url' => $callback_url,
        );
        return $this->post($url, $data);
    }

    /**
     * 获取订单信息
     *
     * @param [type] $orderid
     * @return array 返回订单数组
     */
    public function get($orderid = null)
    {
        if (!$orderid) {
            $this->error = '必须指定payjs订单id';
            return false;
        }
        $url = 'https://payjs.cn/api/check';

        return $this->post($url, array(
            'payjs_order_id' => $orderid
        ));
    }

    /**
     * 生成指定长度的订单ID
     *
     * @param integer $length
     * @return void
     */
    protected static function orderid($length = 6)
    {
        $rand = rand(pow(10, ($length - 1)), pow(10, $length) - 1);
        return time() . $rand;
    }

    /**
     * 签名规则
     *
     * @param array $data
     * @return void
     */
    protected function sign(array $data)
    {
        ksort($data);
        return strtoupper(md5(urldecode(http_build_query($data)) . '&key=' . $this->SK));
    }

    /**
     * 提交数据
     *
     * @param [type] $url 目标地址
     * @param [type] $data  参数
     * @param boolean $decode 是否针对返回值json_decode 默认
     * @return void
     */
    protected function post($url, $data, $decode = true)
    {
        if (!isset($data['payjs_order_id'])) { //非查询状态下
            if ($this->notify_url) $data['notify_url'] = $this->notify_url;
            if ($this->attach) $data['attach'] = $this->attach;
            if (!isset($data['out_trade_no']) || !$data['out_trade_no']) $data['out_trade_no'] = self::orderid();
            if (!isset($data['mchid']) || !$data['mchid']) $data['mchid'] = $this->AK;
            if (!isset($data['notify_url']) || !$data['notify_url']) unset($data['notify_url']);
            if (!isset($data['attach']) || !$data['attach']) unset($data['attach']);
            if (!isset($data['callback_url']) || !$data['callback_url']) unset($data['callback_url']);
        }
        if (!isset($data['sign']) || !$data['sign']) $data['sign'] = $this->sign($data);
        $ql = \QL\QueryList::post($url, $data, array('allow_redirects' => false)); //禁止跳转
        $r = $ql->getHtml();
        if (!$r) {
            $this->error = "执行出现错误";
            return false;
        }
        return $decode ? json_decode($r, true) : $r;
    }
}