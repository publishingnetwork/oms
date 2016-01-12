<!doctype html>

<html>

	<head>
		<meta charset="utf8">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/bootstrap.css">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/bootstrap-responsive.css">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/style.css">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/datepicker.css">
		<title>OMS</title>
	</head>
	
	<body>
		<div class="container-fluid">
			<div class="row-fluid">
				
				<div class="span12">
					<div class="navbar">
						<div class="navbar-inner">
							<a href="" class="brand">OMS</a>
							<ul class="nav">
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										Orders
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<?php if (in_array($user->type, array('admin', 'staff'))): ?>
										<li><a class="order-new" href="<?php echo URL::base(); ?>order#new-order">New Order</a></li>
										<?php endif; ?>
										<li><a href="<?php echo URL::base(); ?>order">List Orders</a></li>
									</ul>
								</li>
								<li>
									<a href="<?php echo URL::base(); ?>order/report">Reports</a>
								</li>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										Products
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<?php if (in_array($user->type, array('admin', 'staff'))): ?>
										<li><a class="product-new" href="<?php echo URL::base(); ?>product#new-product">New Product</a></li>
										<?php endif; ?>
										<li><a href="<?php echo URL::base(); ?>product">List Products</a></li>
									</ul>
								</li>
								<?php if (in_array($user->type, array('admin', 'staff'))): ?>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										Users
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<?php if ($user->type == 'admin'): ?>
										<li><a class="user-new" href="<?php echo URL::base(); ?>user#new-user">New User</a></li>
										<?php endif; ?>
										<li><a href="<?php echo URL::base(); ?>user">List Users</a></li>
									</ul>
								</li>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										Affiliates
										<b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<?php if ($user_type != 'guest'): ?>
										<li><a class="affiliate-new" href="<?php echo URL::base(); ?>affiliate#new-affiliate">New Affiliate</a></li>
										<?php endif; ?>
										<li><a href="<?php echo URL::base(); ?>affiliate">List Affiliates</a></li>
										<li><a href="<?php echo URL::base(); ?>affiliatepayment">List Payments</a></li>
									</ul>
								</li>
								<?php endif; ?>
								<li>
									<a href="<?php echo URL::base(); ?>system/logout" class="pull-right">Logout</a>
								</li>
							</ul>
							
						</div>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div id="js_message" class="alert hide"><span></span><a class="close" data-dismiss="alert" href="#">&times;</a></div>
				<?php echo $body; ?>
			</div>
		</div>
		
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.bsvalidate.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/dataTables.bootstrap.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/bootstrap-datepicker.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/helpers.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/common.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/underscore-min.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.jeditable.min.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/<?php echo $controller; ?>.js"></script>
		
		<script>
		
		</script>
	</body>

</html>