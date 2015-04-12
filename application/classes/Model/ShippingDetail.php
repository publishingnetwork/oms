<?php

class Model_ShippingDetail extends ORM {
	protected $_table_name = 'shipping_details';
	protected $_belongs_to = array('Order' => array());
}