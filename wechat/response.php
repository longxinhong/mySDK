<?php 
/*
 * 次方法不能直接运行，需要对应的类库和函数库支持
 */
function notifu($data){
    if (!empty($data['postStr'])) {
        $payment = get_payment($data['code']);
        $postdata = json_decode(json_encode(simplexml_load_string($data['postStr'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $wxsign = $postdata['sign'];
        unset($postdata['sign']);
    
        foreach ($postdata as $k => $v) {
            $Parameters[$k] = $v;
        }
    
        ksort($Parameters);
        $buff = '';
    
        foreach ($Parameters as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }
    
        if (0 < strlen($buff)) {
            $String = substr($buff, 0, strlen($buff) - 1);
        }
    
        $String = $String . '&key=' . $payment['wxpay_key'];
        $String = md5($String);
        $sign = strtoupper($String);
    
        if ($wxsign == $sign) {
            if ($postdata['result_code'] == 'SUCCESS') {
                $out_trade_no = explode('O', $postdata['out_trade_no']);
                $order_sn = $out_trade_no[1];
                order_paid($order_sn, 2);
                model()->table('pay_log')->data(array('openid' => $postdata['openid'], 'transid' => $postdata['transaction_id']))->where(array('log_id' => $order_sn))->update();
            }
    
            $returndata['return_code'] = 'SUCCESS';
        }
        else {
            $returndata['return_code'] = 'FAIL';
            $returndata['return_msg'] = '签名失败';
        }
    }
    else {
        $returndata['return_code'] = 'FAIL';
        $returndata['return_msg'] = '无数据返回';
    }
    
    $xml = '<xml>';
    
    foreach ($returndata as $key => $val) {
        if (is_numeric($val)) {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        }
        else {
            $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
    }
    
    $xml .= '</xml>';
    echo $xml;
    exit();
}
?>