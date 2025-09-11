<?php
session_start();
$_SESSION["user"] = 0;
$message = "Welcome!";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="styles.css" />
</head>

<body>
	<p><img src="Logo_Blue_cropped.png" align="left" width="200" height="105" /></p>
	<BR>
	<BR>
	<BR>
	<BR>
	<BR>
	<BR>
	<title>User Login</title>

	<form name="frmUser" method="post" action="Login_Redirect.php">

		<h1>
			<?php if ($message != "") {
				echo $message;
			} ?>
		</h1>

		<br><br>

		<div class="table" align="center">
			<div class="row header">
				<div class="cell">Enter Login Details</div>
			</div>
			<div class="row">
				<div class="cell">Username <input type="text" name="userName"></div>
			</div>
			<div class="row">
				<div class="cell">Password <input type="password" name="password"> </div>
			</div>
			<div class="row">
				<div class="cell"><input type="submit" name="submit" value="Log in"></div>
			</div>
		</div>

	</form>
</body>

</html>