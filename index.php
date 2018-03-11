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
        <label for="Wallet">Transaction id: </label>
        <input id="Wallet" type="text" name="trans_id">
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
function GetInfoWallet($wallet, $period_mins = null, $wallet_sender = null, $wallet_getter = null, $trans_id = null) {

    //Get Account
    $account = getAccount($wallet);
    if (ValidationOnError($account) == true) {

        //Acoount and Balance
        $balance_account = GetAccountBalancePrizm($account);
        echo "<h1>Баланс:</h1>" . $balance_account . "<br><br><br>";
        $balance_account_para = GetAccountBalancePrizmPara($account);
        var_dump($account);
        echo "<br><br>";
        echo "<h1>Баланс Пара:</h1>" . $balance_account_para . "<br><br><br>";


        //Go to function Transactions with Params
        $transactions_account_params = GetTransactionsParams($wallet, $period_mins, $wallet_sender, $wallet_getter, $trans_id);


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
* @param    $wallet_geter      string    The code wallet account on which the payment will be made getter
* @return
*/
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
function GetInfoWalletAllTransactions($wallet, $transaction_account) {

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

    if (is_array($array['transactions'])){
        foreach ($array['transactions'] as $value){
            if(isset($value['senderRS'])):         $simply_array[$cnt]['senderRS']      = $value['senderRS'];         endif;//Sender Wallet                                string(26)
            if(isset($value['timestamp'])):        $simply_array[$cnt]['timestamp']     = $value['timestamp'];        endif;//TimeStamp Transaction                        int
            if(isset($value['type'])):             $simply_array[$cnt]['type']          = $value['type'];             endif;//Type Transaction ((+)type=1) ((-)type=0)     int(1)
            if(isset($value['recipientRS'])):      $simply_array[$cnt]['recipientRS']   = $value['recipientRS'];      endif;//Wallet Getter                                string(16)
            if(isset($value['transaction'])):      $simply_array[$cnt]['transaction']   = $value['transaction'];      endif;//Wallet Setter                                int
            $cnt++;
        }
    }

    return $simply_array;
}

function ValidatorRequests() {

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

function TimeStampPrizm ($timestamp) {
    $epoch_begin = 1486768980000 - 500; //Value on Site Prizm
    $date = floor(($timestamp * 1000 + $epoch_begin) / 1000);
    return $date;
}

function GetTransactionsParams($wallet, $period_mins, $wallet_sender, $wallet_getter, $trans_id) {
    if (!empty($wallet)) {

        //Set Params
        if ($period_mins != null && $period_mins > 0) {
            $params['timestamp'] = $period_mins;
        };

        if ($wallet_sender != null && $wallet_sender > "") {
            $params['senderRS'] = $wallet_sender;
        }

        if ($wallet_getter != null && $wallet_getter > "") {
            $params['recipientRS'] = $wallet_getter;
        }

        if ($trans_id != null && $trans_id > 0) {
            $params['transaction'] = $trans_id;
        }
        //End Set Params

        if (is_array($params) && sizeof($params) > 0) {
            //Go to get Transactions with Params
            $transactions = GetTransactions($wallet, $params);
        } else {
            //Go to get All Transactions
            $transactions = GetTransactions($wallet);
        }

        //var_dump($transactions); // Json


    } else {
        return false;
    }
}

function GetTransactions($wallet, $params = null) {
    if (!empty($wallet)) {
        $transaction_array = array();
        $cnt = 0;
        if (!empty($params) && $params != null && sizeof($params) > 0) {
            //Get Slice Transactions with Params
            do {
                $query = http_build_query([
                    'requestType' => 'getBlockchainTransactions',
                    'account' => $wallet,
                    'firstIndex' => $cnt * $GLOBALS['index'],
                    'lastIndex' => ($cnt * $GLOBALS['index']) + $GLOBALS['index']
                ]);
                $transaction_tmp = CurlJsonGet($GLOBALS['url_default_request'], $query);

                $transaction_check = CheckParamsTransactions($transaction_tmp, $params); //Check on Isset Any Params

                //Get Slice Array for Last equally any param
                $last_array = ValidateRequestTransaction(($transaction_check));
                if (sizeof($last_array) > 0 || $last_array == true) {
                    $transaction_tmp = $transaction_check;
                    //var_dump("test");
                }


                //If Valid False die send Request and no-repeat
                if (ValidateRequest($transaction_tmp)) {
                    if (ValidateRequestTransaction($transaction_tmp) === true) {
                        $transaction_array[] = $transaction_tmp;
                    } elseif (sizeof(ValidateRequestTransaction($transaction_tmp)) > 0) {
                        $transaction_array[] = $transaction_tmp;
                    }
                }

                $cnt++;
            } while (ValidateRequestTransaction($transaction_tmp) === true && ValidateRequest($transaction_tmp) === true);

                //Return Transaction_array
                return $transaction_array;

        } else {//Else If Empty Params
            //Get All Transactions
            do {
                $query = http_build_query([
                    'requestType' => 'getBlockchainTransactions',
                    'account'     => $wallet,
                    'firstIndex'  => $cnt * $GLOBALS['index'],
                    'lastIndex'   => ($cnt * $GLOBALS['index']) + $GLOBALS['index']

                ]);
               // var_dump($query);
               // echo "<br><br>";
                $transaction_tmp = CurlJsonGet($GLOBALS['url_default_request'], $query);
                //If Valid False die send Request and no-repeat

                if (ValidateRequest($transaction_tmp)) {
                    if (ValidateRequestTransaction($transaction_tmp) === true) {
                        $transaction_array[] = $transaction_tmp;
                    } elseif (sizeof(ValidateRequestTransaction($transaction_tmp)) > 0) {
                        //var_dump("test");
                        $transaction_array[] = $transaction_tmp;
                    }
                }

                $cnt++;
            } while (ValidateRequestTransaction($transaction_tmp) === true && ValidateRequest($transaction_tmp) === true);

            //Return Transaction_array
            return $transaction_array;


        }//End (!empty($params) && $params != null && sizeof($params) > 0)

    } else { // If empty Wallet
        //Error
        return false;
    }
}

function ValidateRequest($json) {
    if (!is_array(json_decode($json, true))) {
        return false;
    } else {
        return true;
    }
}

function ValidateRequestTransaction($json) {
    $array = json_decode($json, true);
    if (is_array($array)) {
        if (sizeof($array['transactions']) > 0) {
            if (sizeof($array['transactions']) < 100) {
                return json_encode($array); //Last Url //Fix one more Request
            }
            return true;
        }
    } else {
        return false;
    }
}

function CheckParamsTransactions($json, $params) {
    $array_transaction = json_decode($json, true);
    if (sizeof($array_transaction) > 0 && sizeof($params) > 0) {
     //   var_dump($array_transaction['transactions']);
        foreach ($array_transaction['transactions'] as $key => $transaction) {
            foreach ($params as $key_param => $value) {

                if (isset($transaction[$key_param])) {
                    //Check TimeStamp
                    if ($key_param=="timestamp") {
                        $time_minutes_site = TimeStampPrizm($transaction[$key_param]); //Time Stamp Prizm convert Server timestamp in minutes
                        $time_minutes_server = time() - ($value * 60);                 //Value = minutes param * 60sec / and minus now_time - minutes param
                        if ($time_minutes_server < $time_minutes_site) {
                            $last_equally = $key;
                            //var_dump($key);
                        }
                    } else {
                        //Check other Params
                        //var_dump("test");
                        if ($transaction[$key_param] == $value) {
                            $last_equally = $key;
                        }
                    }
                }

                //Break
                if ($last_equally) {
                    break 2;
                }

            }

        }//End Foreach

        //Return
        if ($last_equally) {
            //Send Slide Array
            $array_transaction['transactions'] = array_slice($array_transaction['transactions'], 0 , $last_equally+1);
            //var_dump($array_transaction);
            return json_encode($array_transaction);

        } else {
            //Not Found params
            return false;
        }


    } else {
        //Not array or Empty $params
        return false;
    }
}


?>

</body>
</html>