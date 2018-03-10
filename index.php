<?
require 'multi_curl.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div class="GetInfoWallet">
    <form method="post" action="index.php">
        <h3>GetDataInfoWallet</h3>
        <label for="Wallet">Wallet: </label>
        <input id="Wallet" type="text" name="wallet">

        <h4>Params</h4>

        <label for="Wallet">Period Minutes: </label>
        <input id="Wallet" type="text" name="time_mins">
        <br><br>
        <label for="Wallet">Wallet Sender: </label>
        <input id="Wallet" type="text" name="wallet_sender">
        <br><br>
        <label for="Wallet">Wallet Getter: </label>
        <input id="Wallet" type="text" name="wallet_getter">
        <br><br>
        <input type="submit" value="Отправить">
    </form>

</div>

<?php
$url_default_request       = 'http://tech.prizm-space.com/prizm?';
$index                     = 100;
$timeout                   = 1;
$chunk_url_count           = 3;
$count_attempt_for_request = 3;
//Get Post Value && Test API
if (isset($_REQUEST)) {
    if (isset($_REQUEST['wallet']) && !empty($_REQUEST['wallet'])) {

        $tmp = GetInfoWallet($_REQUEST['wallet'], $_REQUEST['time_mins'], $_REQUEST['wallet_sender'], $_REQUEST['wallet_getter']);

    }//Check Wallet
} //Check On isset Request

/*
* GetInfoWallet
*
* @param    $wallet           string    The code wallet account on site http://tech.prizm-space.com
* @param    $period_mins      int       Get Transaction for the last n min
* @param    $wallet_sender    string    The code wallet account from which the payment will be made sender on wallet
* @param    $wallet_geter     string    The code wallet account on which the payment will be made getter
* @return   $wallet_geter     string    The code wallet account on which the payment will be made getter
*/
//Functions
function GetInfoWallet($wallet, $period_mins = null, $wallet_sender = null, $wallet_getter = null) {

    $account = getAccount($wallet);
    if (ValidationOnError($account) == true) {
        $balance_account = GetAccountBalancePrizm($account);
        echo "<h1>Баланс:</h1>".$balance_account."<br><br><br>";
        $balance_account_para = GetAccountBalancePrizmPara($account);
        var_dump($account); echo "<br><br>";
        echo "<h1>Баланс Пара:</h1>".$balance_account_para."<br><br><br>";
        $transaction_account = GetInfoWalletTransactions($wallet);
        if (ValidationOnError($transaction_account) == true) {
            $all_transactions_account = GetInfoWalletAllTransactions($wallet, $transaction_account);
            $valid_all_transactions_account = ValidTransactionsArray($all_transactions_account);

            if ($valid_all_transactions_account == true) {
                //Sorted Minutes
                if ($period_mins !=null && $period_mins > 0) {
                    $all_transactions_account = SortTransactionOnMin($all_transactions_account, $period_mins);

                    $valid_all_transactions_account = ValidTransactionsArray($all_transactions_account);
                    if ($valid_all_transactions_account == false):
                        $erorrs = GetErrors($all_transactions_account);
                        return $erorrs;
                    endif;
                        //var_dump($all_transactions_account);
                }

                //Sorted Sender
                if ($wallet_sender !=null && $wallet_sender > "") {
                    $all_transactions_account = SortTransactionOnSender($all_transactions_account, $wallet_sender);

                    $valid_all_transactions_account = ValidTransactionsArray($all_transactions_account);
                    if ($valid_all_transactions_account == false):
                        $erorrs = GetErrors($all_transactions_account);
                        return $erorrs;
                    endif;
                }
                //Sorted getter
                if ($wallet_getter !=null && $wallet_getter > "") {
                    $all_transactions_account = SortTransactionOnGetter($all_transactions_account, $wallet_getter);

                    $valid_all_transactions_account = ValidTransactionsArray($all_transactions_account);
                    if ($valid_all_transactions_account == false):
                        $erorrs = GetErrors($all_transactions_account);
                        return $erorrs;
                    endif;
                }
                echo "<h1>Транзакции данного кошелька:</h1><br><br><br>";


                if(is_array($all_transactions_account)):
                    $result_transactions = array();
                    foreach ($all_transactions_account as $all_transaction_account):
                        $result_transactions = array_merge($result_transactions, FormatSimplyInfoTransaction($all_transaction_account));
                    endforeach;
                else:
                    $result_transactions = FormatSimplyInfoTransaction($all_transactions_account);
                endif;

                 var_dump($result_transactions);

            } else {
                $erorrs = GetErrorArray($all_transactions_account);
                return false;
            }
        } else {
            $erorrs = GetErrors($transaction_account);
            return false;
        }
    } else {
        $erorrs = GetErrors($account);
        return false;
    }
}

/*
* GetInfoWalletTransactions
*
* @param    $wallet           string    The code wallet account on site http://tech.prizm-space.com
* @param    $period_mins      int       Get Transaction for the last n min
* @param    $wallet_sender    string    The code wallet account from which the payment will be made sender on wallet
* @param   $wallet_geter      string    The code wallet account on which the payment will be made getter
* @return
*/
//Functions
function GetInfoWalletTransactions($wallet) {
    if (!empty($wallet)) {
        $query = http_build_query([
            'requestType' => 'getBlockchainTransactions',
            'account'     => $wallet
        ]);
        //Send Request for Get Account Wallet Transactions
        $InfoWalletTransactions = CurlJsonGet($GLOBALS['url_default_request'], $query);
        return $InfoWalletTransactions;
    } else {
        $result['error'] = "Error Wallet is empty";
        $result = json_encode($result);
        return $result;
    }

}

/*
* GetInfoWalletTransactions
*
* @param    $wallet           string    The code wallet account on site http://tech.prizm-space.com
* @param    $period_mins      int       Get Transaction for the last n min
* @param    $wallet_sender    string    The code wallet account from which the payment will be made sender on wallet
* @param   $wallet_geter      string    The code wallet account on which the payment will be made getter
* @return
*/
//Functions
function GetInfoWalletAllTransactions($wallet, $transaction_account) {
    if (!empty($wallet)) {
        $array     = json_decode($transaction_account, true);
        $countUrls = ceil($array['transactions'][0]['deadline'] / $GLOBALS['index']); //Float
        $query = array(
            'url'         => $GLOBALS['url_default_request'],
            'requestType' => 'getBlockchainTransactions',
            'account'     => $wallet,
            'firstIndex'  => "",
            'lastIndex'   => "",
            'index'       => $GLOBALS['index'],
            'countUrls'   => $countUrls

        );
        if ($countUrls == 1)  {
            return $transaction_account; // IF Count Urls 1 Return current last 100 transaction back
        } elseif ($countUrls > 1) {
            $urls = CreateUrlsForParse($query);

            return MultiCurlJsonGet($urls); // If count Urls > 1 Return all transaction //Return Array
        }
    } else {
        $result['error'] = "Error Wallet is empty";
        $result = json_encode($result);
        return $result;
    }

    //var_dump($account);
}

/*
* GetAccount function get info wallet account on site http://tech.prizm-space.com
*
* @param    $wallet           string    The code wallet account on site http://tech.prizm-space.com
*/
function GetAccount($wallet) {
    if (!empty($wallet)) {
        $query = http_build_query([
            'requestType' => 'getAccount',
            'account'     => $wallet
        ]);
        //Send Request for Get Account
        $account = CurlJsonGet($GLOBALS['url_default_request'], $query);
        return $account;
    } else {
        $result['error'] = "Error Wallet is empty";
        $result = json_encode($result);
        return $result;
    }

}


/*
* cURL request Get Request
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @param    $req      string    Request type. Ex. 'POST', 'GET' or 'PUT'
* @param    $data     array     Array of data to be POSTed
* @return   $result             HTTP resonse
*/
//Send&Get Request //One chanel
function CurlJsonGet($url, $data = '') {
    if (!empty($url)) {
    //Check on isset Array Params and not Empty
        if ($data != null && !empty($data)) {
            $data_string = json_encode($data);

            $ch = curl_init($url.$data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json'
            ));

            $result = curl_exec($ch);
            //var_dump($result);
            return $result;
        }/*#IF check Data*/ else { //Or send error in array and return
            $result['error'] = "Error Get Params";
            $result = json_encode($result);
            return $result;
        }
    }/*#Else check Data*/ else {
        $result['error'] = "Error Get Url";
        $result = json_encode($result);
        return $result;
    }
}//#IF check url
/*
* ValidationOnError
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @return   $result             HTTP resonse
*/
function ValidationOnError($json) {
    $array = json_decode($json, true);
    if (isset($array['error']) || isset($array['errorCode'])){
        return false;
    } else {
        return true;
    }
}
/*
* GetErrors
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @return   $result             HTTP resonse
*/
function GetErrors($json) {

}
/*
* FormatSimplyInfoTransaction
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @return   $result             HTTP resonse
*/
function FormatSimplyInfoTransaction($json) {
    $array = json_decode($json, true);
  //  var_dump($array);

    $simply_array = array();
    $cnt = 0;

    if (is_array($array['transactions'])):
        foreach ($array['transactions'] as $value):
            if(isset($value['senderRS'])):  $simply_array[$cnt]['senderRS']      = $value['senderRS'];  endif;//Sender Wallet                                string(26s)
            if(isset($value['timestamp'])): $simply_array[$cnt]['timestamp']     = $value['timestamp']; endif;//TimeStamp Transaction                        int
            if(isset($value['type'])):      $simply_array[$cnt]['type']          = $value['type'];      endif;//Type Transaction ((+)type=1) ((-)type=0)     int(1)
            if(isset($value['recipientRS'])):      $simply_array[$cnt]['recipientRS']   = $value['recipientRS'];      endif;//Type Transaction ((+)type=1) ((-)type=0)     int(1)
            if(isset($value['transaction'])):      $simply_array[$cnt]['transaction']   = $value['transaction'];      endif;//Type Transaction ((+)type=1) ((-)type=0)     int(1)

            $cnt++;
        endforeach;
    endif;

    return $simply_array;
}
/*
* MultiCurlJsonGet
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @return   $result             HTTP resonse
*/
function MultiCurlJsonGet($urls) {

    $urls_chunk = array_chunk($urls, $GLOBALS['chunk_url_count']);
    $array_response = array();

    foreach ($urls_chunk as $part_url) {

        $chm = new CURL;

        foreach ($part_url as $url) {
            $chm->new_handle($url, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER  => array('Content-Type: application/json'),
                CURLOPT_RETURNTRANSFER  => true
            ]);
        }
        $response = $chm->exec();
        $cnt = 0;

        $response_tmp = $response;


        foreach ($response_tmp as $response_result):
            $response_result = json_decode($response_result,true);

            while (!is_array($response_result) && $cnt < $GLOBALS['chunk_url_count']) {
                sleep($GLOBALS['timeout']);
                $response = $chm->exec();
                $cnt++;
            }

            if (!is_array($response_result)) {
                $json['error'] = "Error request:". $chm->info;
                $json = json_encode($json);
                return $json;
            }

            $response_tmp_2 = $response; //Check after Repeat Request
            foreach ($response_tmp_2 as $response_result_2):
                if(isset($response_result_2['transactions']) && empty($response_result_2['transactions'])) {
                    break 3; //Stop Send Request (Bug with count Pages)
                }
            endforeach;
        endforeach;

        $array_response = array_merge($array_response, $response);

    }
    return $array_response;
}


/*
* CreateUrlsForParse
*
* @param    $url      string    The url to post to 'theurlyouneedtosendto.com/m/admin'/something'
* @return   $result             HTTP resonse
*/
function CreateUrlsForParse($query) {
    if (!empty($query) && is_array($query)) {

        $array_urls  = array();
        $pages       = $query['countUrls'];
        $url         = $query['url'];
        $wallet      = $query['account'];
        $requestType = $query['requestType'];

        for ($i=0; $i < $pages; $i++) {
            $params_query = http_build_query([
                'requestType' => $requestType,
                'account'     => $wallet,
                'firstIndex'  => $i*$GLOBALS['index'],
                'lastIndex'   => ($i+1)*$GLOBALS['index']   ,
            ]);

            $array_urls[] = $url.$params_query;
        }//EndFor
        return $array_urls;
    }/*EndIf*/ else {
        return false;
    }
}

function ValidatorRequests($array_json) {
    return true;
}

function GetAccountBalancePrizm($account) {

    $array = json_decode($account, true);
    return $array['balanceNQT'];
}

function GetAccountBalancePrizmPara ($account) {
    if (!empty($account)) {
        $array = json_decode($account, true);
        $account_id = $array['account'];
        $query = http_build_query([
            'requestType' => 'getPara',
            'account'     => $account_id
        ]);
        //Send Request for Get Account
        $para = CurlJsonGet($GLOBALS['url_default_request'], $query);
        return $para;

    } else {
        $result['error'] = "Error account is empty";
        $result = json_encode($result);
        return $result;
    }


}

function SortTransactionOnMin ($transactions, $min) {
    if (!empty($transactions)) {
        if (!empty($min)) {
            if(is_array($transactions)) {
                $new_array = array();
                $cnt_=0;
                foreach ($transactions as $transaction) {
                    $arrays = json_decode($transaction, true);
                    $new_array[] = $arrays;
                    $cnt = 0;
                    foreach ($arrays as $key => $value) {
                        if ($key = 'transactions') {
                           for ($i=0; $i < count($value); $i++) {
                               if ($value[$i]['timestamp']) {
                                $time_now = time();
                                $time_transaction = TimeStampPrizm($value[$i]['timestamp']);
                                $diff_time = $time_now - $time_transaction;
                                $diff_time_min = $diff_time / 60;
                               // var_dump($diff_time_min);
                                if ($diff_time_min > $min) {
                                    unset($new_array[$cnt_][$key][$i]);
                                }

                               }
                           }
                        }
                        $cnt++;
                    }
                    $cnt_++;
                }

            } else {
                $new_array = array();
                $cnt_=0;
                $arrays = json_decode($transactions, true);
                $new_array[] = $arrays;
                $cnt = 0;
                foreach ($arrays as $key => $value) {
                    if ($key = 'transactions') {
                        for ($i=0; $i < count($value); $i++) {
                            if ($value[$i]['timestamp']) {
                                $time_now = time();
                                $time_transaction = TimeStampPrizm($value[$i]['timestamp']);
                                $diff_time = $time_now - $time_transaction;
                                $diff_time_min = $diff_time / 60;
                                // var_dump($diff_time_min);
                                if ($diff_time_min > $min) {
                                    unset($new_array[$cnt_][$key][$i]);
                                }

                            }
                        }
                    }
                    $cnt++;
                }
                $cnt_++;
            }
            //Encode
            if(is_array($new_array)):
              foreach ($new_array as $key => $value):
                  $new_array[$key] = json_encode($value);
              endforeach;
              $result = $new_array;
            else:
                $result = json_encode($new_array);
            endif;
            return $result;
            //
        } else {
            $json['error'] = "Error min empty!";
            $json = json_encode($json);
            return $json;
        }
    } else {
        $json['error'] = "Error transactions empty!";
        $json = json_encode($json);
        return $json;
    }
    return false;
}

function SortTransactionOnSender ($transactions, $wallet_sender) {
    if (!empty($transactions)) {
        if (!empty($wallet_sender)) {
            if (is_array($transactions)) {
                $new_array = array();
                $cnt_=0;
                foreach ($transactions as $transaction) {
                    $arrays = json_decode($transaction, true);
                    $new_array[] = $arrays;
                    $cnt = 0;
                    foreach ($arrays as $key => $value) {
                        if ($key = 'transactions') {
                            for ($i=0; $i < count($value); $i++) {
                                if ($value[$i]['senderRS']) {

                                    if ($value[$i]['senderRS'] != $wallet_sender) {
                                        unset($new_array[$cnt_][$key][$i]);
                                    }

                                }
                            }
                        }
                        $cnt++;
                    }
                    $cnt_++;
                }

            } else {
                $new_array = array();
                $cnt_=0;
                $arrays = json_decode($transactions, true);
                $new_array[] = $arrays;
                $cnt = 0;
                foreach ($arrays as $key => $value) {
                    if ($key = 'transactions') {
                        for ($i=0; $i < count($value); $i++) {
                            if ($value[$i]['senderRS']) {

                                if ($value[$i]['senderRS'] != $wallet_sender) {
                                    unset($new_array[$cnt_][$key][$i]);
                                }

                            }
                        }
                    }
                    $cnt++;
                }
                $cnt_++;
            }
            //Encode
            if(is_array($new_array)):
                foreach ($new_array as $key => $value):
                    $new_array[$key] = json_encode($value);
                endforeach;
                $result = $new_array;
            else:
                $result = json_encode($new_array);
            endif;
            return $result;
            //

        } else {
            $json['error'] = "Error wallet sender empty!";
            $json = json_encode($json);
            return $json;
        }
    } else {
        $json['error'] = "Error transactions empty!";
        $json = json_encode($json);
        return $json;
    }
    return false;
}

function SortTransactionOnGetter ($transactions, $wallet_getter) {
    if (!empty($transactions)) {
        if (!empty($wallet_getter)) {
            if (is_array($transactions)) {
                $new_array = array();
                $cnt_=0;
                foreach ($transactions as $transaction) {
                    $arrays = json_decode($transaction, true);
                    $new_array[] = $arrays;
                    $cnt = 0;
                    foreach ($arrays as $key => $value) {
                        if ($key = 'transactions') {
                            for ($i=0; $i < count($value); $i++) {
                                if ($value[$i]['recipientRS']) {
                                    if ($value[$i]['recipientRS'] != $wallet_getter) {
                                        unset($new_array[$cnt_][$key][$i]);
                                    }
                                }
                            }
                        }
                        $cnt++;
                    }
                    $cnt_++;
                }

            } else {
                $new_array = array();
                $cnt_=0;
                $arrays = json_decode($transactions, true);
                $new_array[] = $arrays;
                $cnt = 0;
                foreach ($arrays as $key => $value) {
                    if ($key = 'transactions') {
                        for ($i=0; $i < count($value); $i++) {
                            if ($value[$i]['recipientRS']) {

                                if ($value[$i]['recipientRS'] != $wallet_getter) {
                                    unset($new_array[$cnt_][$key][$i]);
                                }

                            }
                        }
                    }
                    $cnt++;
                }
                $cnt_++;
            }
            //Encode
            if(is_array($new_array)):
                foreach ($new_array as $key => $value):
                    $new_array[$key] = json_encode($value);
                endforeach;
                $result = $new_array;
            else:
                $result = json_encode($new_array);
            endif;
            return $result;
            //

        } else {
            $json['error'] = "Error wallet sender empty!";
            $json = json_encode($json);
            return $json;
        }
    } else {
        $json['error'] = "Error transactions empty!";
        $json = json_encode($json);
        return $json;
    }
    return false;
}{

}

function TimeStampPrizm ($timestamp) {
    $epoch_begin = 1486768980000 - 500; //Value on Site Prizm
    $date = floor(($timestamp * 1000 + $epoch_begin) / 1000);
    return $date;
}

function ValidTransactionsArray($transactions)
{
    if (!is_array($transactions)) {
        $valid_all_transactions_account = ValidationOnError($transactions);
        return $valid_all_transactions_account;
    } else {
        $valid_all_transactions_account = false; // Default
        foreach ($transactions as $all_transaction_account):
            $valid_all_transactions_account = ValidationOnError($all_transaction_account);
            if ($valid_all_transactions_account == false):
                return false;
            endif;
        endforeach;
        return $valid_all_transactions_account;
    }
}

function GetErrorArray($array) {
    foreach ($array as $errors_trans) {
        $errors[] = GetErrors($errors_trans);
    }
    return $errors;
}
?>

</body>
</html>