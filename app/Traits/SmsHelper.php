<?php

namespace App\Traits;

use SoapClient;
use SoapFault;

trait SmsHelper
{


	public static function sendSms(int $phoneNumber, array $massage, string $type = 'shop')
	{

		switch ($type){
			case 'register';
				$msg = "کد شما جهت ورود شما در سایت طارونه.
				 Code : $massage[0]";
				break;
			case 'orderCreated';
				$msg = "$massage[0] عزیز سفارش شما با موفقیت ثبت شد.
				 لینک پیگیری سفارش : $massage[1]";
				break;
			default :
				$bodyID = null;
				break;
		}

		ini_set("soap.wsdl_cache_enabled", "0");
		try {
			$client = new SoapClient('http://api.payamak-panel.com/post/send.asmx?wsdl', array('encoding'=>'UTF-8'));
			$parameters['username'] = "09121059528";
			$parameters['password'] = "3fc4e";
			$parameters['from'] = "50004000059528";
			$parameters['to'] = array($phoneNumber);
			$parameters['text'] = $msg;
			$parameters['isflash'] = true;
			$parameters['udh'] = "";
			$parameters['recId'] = array(0);
			$parameters['status'] = 0x0;
			$client->GetCredit(array("username"=>"wsdemo","password"=>"wsdemo"))->GetCreditResult;
			$client->SendSms($parameters)->SendSmsResult;
			return true;
		} catch (SoapFault $ex) {
			return false;
		}
	}


	# use Queue here
//	public static function sendSms(int $phoneNumber, array $massage, string $type = 'shop'): array
//	{
//		switch ($type){
//			case 'register';
//				$bodyID = 77199;
//				break;
//			case 'orderCreated';
//				$bodyID = 77202;
//				break;
//			default :
//				$bodyID = null;
//				break;
//		}
//
//		ini_set("soap.wsdl_cache_enabled","0");
//		try {
//			$sms = new SoapClient("http://api.payamak-panel.com/post/Send.asmx?wsdl", array("encoding" => "UTF-8"));
//		} catch (\SoapFault $e) {
//			return ['success' => false, 'msg' => 'ERROR_IN_SEND_SMS', 'e' => $e->getMessage()];
//		}
//		$data = array(
//			"username" 	=> "09121059528",
//			"password" 	=> "3fc4e",
//			"text" 		=> $massage,
//			"to" 		=> "$phoneNumber",
//			"bodyId" 	=> $bodyID,
//		);
//		$send_Result = $sms->SendByBaseNumber($data)->SendByBaseNumberResult;
//		return $send_Result;
//
//	}

}
