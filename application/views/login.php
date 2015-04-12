<!doctype html>

<html>

	<head>
		<meta charset="utf8">
		<title></title>
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/bootstrap-responsive.min.css">
		<link rel="stylesheet" href="<?php echo URL::base(); ?>css/style.css">
		<title>Login</title>
	</head>
	
	<body>
		<div class="row-fluid">
			<div class="span3 offset4">
				<div class="well" id="login-well">
					<form action="<?php echo URL::base(); ?>system/login" method="post">
						<fieldset>
							<legend>Please login</legend>
							<?php if (!empty($error)): ?>
							<div class="alert alert-error">
								<?php echo $error; ?>
								<a class="close" data-dismiss="alert" href="#">&times;</a>
								
							</div>
							<?php endif; ?>
							<?php if (!empty($message)): ?>
							<div class="alert alert-success">
								<?php echo $message; ?>
								<a class="close" data-dismiss="alert" href="#">&times;</a>
								
							</div>
							<?php endif; ?>
							
							<div class="control-group">
								<label for="login">Login</label>
								<div class="controls">
									<input type="text" class="input-block-level" id="login" name="login" value="">
								</div>
							</div>
							<div class="control-group">
								<label for="password">Password</label>
								<div class="controls">
									<input type="password" class="input-block-level" id="password" name="password" value="">
								</div>
							</div>
							<button class="btn btn-success btn-block">Login</button>
						</fieldset>
					</form>
				
				</div>
			</div>
		</div>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/jquery.bsvalidate.js"></script>
		<script type="text/javascript" src="<?php echo URL::base(); ?>js/bootstrap.min.js"></script>
<!-- 		<script type="text/javascript" src="<?php echo URL::base(); ?>js/login.js"></script> -->
	</body>

</html>