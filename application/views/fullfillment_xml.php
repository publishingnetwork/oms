<?php echo "<?xml version='1.0' encoding='ISO-8859-1'?>"; ?>
<!DOCTYPE OrderList SYSTEM 'http://xcp.xpertfulfillment.com/xml/OrderList.dtd'>
<OrderList MerchantName='Holistic Labs Ltd' MerchantId='164'>

<?php foreach ($orders as $order): ?>
<Order id='<?php echo $order['public_id']; ?>'>
	<AddressInfo type='ship'>
		<Name><?php echo htmlspecialchars($order['name']); ?></Name>
		<Company> </Company>
		<Address1><?php echo htmlspecialchars($order['address1']); ?></Address1>
		<Address2><?php echo !empty($order['address2']) ? htmlspecialchars($order['address2']) : ''; ?></Address2>
		<City><?php echo $order['city']; ?></City>
		<State><?php echo $order['state']; ?></State>
		<Country><?php echo $order['country']; ?></Country>
		<Zip><?php echo $order['zip']; ?></Zip>
		<Phone><?php echo $order['phone']; ?></Phone>
		<Email><?php echo $order['email']; ?></Email>
	</AddressInfo>
	<AddressInfo type='bill'>
		<Name><?php echo $order['name']; ?></Name>
		<Company> </Company>
		<Address1><?php echo $order['address1']; ?></Address1>
		<Address2><?php echo !empty($order['address2']) ? $order['address2'] : ''; ?></Address2>
		<City><?php echo $order['city']; ?></City>
		<State><?php echo $order['state']; ?></State>
		<Country><?php echo $order['country']; ?></Country>
		<Zip><?php echo $order['zip']; ?></Zip>
		<Phone><?php echo $order['phone']; ?></Phone>
		<Email><?php echo $order['email']; ?></Email>
	</AddressInfo>
	<ShippingMethod><?php echo $order['shipping_method']; ?></ShippingMethod>
	<Insurance>0</Insurance>
	<ReqSignature>0</ReqSignature>
		<?php $i = 0; ?>
		<?php foreach ($order['items'] as $item): ?>
		<Item num='<?php echo $i++; ?>'>
			<ProductId><?php echo $item['sku']; ?></ProductId>
			<Quantity><?php echo $item['quantity']; ?></Quantity>
		</Item>
		<?php endforeach; ?>
	<orderRef></orderRef>
	<SpecialInstructions><?php echo htmlentities($order['comments'])?></SpecialInstructions>
</Order>
<?php endforeach; ?>
</OrderList>