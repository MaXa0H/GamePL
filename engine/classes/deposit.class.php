<?php
if (!defined('gamepl_er6tybuniomop')) {
    header('Location:http://gamepl.ru');
    exit;
}
$true = true;

class deposit
{
    public static $payments = array(
        '1' => 'WebMoney',
        '2' => 'Robokassa',
        '3' => 'Яндекс. Деньги',
        '4' => 'Visa или MasterCard',
        '5' => 'UnitPay',
        '6' => 'SpryPay',
        '7' => 'NextPay',
        '8' => 'WAYtoPAY',
        '9' => 'ИНТЕРКАССА',
        '11' => 'QIWI'
    );

    public static function base()
    {
        global $conf, $title;
        if ($_POST['data']) {
            $deposit = (int)$_POST['data']['deposit'];
            $dep2 = (int)($deposit / $conf['curs']);
            if ($deposit) {
                if ($dep2 < 10 or $dep2 > 100000) {
                    api::result(l::t('Допустимое значение от ') . (10 * $conf['curs']) . l::t(' до ') . (100000 * $conf['curs']));
                } else {
                    if (api::$demo) {
                        api::result(l::t('Данная функция отключена в демо режиме.'));
                        return false;
                    }
                    if (api::ajax_f()) {

                        $d = array();
                        $d['r'] = l::t('Перенаправление на страницу оплаты');
                        if ($_POST['data']['tip'] == 1) {
                            $d['c'] = $conf['wmr1'];
                        }
                        if ($_POST['data']['tip'] == 3) {
                            $d['c'] = $conf['yandex1'];
                        }
                        if ($_POST['data']['tip'] == 4) {
                            $d['c'] = $conf['yandex1'];
                        }
                        if ($_POST['data']['tip'] == 5) {
                            $d['c'] = $conf['unitpay_key'];
                        }
                        if ($_POST['data']['tip'] == 6) {
                            $d['c'] = $conf['sp1'];
                        }
                        if ($_POST['data']['tip'] == 8) {
                            $d['c'] = $conf['waytopay'];
                        }
                        if ($_POST['data']['tip'] == 9) {
                            $d['c'] = $conf['interkassa'];
                        }
                        if ( $_POST[ 'data' ][ 'tip' ] == 11 ) {
                            $phone = trim($_POST['data']['phone']);
                            if (!$phone) {
                                api::result('Укажите номер телефона');
                                return;
                            }
                            if (strlen($phone)>15) {
                                api::result('Неверный номер телефона');
                                return;
                            }
                        }
                        die(json_encode($d));
                    } else {
                        if ($_POST['data']['tip'] == 2) {
                            $t = time();
                            $deposit = api::price($deposit / $conf['curs']);
                            $crc = md5($conf['rbc1'] . ":" . $deposit . ":" . $t . ":" . $conf['rbc2'] . ":Shp_id=" . api::info('id'));
                            if ($conf['rbc4']) {
                                $get = 'http://www.free-kassa.ru/merchant/cash.php';
                            } else {
                                $get = 'https://merchant.roboxchange.com/Index.aspx';
                            }
                            $get .= '?MerchantLogin=' . $conf['rbc1'];
                            $get .= '&Culture=ru';
                            $get .= '&InvId=' . $t;
                            $get .= '&OutSum=' . $deposit;
                            $get .= '&Shp_id=' . api::info('id');
                            $get .= '&SignatureValue=' . $crc;
                            $get .= '&Desc=' . l::t('Пополнение счета ') . api::info('id');
                            header('location:' . $get);
                            die();
                        }
                        if ($_POST['data']['tip'] == 7) {
                            $t = time();
                            $deposit = api::price($deposit / $conf['curs']);
                            $get = 'https://www.nextpay.ru/buy/index.php?command=show_product_form_ext';
                            $get .= '&product_id=' . $conf['nextpay'];
                            $get .= '&customer=' . api::info('id');
                            $get .= '&ext_order_cost=' . $deposit;
                            header('location:' . $get);
                            die();
                        }
                        if ( $_POST[ 'data' ][ 'tip' ] == 11 ) {
                            $phone = trim($_POST['data']['phone']);
                            if(!$phone){
                                api::result('Укажите номер телефона');
                                return;
                            }
                            if(strlen($phone)>15){
                                api::result('Неверный номер телефона');
                                return;
                            }
                            $t = time ();
                            $deposit = api::price($deposit);
                            $parameters = array(
                                'user' => 'tel:'.$phone,
                                'amount' => $deposit,
                                'ccy' => 'RUB',
                                'comment' => api::info ( 'id' ),
                                'pay_source' => 'qw',
                                'lifetime' => date('Y-m-d', strtotime(date('Y-m-d') . " + 1 DAY")) . "T00:00:00",
                                'prv_name' => $conf['title']
                            );
                            $ch = curl_init('https://api.qiwi.com/api/v2/prv/'.$conf['qiwi'].'/bills/'.$t);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                            curl_setopt($ch, CURLOPT_USERPWD, $conf['qiwi2'].':'.$conf['qiwi3']);
                            curl_setopt($ch, CURLOPT_HTTPHEADER,array (
                                "Accept: application/json"
                            ));
                            $httpResponse = curl_exec($ch);
                            if (!$httpResponse) {
                                api::result('Ошибка создания счета2');
                                return false;
                            }

                            $httpResponseAr = @json_decode($httpResponse);
                            if($httpResponseAr->response->result_code!==0){
                                api::result('Ошибка создания счета '.$httpResponseAr->response->result_code);
                                return false;
                            }
                            header ( 'location:https://w.qiwi.com/order/external/main.action?shop='.$conf['qiwi'].'&transaction='.$t.'&successUrl=http://'.$conf['domain'].'/users/deposit/success&failUrl=http://'.$conf['domain'].'/users/deposit/fail' );
                            die();
                        }
                        api::result(l::t('Платежная система не найдена'));
                    }
                }
            } else {
                api::result(l::t('Укажите сумму для оплаты'));
            }
        }
        tpl::load('users-deposit');
        $payments = null;
        if ($conf['wmr2']) {
            $payments .= self::list_add(1);
        }

        if ($conf['rbc3']) {
            $payments .= self::list_add(2);
        }
        if ($conf['yandex2']) {
            $payments .= self::list_add(3);
            $payments .= self::list_add(4);
        }
        if ($conf['unitpay']) {
            $payments .= self::list_add(5);
        }
        if ($conf['sp1']) {
            $payments .= self::list_add(6);
        }
        if ($conf['nextpay']) {
            $payments .= self::list_add(7);
        }
        if ($conf['waytopay']) {
            $payments .= self::list_add(8);
        }
        if ($conf['interkassa']) {
            $payments .= self::list_add(9);
        }
        if ($conf['qiwi']) {
            $payments .= self::list_add(11);
        }
        if (!$payments) {
            $payments = '<option value="0">' . l::t('- - Нет доступных способов оплаты - -') . '</option>';
        }
        tpl::set('{payments}', $payments);
        tpl::set('{rub}', $conf['curs']);
        tpl::compile('content');
        if (api::modal()) {
            die(tpl::result('content'));
        } else {
            api::nav('', l::t('Управление счетом'), '1');
            $title = l::t('Управление счетом');
        }
    }

    public static function list_add($num)
    {
        return '<option value="' . $num . '">' . self::$payments[$num] . '</option>';
    }

    public static function invite($sum, $id)
    {
        global $conf;
        db::q('SELECT invite FROM users where id="' . $id . '"');
        if (db::n() != 0) {
            $row = db::r();
            if ($row['invite'] != 0) {
                db::q('SELECT balance,invite_money,id FROM users where id="' . $row['invite'] . '"');
                $row = db::r();
                db::q('SELECT id FROM users where invite="' . $row['id'] . '" and signup="0" order by id desc');
                $priced = db::n();
                $pr = $conf['invite'];
                krsort($pr);
                foreach ($pr as $key => $val) {
                    if ($priced >= $key) {
                        $price = $val;
                        break;
                    }
                }
                $summ = (int)($sum / 100 * $price);
                if ($summ != 0) {
                    $amount = api::price($row['balance'] + $summ);
                    $amount2 = api::price($row['invite_money'] + $summ);
                    db::q("UPDATE users set balance='" . $amount . "', invite_money='" . $amount2 . "' where id='" . $row['id'] . "'");
                    $msg = l::t("[Партнерская программа] Пополнение счета ") . $row['id'] . l::t(" на сумму ") . $summ . " " . $conf['curs-name'];
                    api::log_balance($row['id'], $msg, '0', $summ);
                }
            }
        }
    }

    public static function getMd5Sign($method, $params, $secretKey)
    {
		ksort($params);
		unset($params['sign']);
		unset($params['signature']);
		array_push($params, $secretKey);
		array_unshift($params, $method);
		
		return hash('sha256', join('{up}', $params));
    }

    public static function result_unitpay()
    {
        global $conf;
        $key = $conf['unitpay'];
        if (!$key) {
            die;
        }
        if ($_REQUEST['method'] == 'check') {
            $d['result']['message'] = 'Запрос успешно обработан';
            die(json_encode($d));
        } elseif ($_REQUEST['method'] == 'pay') {
            $shp_id = (int)$_REQUEST['params']['account'];
            db::q('SELECT * FROM users where id="' . $shp_id . '"');
            if (db::n() == 0) {
                $d['error']['code'] = '-32000';
                $d['error']['message'] = l::t('Пользователь не найден');
                die(json_encode($d));
            } else {
                if ($_REQUEST['params']['signature'] == self::getMd5Sign($_REQUEST['method'],$_REQUEST['params'], $key)) {
                    $row = db::r();
                    $amount2 = (int)$_GET['params']['sum'];
                    $amount2 = $amount2 * $conf['curs'];
                    $amount = api::price($row['balance'] + $amount2);
                    db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
                    $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - UnitPay';
                    api::log_balance($shp_id, $msg, '0', $amount2);
                    $d['result']['message'] = 'Запрос успешно обработан';
                    self::invite($amount2, $shp_id);
                    api::inc('sms');
                    $msg = "Платеж: " . $amount2;
                    if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                        sms::send($conf['sms_phone_admin'], $msg);
                    }
                    die(json_encode($d));
                } else {
                    $d['error']['code'] = '-32000';
                    $d['error']['message'] = l::t('Неверная подпись');
                    die(json_encode($d));
                }
            }
        } else {
            $d['error']['code'] = '-32000';
            $d['error']['message'] = l::t('Критическая ошибка');
            die(json_encode($d));
        }
    }


    public static function result_nextpay()
    {
        global $conf;
        $key = $conf['nextpay'];
        if (!$key) {
            die;
        }
        $shp_id = (int)$_REQUEST['customer'];

        db::q('SELECT * FROM users where id="' . $shp_id . '"');
        if (db::n() == 0) {
            die('no_login');
        } else {
            if (!$_REQUEST['hash']) {
                die('ok');
            } else {
                $row = db::r();
                $test = $_REQUEST['test'];
                $product_id = $_REQUEST['product_id'];
                $order_id = $_REQUEST['order_id'];
                $currency = $_REQUEST['currency'];
                $cost_general = $_REQUEST['cost_general'];
                $cost = $_REQUEST['cost'];
                $profit = $_REQUEST['profit'];
                $commission = $_REQUEST['commission'];
                $hash = $_REQUEST['hash'];
                if ($test == 1) {
                    die('error');
                }
                if ($product_id != $conf['nextpay']) {
                    die('error');
                }
                $sign = $test .
                    $product_id .
                    $order_id .
                    $currency .
                    $cost_general .
                    $cost .
                    $profit .
                    $commission .
                    $conf['nextpay_key'];
                if ($hash != sha1($sign)) {
                    die('error');
                }
                $amount2 = $profit;
                $amount2 = $amount2 * $conf['curs'];
                $amount = api::price($row['balance'] + $amount2);
                db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
                $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - NextPay';
                api::log_balance($shp_id, $msg, '0', $amount2);
                self::invite($amount2, $shp_id);
                api::inc('sms');
                $msg = l::t("Платеж: ") . $amount2;
                if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                    sms::send($conf['sms_phone_admin'], $msg);
                }
                die("ok");
            }
        }
    }


    public static function result_wm()
    {
        global $conf;
        $LMI_SECRET_KEY = $conf['wmr2'];
        if (!$LMI_SECRET_KEY) {
            die;
        }
        $LMI_PAYEE_PURSE = $conf['wmr1'];
        if ($_REQUEST['LMI_PREREQUEST'] == 1) {
            die("YES");
        } else {
            $chkstring = $LMI_PAYEE_PURSE .
                $_REQUEST['LMI_PAYMENT_AMOUNT'] .
                $_REQUEST['LMI_PAYMENT_NO'] .
                $_REQUEST['LMI_MODE'] .
                $_REQUEST['LMI_SYS_INVS_NO'] .
                $_REQUEST['LMI_SYS_TRANS_NO'] .
                $_REQUEST['LMI_SYS_TRANS_DATE'] .
                $LMI_SECRET_KEY .
                $_REQUEST['LMI_PAYER_PURSE'] .
                $_REQUEST['LMI_PAYER_WM'];
            $md5sum = strtoupper(hash('sha256', $chkstring));
            if ($_POST['LMI_HASH'] == $md5sum) {
                $shp_id = (int)$_REQUEST['FIELD_id'];
                db::q('SELECT * FROM users where id="' . $shp_id . '"');
                if (db::n() == 0) {
                    echo 'error';
                    exit;
                } else {
                    $row = db::r();
                    $amount2 = (int)$_REQUEST['LMI_PAYMENT_AMOUNT'];
                    $amount2 = $amount2 * $conf['curs'];
                    $amount = api::price($row['balance'] + $amount2);
                    db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
                    $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - WebMoney';
                    api::log_balance($shp_id, $msg, '0', $amount2);
                    self::invite($amount2, $shp_id);
                    api::inc('sms');
                    $msg = l::t("Платеж: ") . $amount2;
                    if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                        sms::send($conf['sms_phone_admin'], $msg);
                    }
                    die("YES");
                }
            } else {
                echo 'error';
                exit;
            }
        }
    }

    public static function result_robokassa()
    {
        global $conf;
        $mrh_pass2 = $conf['rbc3'];
        if (!$mrh_pass2) {
            die;
        }
        $out_summ = $_POST["OutSum"];
        $inv_id = (int)$_POST["InvId"];
        $shp_id = (int)$_POST["Shp_id"];
        $crc = $_POST["SignatureValue"];
        $crc = strtoupper($crc);
        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_id=$shp_id"));
        if ($my_crc == $crc) {
            $amount2 = api::price($out_summ);
            db::q('SELECT balance FROM users where id="' . $shp_id . '"');
            $row = db::r();
            $amount2 = api::price($amount2 * $conf['curs']);
            $amount = api::price($row['balance'] + $amount2);
            db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
            $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - Robokassa';
            api::log_balance($shp_id, $msg, '0', $amount2);
            self::invite($amount2, $shp_id);
            api::inc('sms');
            $msg = "Платеж: " . $amount2;
            if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                sms::send($conf['sms_phone_admin'], $msg);
            }
            echo "OK$inv_id\n";
            exit;
        } else {
            echo "bad sign\n";
            exit;
        }
    }

    public static function result_qiwi()
    {
        global $conf;
        $mrh_pass2 = $conf['qiwi4'];
        if (!$mrh_pass2) {
            die;
        }
        $out_summ = $_POST[ "amount" ];
        $shp_id = (int) $_POST[ "comment" ];
        $requestParams = $_POST;
        ksort($requestParams);
        $signData = implode('|', $requestParams);
        $my_crc = base64_encode(hash_hmac('sha1', $signData, $mrh_pass2, $raw_output=TRUE));
        $crc = trim($_SERVER['HTTP_X_API_SIGNATURE']);
        if ( $my_crc == $crc ) {
            if($_POST['status']!="paid"){
                header('Content-Type: text/xml');
                echo '<?xml version="1.0"?><result><result_code>0</result_code></result>';
                exit;
            }
            $amount2 = api::price($out_summ);
            db::q('SELECT balance FROM users where id="' . $shp_id . '"');
            $row = db::r();
            $amount2 = api::price($amount2 * $conf['curs']);
            $amount = api::price($row['balance'] + $amount2);
            db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
            $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - QIWI';
            api::log_balance($shp_id, $msg, '0', $amount2);
            self::invite($amount2, $shp_id);
            api::inc('sms');
            $msg = "Платеж: " . $amount2;
            if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                sms::send($conf['sms_phone_admin'], $msg);
            }
            header('Content-Type: text/xml');
            echo '<?xml version="1.0"?><result><result_code>0</result_code></result>';
            exit;
        } else {
            header('Content-Type: text/xml');
            echo '<?xml version="1.0"?><result><result_code>1</result_code></result>';
            exit;
        }
    }

    public static function result_waytopay()
    {
        global $conf;
        $mrh_pass2 = $conf['waytopay'];
        $mrh_secret_key = $conf['waytopay2'];
        if (!$mrh_pass2) {
            echo "ERROR_no conf\n";
            die;
        }

        $inv_id = (int)$_REQUEST["wInvId"];
        $is_sets = (int)$_REQUEST["wIsTest"];


        $out_summ = $_REQUEST["wOutSum"];

        $shp_id = (int)$_POST['account'];
        $hash = md5("$mrh_pass2:$out_summ:$inv_id:$mrh_secret_key");

        if ($hash === (string)$_REQUEST["wSignature"]) {
            if ($is_sets == 1) {
                echo "OK_$inv_id";
                exit;
            }
            $amount2 = api::price($out_summ);
            db::q('SELECT balance FROM users where id="' . $shp_id . '"');
            $row = db::r();
            $amount2 = api::price($amount2 * $conf['curs']);
            $amount = api::price($row['balance'] + $amount2);
            db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
            $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - waytopay';
            api::log_balance($shp_id, $msg, '0', $amount2);
            self::invite($amount2, $shp_id);
            api::inc('sms');
            $msg = l::t("Платеж: ") . $amount2;
            if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                sms::send($conf['sms_phone_admin'], $msg);
            }
            echo "OK_$inv_id";
            exit;
        } else {
            echo "ERROR_bad sign\n";
            exit;
        }
    }


    public static function result_yandex()
    {
        global $conf;
        $mrh_pass2 = $conf['yandex2'];
        if (!$mrh_pass2) {
            die;
        }
        $out_summ = $_POST['amount'];
        $shp_id = (int)$_POST['label'];
        $hash = sha1($_POST['notification_type'] . '&' . $_POST['operation_id'] . '&' . $_POST['amount'] . '&' . $_POST['currency'] . '&' . $_POST['datetime'] . '&' . $_POST['sender'] . '&' . $_POST['codepro'] . '&' . $mrh_pass2 . '&' . $_POST['label']);
        if ($hash === $_POST['sha1_hash']) {
            $amount2 = (int)$out_summ;
            db::q('SELECT balance FROM users where id="' . $shp_id . '"');
           if($row = db::r()){
               $amount2 = $amount2 * $conf['curs'];
               $amount = api::price($row['balance'] + $amount2);
               db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
               $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - Яндекс. Деньги';
               api::log_balance($shp_id, $msg, '0', $amount2);
               self::invite($amount2, $shp_id);
               api::inc('sms');
               $msg = "Платеж: " . $amount2;
               if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                   sms::send($conf['sms_phone_admin'], $msg);
               }
               exit;
           }
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
            exit;
        }
    }

    public static function result_sp()
    {
        global $conf;

        $spQueryFields = array('spPaymentId', 'spShopId', 'spShopPaymentId', 'spBalanceAmount', 'spAmount', 'spCurrency', 'spCustomerEmail', 'spPurpose', 'spPaymentSystemId', 'spPaymentSystemAmount', 'spPaymentSystemPaymentId', 'spEnrollDateTime', 'spHashString', 'spBalanceCurrency');
        foreach ($spQueryFields as $spFieldName) {
            if (!isset($_REQUEST[$spFieldName])) {
                exit("error в запросе с данными платежа отсутствует один из параметров");
            }
        }
        $yourSecretKeyString = $conf['sp2'];
        if (!$yourSecretKeyString) {
            exit("error критическая ошибка");
        }
        $localHashString = md5($_REQUEST['spPaymentId'] . $_REQUEST['spShopId'] . $_REQUEST['spShopPaymentId'] . $_REQUEST['spBalanceAmount'] . $_REQUEST['spAmount'] . $_REQUEST['spCurrency'] . $_REQUEST['spCustomerEmail'] . $_REQUEST['spPurpose'] . $_REQUEST['spPaymentSystemId'] . $_REQUEST['spPaymentSystemAmount'] . $_REQUEST['spPaymentSystemPaymentId'] . $_REQUEST['spEnrollDateTime'] . $yourSecretKeyString);
        if ($localHashString == $_REQUEST['spHashString']) {
            if ($_REQUEST['spCurrency'] != "rur") {
                exit("error критическая ошибка");
            } else {
                if ($_REQUEST['spBalanceCurrency'] != "rur") {
                    exit("error критическая ошибка");
                } else {
                    $shp_id = (int)$_REQUEST['spUserDataid'];
                    $amount2 = (int)$_REQUEST['spBalanceAmount'];
                    db::q('SELECT balance FROM users where id="' . $shp_id . '"');
                    if (db::n() != 1) {
                        exit("error критическая ошибка");
                    } else {
                        $row = db::r();
                        $amount2 = $amount2 * $conf['curs'];
                        $amount = api::price($row['balance'] + $amount2);
                        db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
                        $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - SpryPay';
                        api::log_balance($shp_id, $msg, '0', $amount2);
                        self::invite($amount2, $shp_id);
                        api::inc('sms');
                        $msg = l::t("Платеж: ") . $amount2;
                        if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                            sms::send($conf['sms_phone_admin'], $msg);
                        }
                        exit("ok");
                    }
                }
            }
        } else {
            exit("error не совпали подписи");
        }
    }

    public static function result_interkassa()
    {
        api::nav("/users/profile/" . api::info('id'), l::t("Профиль"));
        api::nav('', 'Счет', '1');
        api::success(l::t('Ваш платеж находится на стадии завершения.'));
    }


    public static function result_interkassa2()
    {
        global $conf;


        $mrh_pass2 = $conf['interkassa2'];
        if (!$mrh_pass2) {
            header($_SERVER['SERVER_PROTOCOL'] . 'error', true, 100);
            exit('error1');
        }
        if ($_POST['ik_co_id'] != $conf['interkassa']) {
            header($_SERVER['SERVER_PROTOCOL'] . 'E_PROTOCOL_NOT_EXIST', true, 100);
            exit('error2');
        }
        $out_summ = api::price($_POST['ik_am']);
        $shp_id = (int)$_POST['ik_x_account'];

        $dataSet = $_POST;
        unset($dataSet['ik_sign']);
        ksort($dataSet, SORT_STRING);
        array_push($dataSet, $mrh_pass2);
        $signString = implode(':', $dataSet);
        $hash = base64_encode(md5($signString, true));
        if ($hash === $_POST['ik_sign']) {
            if ($_POST['ik_inv_st'] != "success") {
                header($_SERVER['SERVER_PROTOCOL'] . 'E_PROTOCOL_NOT_EXIST', true, 100);
                exit('error3');
            }
            $amount2 = $out_summ;
            db::q('SELECT balance FROM users where id="' . $shp_id . '"');
            $row = db::r();
            $amount2 = $amount2 * $conf['curs'];
            $amount = api::price($row['balance'] + $amount2);
            db::q("UPDATE users set balance='" . $amount . "' where id='" . $shp_id . "'");
            $msg = l::t("Пополнение счета ") . $shp_id . l::t(" на сумму ") . $amount2 . " " . $conf['curs-name'] . ' - ИНТЕРКАССА';
            api::log_balance($shp_id, $msg, '0', $amount2);
            self::invite($amount2, $shp_id);
            api::inc('sms');
            $msg = l::t("Платеж: ") . $amount2;
            if ($conf['sms_payment'] && $conf['sms_phone_admin']) {
                sms::send($conf['sms_phone_admin'], $msg);
            }
            exit($conf['interkassa3']);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . 'E_PROTOCOL_NOT_EXIST', true, 100);
            exit('error4');
        }
    }

    public static function fail()
    {
        api::nav("/users/profile/" . api::info('id'), l::t("Профиль"));
        api::nav('', 'Счет', '1');
        api::error(l::t('Не удалось пополнить баланс'));
    }

    public static function success()
    {
        api::nav("/users/profile/" . api::info('id'), l::t("Профиль"));
        api::nav('', 'Счет', '1');
        api::success(l::t('Счет успешно пополнен'));
    }

}

?>