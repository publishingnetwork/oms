<?php

class Model_PaypalDetail extends ORM {
	protected $_table_name = 'paypal_details';
	protected $_belongs_to = array('order' => array());
	
	public function _from_paypal_response($response) {
		$this->status        = $response->PaymentTransactionDetails->PaymentInfo->PaymentStatus;
		$this->email         = $response->PaymentTransactionDetails->PayerInfo->Payer;
		$this->gross         = $response->PaymentTransactionDetails->PaymentInfo->GrossAmount->value;
		$this->fee           = $response->PaymentTransactionDetails->PaymentInfo->FeeAmount->value;
		$this->name          = $response->PaymentTransactionDetails->PayerInfo->Address->Name;
		$this->address1      = $response->PaymentTransactionDetails->PayerInfo->Address->Street1;
		$this->address2      = $response->PaymentTransactionDetails->PayerInfo->Address->Street2;
		$this->city          = $response->PaymentTransactionDetails->PayerInfo->Address->CityName;
		$this->state         = $response->PaymentTransactionDetails->PayerInfo->Address->StateOrProvince;
		$this->country       = $response->PaymentTransactionDetails->PayerInfo->Address->Country;
		$this->phone         = $response->PaymentTransactionDetails->PayerInfo->Address->Phone;
		$this->zip           = $response->PaymentTransactionDetails->PayerInfo->Address->PostalCode;
		$this->notes         = $response->PaymentTransactionDetails->PaymentItemInfo->Memo;
		$this->item_id       = $response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Number;
		$this->item_title    = $response->PaymentTransactionDetails->PaymentItemInfo->PaymentItem[0]->Name;
		$this->shipping_cost = $response->PaymentTransactionDetails->PaymentInfo->ShipAmount;
		$this->paypal_account = $response->PaymentTransactionDetails->ReceiverInfo->Receiver;
		
		$date = new DateTime($response->PaymentTransactionDetails->PaymentInfo->PaymentDate);
		$this->date_added = $date->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

		$this->net           = $this->gross - $this->fee;
	}
}