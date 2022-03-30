<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ShurjopayController extends Controller
{   
    public function checkout(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $info = array( 
            'currency' =>$_GET['resellerCurrency'],
            'amount' =>$_GET['sellingcurrencyamount'], 
            'order_id' =>$_GET['transid'],
            'spkey'=> $_GET['spkey'], 
            'discsount_amount' =>0, 
            'disc_percent' =>0,
            'client_ip' =>$ip,
            'customer_name' =>$_GET['name'],
            'customer_phone' =>$_GET['telNo'], 
            'email' =>$_GET['emailAddr'], 
            'customer_address' =>$_GET['address1'],
            'customer_city' =>$_GET['city'], 
            'customer_state' =>$_GET['state'], 
            'customer_postcode' =>$_GET['zip'], 
            'customer_country' =>$_GET['country'],
            'tx_id'=> $_GET['spkey'] . '_' .$_GET['transid'].rand(100,1000),
            'transactiontype'=> $_GET['transactiontype'],
            'userid'=>$_GET['userid'],
            'checksum' => $_GET['checksum'],
            'paymenttypeid' => $_GET['paymenttypeid'],
            'key' =>$_GET['apikey'],
            'value1'=>$_GET['checksum'],
            'value2'=>$_GET['apikey'],
            'description' => $_GET['description'],
            'invoiceids' => $_GET['invoiceids'],
            'usertype' => $_GET['usertype'],
            'redirecturl' =>$_GET['redirecturl'],
             );
        
        // echo"<pre>";
        // print_r($info);
        // die();
        // // env('MERCHANT_RETURN_URL');
        // env('MERCHANT_CANCEL_URL');

        $flag=0;
        $info['prefix']=env('MERCHANT_PREFIX');
        $info['return_url']=env('MERCHANT_RETURN_URL');
        $info['cancel_url']=env('MERCHANT_CANCEL_URL');

        if(!isset($info['prefix']))
        {
            $flag=1;
            echo 'Please provide Prefix';
        }
        if(!isset($info['amount']))
        {
            $flag=2;
            echo 'Please provide amount';

        }
        if(!isset($info['order_id']))
        {
            $flag=3;
            echo 'Please provide order id';

        }
        if(!isset($info['customer_name']))
        {
            $flag=4;
            echo 'Please provide customer name';

        }
        if(!isset($info['customer_phone']))
        {
            $flag=5;
            echo 'Please provide customer phone';

        }
        if(!isset($info['customer_address']))
        {
            $flag=6;
            echo 'Please provide customer address';

        }
        if($flag==0)
        {
            $response = $this->getUrl($info);

            $arr = json_decode($response);
            if(!empty($arr->checkout_url))
            {
                $url = ($arr->checkout_url);
                return redirect($url);
            }
            else{
                return $response;
            }

        }
    }
    private function getToken() {
        $userExists=false;
        if(!empty(env('MERCHANT_USERNAME')) && !empty(env('MERCHANT_PASSWORD')))
        {
            $user= env('MERCHANT_USERNAME');
            $pass= env('MERCHANT_PASSWORD');
            $userExists=true;
        }


        if($userExists)
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => env('ENGINE_URL').'/api/get_token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                                            "username": "'.$user.'",
                                            "password": "'.$pass.'"
                                        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        }
        else
        {
            $response="Please enter valid username and password";
        }



        return $response;
    }
    private function getUrl($info) {
        $response=$this->getToken();
        $arr=json_decode($response);
        if(!empty($arr->token))
        {
            $tok=($arr->token);
            $s_id=($arr->store_id);
            $info2=array(
                'token'=>$tok,
                'store_id'=>$s_id);
            $final_array=array_merge($info2, $info);
            $bodyJson=json_encode($final_array);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('ENGINE_URL').'/api/secret-pay',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$bodyJson,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                echo "cURL Error #:" . $err;
                exit();
            }else{
                return $response;
            }

        }
        else{
            return $response;
        }

    }
    public function verify() {
       $order_id = $_GET['order_id'];
        $order_id = array(
            'order_id' => $order_id);
        $order_id=json_encode($order_id);
        $response=$this->getToken();
        $arr=json_decode($response);
        if(!empty($arr->token))
        {
            $tok=($arr->token);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('ENGINE_URL').'/api/verification',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$order_id ,
                CURLOPT_HTTPHEADER => array(
                    'Authorization:Bearer '.$tok,
                    'Content-Type: application/json'
                ),
            ));          
            $response = curl_exec($curl);
            curl_close($curl);






//this return the verifies data to the srs panel
            $arr=json_decode($response,true);
            $arr = array_shift($arr);           
            $return_url="https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet";
            $amount=$arr['amount'];
            $transId=$arr['customer_order_id'];                 
            $rkey = rand(); 
            $sp_code=$arr['sp_code'];     
            if($sp_code=="1000")
            {
                $status="Y";
            }
            else
            {
                $status="N";
            }      
            $key=$arr['value2'];             
            $tr = $transId."|".$amount."|".$amount."|".$status."|".$rkey."|".$key;
            $str=md5($tr);            
            $form='<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
                               <form id="submit" name="f1" action="'.$return_url.'">
                               <input type="hidden"  name="transid" value="'.$transId.'">
                               <input type="hidden"  name="status" value="'.$status.'">
                               <input type="hidden"   name="rkey" value="'.$rkey.'">
                               <input type="hidden"  name="checksum" value="'.$str.'">
                               <input type="hidden"   name="sellingamount" value="'.$amount.'">
                               <input type="hidden"   name="accountingamount" value="'.$amount.'">
                               </form><script>$("#submit").submit();</script>';
                               echo $form;          
                              
        }
    }   
}
















 // public function ReturnPaySpv2($data)
    //     {
           //   echo"<pre>";             
            // print_r( $str);
            // die();   
        //     echo"<pre>";             
            // print_r($str);
            // die();   
            // transid=Payment-Test-1&status=Y&rkey=157354379&checksum=0e23bab05424ed318cbe7781a5c311d2&sellingamount=100.0&accountingamount=100.0
         //  echo"<pre>";             
            // print_r($arr);
            // die(); 
          //  echo"<pre>";             
            // print_r($form);
            // die();
            // return redirect()->away('https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet?$amount=Payment-Test-1&status=Y&rkey=157354379&checksum=0e23bab05424ed318cbe7781a5c311d2&sellingamount=100.0&accountingamount=100.0');
            // echo"<pre>";             
            // print_r($amount);
            // die();  
            // {"id":8128,"order_id":"NOK6242d57a8d639","currency":"BDT","amount":100,"payable_amount":100,"discsount_amount":0,"disc_percent":0,"usd_amt":0,"usd_rate":0,"card_holder_name":null,"card_number":null,"phone_no":"01111111111","bank_trx_id":"6242d582","invoice_no":"NOK6242d57a8d639","bank_status":"Success","customer_order_id":"Payment-Test-1","sp_code":1000,"sp_massage":"Success","name":"Demo","email":null,"address":"Faridpur","city":"FARIDPUR","value1":null,"value2":null,"value3":null,"value4":null,"transaction_status":null,"method":"Nagad","date_time":"2022-03-29 15:46:42"}
        //               echo"<pre>";
        //    print_r($response);
        //    $toClient= json_encode(array(
        //        'amount'=>$arr['amount'],
        //        'transid'=>'Payment-Test-1',
              
        //     ));
            // $forUrl= urlencode($toClient);
            
            // $clintResponseUrl='https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet?'.$forUrl;
            // return redirect()->away($clintResponseUrl);
            // echo"<pre>";           
            // $forUrl= urlencode($toClient);
            // print_r($forUrl);
            // die();
            // return redirect()->away('https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet?');
            // $forUrl= urldecode($toClient);
        //     $clintResponseUrl='cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet'.$forUrl;
        // //    die();
        //              return redirect()->away($clintResponseUrl);//'https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet?transid=Payment-Test-1&status=Y&rkey=157354379&checksum=0e23bab05424ed318cbe7781a5c311d2&sellingamount=100.0&accountingamount=100.0');
                    //  return redirect()->away('https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet?transid=Payment-Test-1&status=Y&rkey=157354379&checksum=0e23bab05424ed318cbe7781a5c311d2&sellingamount=100.0&accountingamount=100.0');
        //    echo"<pre>";
        //    print_r($data);
        //    die();
          //  return redirect()->to('https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet')->with('data', $data);
        // $return_url="https://cp.resellcamp.com/servlet/TestCustomPaymentAuthCompletedServlet";
          
        // $response = Http::post($return_url, [
        //     'transId' => 'Steve',
        //     'status' => 'Network Administrator',
        // ]);

        // dd($response);
        //    $form='<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
            //                 <form id="submit" name="f1" action="'.$return_url.'">
            //                 <input type="hidden" name="transid" value="'.$transId.'">
            //                 <input type="hidden" name="status" value="'.$status.'">
            //                 <input type="hidden" name="rkey" value="'.$rkey.'">
            //                 <input type="hidden" name="checksum" value="'.md5($str).'">
            //                 <input type="hidden" name="sellingamount" value="'.$amount.'">
            //                 <input type="hidden" name="accountingamount" value="'.$amount.'">
            //             </form><script>$("#submit").submit();</script>';
        //  }

//     public function ReturnPaySpv2($data)
//     {
//         $get_payinfo = PaymentTreckerModel::where('txid', '=', $tx_id)->first();
//         $pay = PaymentTreckerModel::find($get_payinfo->id);
//         $merchant_id=$get_payinfo->merchant_id;
//         $return_url=$get_payinfo->return_url;
//         $get_uid = MerchantModel::where('id', '=', $merchant_id)->first();
//         srand((double)microtime()*1000000);
//         $rkey = rand();
//         $transId=  $data->order_id;// trim($get_payinfo->order_id);
//         $sellingCurrencyAmount=(float)$amount;
//         $accountingCurrencyAmount=(float)$amount;
//         $key=trim($get_uid->ref_code);
//         $checksum="";        
//         $pay->sp_code = $sp_code;
//         $pay->bank_status = $bank_status;
//         if($sp_code=="1000")
//         {
//             $status="Y";
//         }
//         else
//         {
//             $status="N";
//         }
//         http::post();
//         //$checksum=$this->generateChecksum($transId,$amount,$amount,$status,$rkey,$key);
//         $str = $transId."|".$amount."|".$amount."|".$status."|".$rkey."|".$key;
//         //echo md5($str)."_".$checksum;exit;
//          $form='<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
//                 <form id="submit" name="f1" action="'.$return_url.'">
//                 <input type="hidden" name="transid" value="'.$transId.'">
//                 <input type="hidden" name="status" value="'.$status.'">
//                 <input type="hidden" name="rkey" value="'.$rkey.'">
//                 <input type="hidden" name="checksum" value="'.md5($str).'">
//                 <input type="hidden" name="sellingamount" value="'.$amount.'">
//                 <input type="hidden" name="accountingamount" value="'.$amount.'">
//             </form><script>$("#submit").submit();</script>';
//         // $pay->save();
//     }


//     public function ReturnPay(Request $request){
        
//         $response_encrypted = $request->spdata;
//         $curl = curl_init();

//         curl_setopt_array($curl, [
//             CURLOPT_URL => "https://shurjopay.com/merchant/decrypt.php?data=" . $response_encrypted,
//             CURLOPT_RETURNTRANSFER => true,
//             CURLOPT_ENCODING => "",
//             CURLOPT_MAXREDIRS => 10,
//             CURLOPT_TIMEOUT => 30,
//             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//             CURLOPT_CUSTOMREQUEST => "GET",
//             CURLOPT_POSTFIELDS => "",
//         ]);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//         $response = curl_exec($curl);
//         $err = curl_error($curl);
//         $pos_data=array();
//         curl_close($curl);
//         if ($err) {
//             echo "cURL Error #:" . $err;
//         } else {
          
//             $sp_data = simplexml_load_string($response) or die("Error: Cannot create object");
//             $tx_id = $sp_data->txID;

//             //$tx_array=explode('_',$tx_id);
           
//             $bank_status=$sp_data->bankTxStatus;
//             $bank_trx=$sp_data->bankTxID;
//             $sp_code=trim($sp_data->spCode);
//             $paymentOption=$sp_data->paymentOption;
//             $payment_desc=$sp_data->spCodeDes;
//             //$amount=$sp_data->txnAmount;
//             $amount=$sp_data->custom1;


//             $get_payinfo = PaymentTreckerModel::where('txid', '=', $tx_id)->first();
//             $pay = PaymentTreckerModel::find($get_payinfo->id);
//             $merchant_id=$get_payinfo->merchant_id;
//             $return_url=$get_payinfo->return_url;

//             $get_uid = MerchantModel::where('id', '=', $merchant_id)->first();
 
//            // $data="(Payment ID: ".$tx_id.", Bank TrxID: ".$bank_trx.", Bank Status: ".$bank_status.", Amount: ".$amount.", Payment Option: ".$paymentOption.", Remarks:".$payment_desc.")";
//             //$tx_id=explode('_',$tx_id);
//             //$tx_id=$tx_id[1];

            
//             srand((double)microtime()*1000000);
//             $rkey = rand();
//             $transId=trim($get_payinfo->order_id);
//             $sellingCurrencyAmount=(float)$amount;
//             $accountingCurrencyAmount=(float)$amount;
//             $key=trim($get_uid->ref_code);
//             $checksum="";
            
//           // echo $checksum."_".$transId;exit;

//             // $pos_data=array(
//             //     'transid'=>$transId,
//             //     'status'=>$status,
//             //     'rkey'=>$rkey,
//             //     'checksum'=>$checksum,
//             //     'sellingamount'=>(float)$amount,
//             //     'accountingamount'=>(float)$amount
//             // );
//             $pay->sp_code = $sp_code;
//             $pay->bank_status = $bank_status;

//             if($sp_code=="000")
//             {
//                 $status="Y";
//             }
//             else
//             {
//                 $status="N";
//             }
//             //$checksum=$this->generateChecksum($transId,$amount,$amount,$status,$rkey,$key);
//             $str = $transId."|".$amount."|".$amount."|".$status."|".$rkey."|".$key;
//             //echo md5($str)."_".$checksum;exit;
//              $form='<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
//                     <form id="submit" name="f1" action="'.$return_url.'">
//                     <input type="hidden" name="transid" value="'.$transId.'">
//                     <input type="hidden" name="status" value="'.$status.'">
//                     <input type="hidden" name="rkey" value="'.$rkey.'">
//                     <input type="hidden" name="checksum" value="'.md5($str).'">
//                     <input type="hidden" name="sellingamount" value="'.$amount.'">
//                     <input type="hidden" name="accountingamount" value="'.$amount.'">
//                 </form><script>$("#submit").submit();</script>';
//             $pay->save();
//         }

        
//         //return redirect()->intended($return_url);
//         //return Redirect::to($return_url,$pos_data) ; 
//         //return Redirect::away($return_url)->withInputs($pos_data);
//         return $form;
//     }


//     public function addnewmerchant($key, $email, $mobile)
//     {
//         $merchant = new MerchantModel();
//         $merchant->ref_code = $key;
//         $merchant->user_type = "merchant";
//         $merchant->password = '$2y$10$AYomEl46CN.HFumakuNspul/s.tR4zHorKNfvWRmG6blxXWiiaAqi';
//         $merchant->email = $email;
//         $merchant->merchant_name = $email;
//         $merchant->mobile = $mobile;
//         $merchant->trade_license = $mobile;
//         $merchant->save();
//         return $merchant->id;
//     }
   









//  // //


//     /////////////////////////////////////////////////////////////////////////////////////$key = $_GET['apikey'];
//     /////////////////////////////////////////////////////////////////////////////////////// $spkey= $_GET['spkey'];
//     ////////////////////////////////////////////////////////////////////////////////// &spkey=SPR
//     // &uid=spregister
//     // &pass=Gf4SjvsDZ3st
//     ////////////////////////////////////////////////////////////////////////////// &paymenttypeid=113296
//     ///////////////////////////////////////////////////////////////////////////// &transid=Payment-Test-1
//     // //////////////////////////////////////////////////////////////////////////////&userid=1150509
//     ////////////////////////////////////////////////////////////////////////////// $usertype = $_GET['usertype'];
//     //////////////////////////////////////////////////////// $checksum = $_GET['checksum'];
//     ////////////////////////////////////////////////////////////////////////////////////// $transactiontype = $_GET['transactiontype'];    
//     ////////////////////////////////////////////////////////////////////////////////////// $invoiceids = $_GET['invoiceids'];
//     // $debitNoteIds = $_GET["debitnoteids"];
//     ////////////////////////////////////////////////////////// $description = $_GET['description'];
//     ///////////////////////////////////////////////////////// $sellingCurrencyAmount = $_GET['sellingcurrencyamount'];
//     // $accountingCurrencyAmount = $_GET['accountingcurrencyamount'];
//     // &redirecturl=https%3A%2F%2Fcp.resellcamp.com%2Fservlet%2FTestCustomPaymentAuthCompletedServlet
   
//     // ////////////////////////////////////////////////////////////////////////&name=Demo
//     // &company=Demo+Account
//     ////////////////////////////////////////////////////////////////////////////////////////// &emailAddr=demo%40gmail.com
//     // ////////////////////////////////////////////////////////////////////////////////&address1=Faridpur
//     // &address2=
//     // &address3=
//     //////////////////////////////////////////////////////////////////////////////////////////// &city=FARIDPUR
//     // ///////////////////////////////////////////////////////////////////////////////////////////&state=Faridpur
//     ///////////////////////////////////////////////////////////////////////////////////////////////// &country=BD
//     // /////////////////////////////////////////////////////////////////////////////////////////////&zip=7800
//     // &telNoCc=880
//     ///////////////////////////////////////////////////////////// &telNo=0170823456
//     // &faxNoCc=
//     // &faxNo=
//     // &resellerEmail=demo%40gmail.com
//     // &resellerURL=demo1150509.srsportal.com
//     // &resellerCompanyName=Demo+Account
//     //////////////////////////////////////////////////////////// &resellerCurrency=BDT
//     // &brandName=Demo+Account

