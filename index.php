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
    <form method="post" action="index.php">
        <h3>Paymant</h3>
        <label for="Wallet">Wallet : </label>
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
$url_default_request       = 'http://tech.prizm-space.com/prizm?';  //Default url fore Request
$index                     = 100;                                   // Count index page for one request
$timeout                   = 1;                                     //Now Don't use if will be error 503 insert to after:  $transaction_tmp = CurlJsonGet($GLOBALS['url_default_request'], $query);
//Get Post Value && Test API
if (isset($_REQUEST)) {
    if (isset($_REQUEST['wallet']) && !empty($_REQUEST['wallet'])) {
        //Get Transactions or Info
        $tmp = GetInfoWallet($_REQUEST['wallet'], $_REQUEST['time_mins'], $_REQUEST['wallet_sender'], $_REQUEST['wallet_getter'], $_REQUEST['trans_id']);

    }//Check Wallet
} //Check On isset Request

/*
* GetInfoWallet
*
* @param    $wallet           string    The code wallet account on site http://tech.prizm-space.com
* @param    $period_mins      int       Get Transaction for the last n min
* @param    $wallet_sender    string    The code wallet account from which the payment will be made sender on wallet
* @param    $wallet_geter     string    The code wallet account on which the payment will be made getter
* @param    $trans_id         int       The code transaction_id
* @return   $result           json      The return encode array all search transactions with params
*/
function GetInfoWallet($wallet, $period_mins = null, $wallet_sender = null, $wallet_getter = null, $trans_id = null) {

    //Get Account
    $account = getAccount($wallet);                                            //Json array account
    if (ValidationOnError($account) == true) {                                 //Validate on error get account

        //Acoount and Balance
        $balance_account = GetAccountBalancePrizm($account);                   //String
        //Echo format balance
        echo "<h1>Баланс:</h1>" . $balance_account . "<br><br><br>";           //String
        //Get balance para
        $balance_account_para = GetAccountBalancePrizmPara($account);          //Json
        var_dump($account); // Echo json array
        echo "<br><br>";
        echo "<h1>Баланс Пара:</h1>" . $balance_account_para . "<br><br><br>"; // Echo json array


        //Go to function Transactions with Params
        $transactions_account_params = GetTransactionsParams($wallet, $period_mins, $wallet_sender, $wallet_getter, $trans_id);
        return $transactions_account_params; //Return Json array or if(have erorrs: false)

    } else {
        $erorrs = GetErrors($account); // Function for get Errors you can add your own functionality to function
        return false;                  // Default return if error
    }
}

/*
* GetInfoWalletTransactions
*
* @param    $wallet           string      The code wallet account on site http://tech.prizm-space.com
* @return   $result           json_array  Full info for One Transaction
*/
function GetInfoWalletTransactions($wallet) {
    if (!empty($wallet)) {
        //Create params for url
        $query = http_build_query([
            'requestType' => 'getBlockchainTransactions',
            'account'     => $wallet
        ]);
        //Send Request for Get Account Wallet Transactions
        $InfoWalletTransactions = CurlJsonGet($GLOBALS['url_default_request'], $query);
        return $InfoWalletTransactions; //Json array
    } else {
        $result['error'] = "Error Wallet is empty"; //Error Description
        $result = json_encode($result);             //Encode error to array
        return $result;                             //Json array: Return array with false
    }

}

/*
* GetAccount function get info wallet account on site http://tech.prizm-space.com
*
* @param    $wallet           string      The code wallet account on site http://tech.prizm-space.com
* @return   $result           json_array  Json array with info for account get
*/
function GetAccount($wallet) {
    if (!empty($wallet)) {
        //Create params for url
        $query = http_build_query([
            'requestType' => 'getAccount',
            'account'     => $wallet
        ]);
        //Send Request for Get Account
        $account = CurlJsonGet($GLOBALS['url_default_request'], $query);
        return $account;                                //Json array
    } else {
        $result['error'] = "Error Wallet is empty";     //Error Description
        $result = json_encode($result);                 //Encode error to array
        return $result;                                 //Json array: Return array with false
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
* @param    $json     string    json array
* @return   $result   bool      Status Validate
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
* @param    $json      json_array    Array with errors
* @return   $result                  Custom Function (Now None)
*/
function GetErrors($json) {

}

/*
* GetAccountBalancePrizm
*
* @param    $account   json_array    Array account info
* @return   $result    string        String balance
*/
function GetAccountBalancePrizm($account) {

    $array = json_decode($account, true);
    return $array['balanceNQT'];
}

/*
* GetAccountBalancePrizmPara
*
* @param    $account   json_array    Array account info
* @return   $result    json_array    Array with info GetPara
*/
function GetAccountBalancePrizmPara ($account) {
    if (!empty($account)) {
        $array = json_decode($account, true);
        $account_id = $array['account'];
        //Create param for url
        $query = http_build_query([
            'requestType' => 'getPara',
            'account'     => $account_id
        ]);
        //Send Request for Get Account
        $para = CurlJsonGet($GLOBALS['url_default_request'], $query); //Request for get ParaAccount
        return $para;                                                 //Return

    } else {
        $result['error'] = "Error account is empty";        //Error Description
        $result = json_encode($result);                     //Encode Error
        return $result;                                     //Return array with error
    }


}

/*
* TimeStampPrizm
*
* @param    $timestamp  json_array    Array account info
* @return   $result     timestamp     TimeStamp format with static value on site prizma
*/
function TimeStampPrizm ($timestamp) {
    $epoch_begin = 1486768980000 - 500;                             //Value on Site Prizm
    $date = floor(($timestamp * 1000 + $epoch_begin) / 1000); //Function on site write on js
    return $date;
}

/*
* GetTransactionsParams MiddleWear function
*
* @param    $wallet         string         Account wallet
* @param    $period_mins    int            Minutes
* @param    $wallet_sender  string         Account wallet sender
* @param    $wallet_getter  string         Account wallet getter
* @param    $trans_id       int            Transaction_id operation
* @return   $result         json_array     Ready Array with transactions or False
*/
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

        return $transactions; // Json array


    } else {
        return false; // if empty wallet
    }
}

/*
* GetTransactions
*
* @param    $wallet         string              Account wallet
* @param    $params         array               Params for Search
* @return   $result         json_array/bool     Ready Array with all transactions or False(if have error)
*/
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
                //var_dump($query);
                //var_dump($cnt);
                //Get Slice Array for Last equally any param
                if (ValidateRequest($transaction_check)) {
                    if (ValidateRequestTransaction($transaction_check) === true) {
                        $transaction_tmp = $transaction_check;
                    } elseif (sizeof(ValidateRequestTransaction($transaction_check)) > 0) {
                        $transaction_tmp = $transaction_check;
                    }
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
                //var_dump($cnt);
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

/*
* ValidateRequest
*
* @param    $json           json_array     Json Request for Validate on isset array json
* @return   $result         bool           Status Validate
*/
function ValidateRequest($json) {
    if (!is_array(json_decode($json, true))) {
        return false;
    } else {
        return true;
    }
}

/*
* ValidateRequestTransaction
*
* @param    $json           json_array          Json array with transaction
* @return   $result         bool/json_array     Return array if this last page with transactions or return true (not last) or false array don't have transactions in request
*/
function ValidateRequestTransaction($json) {
    $array = json_decode($json, true);
    if (is_array($array)) {
        if (sizeof($array['transactions']) > 0) {
            if (sizeof($array['transactions']) < $GLOBALS['index']) {
                return json_encode($array); //Last Url //Fix one more Request
            }
            return true;
        }
    } else {
        return false;
    }
}

/*
* CheckParamsTransactions
*
* @param    $json           string                Account wallet
* @param    $params         array                 Minutes
* @return   $result         json_array/bool       If find param return slice array or false(if have error)
*/
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
                       // echo gmdate("Y/m/d H:i:s",$time_minutes_server)."---".gmdate("Y/m/d H:i:s",$time_minutes_site)." UNIX ". $time_minutes_server . "---" . $time_minutes_site . "<br><br>";
                        if ($time_minutes_server > $time_minutes_site) {
                             $last_equally = $key;
                            //var_dump($key);
                        }
                    } else {
                        //Check other Params
                        //var_dump("test");
                        if ($transaction[$key_param] == $value) {
                         //   var_dump("test");
                            if ($key_param="transaction") {
                                //Don't add last transaction
                                $last_equally = $key-1;
                            } else {
                                $last_equally = $key;
                            }

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