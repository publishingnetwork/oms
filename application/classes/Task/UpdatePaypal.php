<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Updatepaypal extends Minion_Task
{

    protected function _execute(array $params)
    {
		require_once(SYSPATH . '../vendor/autoload.php');
	
		Log::instance()->add(Log::INFO, 'Started to update PayPal statuses!');
	
		set_time_limit(0);
		ignore_user_abort(true);
	
		$paypal_details = ORM::factory('PaypalDetail')
			->where('date_added', '>', DB::expr('DATE_SUB(NOW(), INTERVAL 21 day)'))
			->find_all();

		foreach ($paypal_details as $p_d) {
			$transaction_details = new GetTransactionDetailsRequestType();
			$transaction_details->TransactionID = $p_d->transaction_id;
			
			$request = new GetTransactionDetailsReq();
			$request->GetTransactionDetailsRequest = $transaction_details;
			
			$service = new PayPalAPIInterfaceServiceService();
			try {
				$apiCredential = new PPSignatureCredential('tycg85_api1.gmail.com', 'WZNECZM4GQMFHMJ4', 'AFcWxV21C7fd0v3bYYYRCpSSRl31A9l6qEDi02xkaCpZfFqI8kuwQ9hS');
				/* wrap API method calls on the service object with a try catch */
				$response = $service->GetTransactionDetails($request, $apiCredential);

				if($response->Ack != 'Success'){

					$apiCredential = new PPSignatureCredential('payment_api1.holistichealthlabs.com', 'D4N7C9ZTXRLLGAW9', 'AeWPD6GoYXc3DoYa13UkIOrSS7wTAljOMT-Ry2mfagl8-zl9jK.wYp8d');
					/* wrap API method calls on the service object with a try catch */
					$response = $service->GetTransactionDetails($request, $apiCredential);
				}
				
				if ($response->Ack == 'Success' || $response->Ack == 'SuccessWithWarning') {
					$p_d->status = $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus;
					$p_d->save();
					
					Log::instance()->add(Log::INFO, 'PayPal status updated for # ' . $p_d->id . '. New status: ' . $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus);
					
					if ($p_d->status == 'Refunded' || $p_d->status == 'Reversed') {
						$order = ORM::factory('Order', $p_d->order_id);
						$order->internal_status = 'refunded';
						$order->_update_affiliate_status();
						$order->save();
						Log::instance()->add(Log::INFO, 'Order internal status updated for # ' . $order->id . '. New status: REFUNDED');
					}
					
				} else {
					
					Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Errors: ' . json_encode($response->Errors[0]));

				}
			} catch (Exception $e) {			
				Log::instance()->add(Log::WARNING, 'Failed to fetch PayPal status for # ' . $p_d->id . '. Error: ' . $e->getMessage());
			}
				
		}
    }
}