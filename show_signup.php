<?php

session_start();
if($_SESSION["user"]) {

	include_once('connect.php');
	include_once "helper.php";

	$uid = CleanUpDesc( $_GET["uid"] );
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
        <script>
	function gotologout() {
		window.location.href = "logout.php";
	}

	function gototomain() {
		window.location.href = "phpinfo.php";
	}

	function etransfer_payment_done(id) {
		window.location.href = "show_signup.php?setvalue=1&command=etransferdone&id=" + id;
	}

	function confirm_payment(uid) {
		window.open( "enter_value.php?state=205&uid=" + uid, '_blank');
	}

	function confirm_submitted(uid) {
		window.open( "enter_value.php?state=220&uid=" + uid, '_blank');
	}

        </script>
</head>


<body>

<p><img src="Logo_Blue_cropped.png" align="left" width="200" height="105" /></p>
<BR>
<BR>
<BR>
<BR>
<BR>
<BR>

<link rel="stylesheet" type="text/css" href="styles.css" />
<div>
<input id="button" type="button" onclick="gototomain()" value="Back to list of orders">
</div>

<h1>Signup Information</h1>
<br><br>


<?php

	function SetEtransferDone($db, $id) {
		$sql = "UPDATE signup SET status=105, payment_complete=1 WHERE id=$id";
		$res = $db->query($sql);
	}

	if( isset( $_GET['setvalue'] ) && $_GET['setvalue'] == 1 ) {
		if( isset( $_GET['command'] ) ) {
			$cmd = strtolower (trim($_GET['command']) );
			if( strcmp( $cmd, "etransferdone") == 0 ) {
				$id = trim( $_GET['id'] );
				SetEtransferDone($db, $id);
			}
		}
	}


	function PaymentSummary( $json_data , $json_cart ) {

	}

	function ParseCartKey( $json_data, $categ ) {

		$found = false;
		$prefix = "cart_category_";

		foreach( $json_data as $k => $v ) {

			$kt = trim( $k );
			if( strlen( $kt ) <= strlen(  $prefix ) ) {
				continue;
			}

			$temp = substr( $kt , 0, strlen( $prefix ) );
			if( strcmp( $temp, $prefix ) == 0 ) {

				$product_id = substr( trim( $kt ) , strlen( $prefix ) );
				if(strcmp ( trim($v), $categ ) == 0 ) {
					echo "<td>" . trim( $json_data["cart_title_$product_id"] ) . "</td>";
					$found = true;
					break;
				}
			}
		}

		if( $found == false ) {
			echo "<td> None </td>";
		}
	}

	function ShowModemInfo($cart_data, $user_data, $upfront_data) {

		echo "<h1> Modem Information </h1>";
		echo '<div class="table">';
		echo '<div class="row header">';
		echo "<div class='cell'>Modem</div><div class='cell'>Service</div>";
		echo "</div>";

		$v = $user_data['selected_modem_plan_name'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'>Selected Modem Plan</div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['selected_modem_plan'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'>Selected Modem Id</div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['BYO_ModemName'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'>Customer's Modem Info </div><div class='cell'> $v </div></div>";
		}

		//var_dump( $user_data );

		echo "</div>";
	}
/*
	function ShowUpfront($cart_data, $user_data, $upfront_data) {

		echo "<h1> Upfront Fees </h1>";
		echo '<div class="table">';
		echo '<div class="row header">';
		echo "<div class='cell'>Item</div><div class='cell'>Service</div><div class='cell'>total</div>";
		echo "</div>";

                foreach( $upfront_data as $k1 => $v1 ) {
			if( strcmp( $k1, "ModemPurchaseOption" ) == 0 ) {
				continue;
			}

			$item = $k1;
			$service = $v1[0];
			$total = number_format( $v1[1], 2);

			echo "<div class='row'><div class='cell'>$item</div><div class='cell'>$service</div><div class='cell'>$$total</div></div>";
		}

		echo "</div>";
	}
 */

	function ShowMonthly($cart_data, $user_data, $monthly_data) {

		echo "<h1> Monthly Fees </h1>";
		echo '<br><br><div class="table" >';
		echo '<div class="row table green">';
		echo "<div class='cell'>Item</div><div class='cell'>Service</div><div class='cell'>total</div>";
		echo "</div>";

                foreach( $monthly_data as $k1 => $v1 ) {
			$item = $k1;
			$service = $v1[0];
			$total = number_format( $v1[1], 2);

			echo "<div class='row'><div class='cell'>$item</div><div class='cell'>$service</div><div class='cell'>$$total</div></div>";
                }

		echo "</div>";
	}

	function ShippingAddress ($user_data) {

		echo "<h2>" . $user_data['modem_delivery'] . "</h2>";

		if( !isset($user_data['modem_shipping_unit_no']) || !isset( $user_data['modem_shipping_street'] ) ) {
			return;
		}
		echo "<h2> Shipping Address </h2>";
		echo '<div class="table">';
		echo '<div class="row header blue">';
		echo "<div class='cell'>Unit</div><div class='cell'>Street</div><div class='cell'>City</div><div class='cell'>Prov</div>";
		echo "<div class='cell'> Postal Code</div><div class='cell'>Phone</div></div>";

		echo "<div class='row'>";

		echo "<div class='cell'>" . $user_data['modem_shipping_unit_no'] . "</div><div class='cell'>" . 
			$user_data['modem_shipping_street'] .  "</div><div class='cell'>" . 
			$user_data['modem_shipping_city'] . "</div><div class='cell'>" . 
			$user_data['modem_shipping_state'] . "</div><div class='cell'>" . 
			$user_data['modem_shipping_postal_code'] . "</div><div class='cell'>" . 
			$user_data['modem_shipping_phone'] . "</div>";

		echo "</div>";
		echo "</div>";
	}

	function GetValue($user_data, $key) {

		foreach( $user_data as $k1 => $v1 ) {
			if( strcmp( $key, $k1 ) == 0 ) {
				return trim($v1);
			}
		}
	}

	function ShowInstallationDates($user_data) {
		echo "<h1> Installation Dates </h1>";
		echo '<div class="table">';
		echo '<div class="row header">';
		echo "<div class='cell'></div><div class='cell'>Date (mm/dd/yyyy)</div><div class='cell'> Time </div>";
		echo "</div>";

		echo "<div class='row'>";
		echo "<div class='cell'> Preferred Appointment 1</div><div class='cell'>" . 
			GetValue( $user_data, "preffered_installation_date_1") . "</div><div class='cell'>" . 
			GetValue($user_data, "preffered_installation_time_1") . "</div>";

		echo "</div><div class='row'>";
		echo "<div class='cell'> Preferred Appointment 2</div><div class='cell'>" . 
			GetValue( $user_data, "preffered_installation_date_2") . "</div><div class='cell'>" . 
			GetValue($user_data, "preffered_installation_time_2") . "</div>";

		echo "</div><div class='row'>";
		echo "<div class='cell'> Preferred Appointment 3</div><div class='cell'>" . 
			GetValue( $user_data, "preffered_installation_date_3") . "</div><div class='cell'>" . 
			GetValue($user_data, "preffered_installation_time_3") . "</div>";

		echo "</div><div class='row'>";
		echo "<div class='cell'> Installation Instructions </div><div class='cell'>" . 
			base64_decode( GetValue( $user_data, "installation_instructions") ) . "</div>";
		echo "</div>";

		echo "</div>";
	}

	function ShowRequestedInstallation($cart_data, $user_data) {
		echo "<h2> Services Requested </h2>";
		echo '<div class="table">';
		echo '<div class="row table blue">';
		echo "<div class='cell'>Prod Id</div><div class='cell'> Service Name </div><div class='cell'> Service </div>";
		echo "<div class='cell'> Price </div><div class='cell'>tax</div><div class='cell'>total</div>";
		echo "</div>";

		$DueNow = 0;
		foreach( $cart_data as $k1 => $v1 ) {
			$prod_id = trim( $v1["product_id"] );
			$line_total = number_format( trim( $v1["line_total"] ), 2);
			$line_tax = number_format( trim( $v1["line_tax"] ), 2);
			$category = $user_data["cart_category_".$prod_id];
			$name = $user_data["cart_title_".$prod_id] . " ID " . $prod_id;
			$total = number_format( (float) $line_total + (float) $line_tax, 2);

			echo "<div class='row'>";
			echo "<div class='cell'>$prod_id</div><div class='cell'>$category</div><div class='cell'>$name</div>";
			echo "<div class='cell'>$$line_total</div><div class='cell'>$$line_tax</div><div class='cell'>$$total</div></div>";
			$DueNow += (float) $total;
		}

		echo "</div>";
	}

	function ShowPaymentInfo( $user_data ) {
                echo "<h2> Monthly Payment Info </h2>";
                echo '<div class="table border">';
                echo '<div class="row header blue">';
                echo "<div class='cell'> Field </div><div class='cell'> Value </div> ";
                echo "</div>";
		echo "<div class='row'>";
		echo "<div class='cell'> Monthly Payment Options </div><div class='cell'> " . GetValue($user_data, "monthly_bill_payment_option") . "</div>";
		echo "</div>";

		//var_dump( $user_data );

		foreach( $user_data as $k => $v ) {
			if( strcmp( "cc_monthly_billing_card_number", $k ) == 0) {
				if( strlen( $v ) > 0 ) {
					echo "<div class='row'><div class='cell'>$k</div><div class='cell'>" . base64_decode( $v ) . " </div></div>";
				}
			}
			if( strcmp( "cc_monthly_billing_card_expiry", $k ) == 0 ||
				strcmp( "cc_monthly_billing_card_cvv", $k ) == 0 ||
				strcmp( "cc_monthly_billing_full_name", $k ) == 0 ||
				strcmp( "cc_monthly_billing_address", $k ) == 0 ||
				strcmp( "cc_monthly_billing_city", $k ) == 0 ||
				strcmp( "cc_monthly_billing_state", $k ) == 0 || 
				strcmp( "cc_monthly_billing_postcode", $k ) == 0 || 
				strcmp( "cc_monthly_billing_phone", $k ) == 0 || 
				strcmp( "bank_monthly_billing_account_type", $k ) == 0 || 
				strcmp( "bank_monthly_billing_financial_institution", $k ) == 0 || 
				strcmp( "bank_monthly_billing_transit_number", $k ) == 0 || 
				strcmp( "bank_monthly_billing_institution_number", $k ) == 0 || 
				strcmp( "bank_monthly_billing_account_number", $k ) == 0 || 
				strcmp( "bank_monthly_billing_first_name", $k ) == 0 || 
				strcmp( "bank_monthly_billing_last_name", $k ) == 0 || 
				strcmp( "bank_monthly_billing_address", $k ) == 0 || 
				strcmp( "bank_monthly_billing_city", $k ) == 0 || 
				strcmp( "bank_monthly_billing_state", $k ) == 0 || 
				strcmp( "bank_monthly_billing_postcode", $k ) == 0 || 
				strcmp( "bank_monthly_billing_phone", $k ) == 0  ) {
				if( strlen( $v ) > 0 ) {
					echo "<div class='row'><div class='cell'>$k</div><div class='cell'>$v</div></div>";
				}
			}
		}
                echo "</div>";
        }

	function ShowJSONInfo($json_data) {

		echo "<div class='table'>";

		foreach( $json_data as $key => $value ) {
			if( is_string( $value )  ) {
				echo "<div class='row'>";
				echo "<div class='cell'> $key </div><div class='cell'> $value </div>";
				echo "</div>";
			} else {
				echo "<div class='row'>";
				echo "<div class='cell'>$key </div> <div class='cell'> <table border=\"1\">" ;
				foreach( $value as $ikey => $ivalue ) {
					echo "<tr>";
					echo "<td> $ikey </td><td> $ivalue </td>";
					echo "</tr>";
				}
				echo "</table></div></div>";
			}
		}

		echo "</div>";
	}


	/*

| id         | int          | NO   | PRI | NULL    | auto_increment |
| unique_id  | varchar(512) | NO   |     | NULL    |                |
| lastupdate | date         | YES  |     | NULL    |                |
| user_data  | text         | YES  |     | NULL    |                |
| cart       | text         | YES  |     | NULL    |                |
| status     | int          | YES  |     | NULL    |                |
| version    | int          | YES  |     | NULL    |                |
| monthly    | text         | YES  |     | NULL    |                |
| upfront    | text         | YES  |     | NULL    |                |
| dg_order   | text         | YES  |     | NULL    |                |
	 */


        echo "<table border='1'>";
        $sql = "SELECT * FROM signup WHERE id ='$uid';";
        $db = connect_db();
        $res = $db->query($sql);
        while( $obj = $res->fetch_object() )  {

                $id = $obj->id;
                $unique_id = $obj->unique_id;
		$status = $obj->status;

                //$user_data_text = base64_decode($obj->user_data);
                $user_data = GetUserData($unique_id); //json_decode( $user_data_text, true );

                //$cart_text = base64_decode($obj->cart);
                $cart_data = GetCartData($unique_id); //json_decode( $cart_text, true );

                $monthly_text = base64_decode($obj->monthly);

                $upfront_text = base64_decode($obj->upfront);
                $upfront_data = json_decode( $upfront_text, true );

                $order_text = base64_decode($obj->dg_order);
                $monthly_data = json_decode( $monthly_text, true );
                $order_data = json_decode( $order_text, true );

		echo "<hr>";

		/*
		if( $status > 200 )  { // payment complete
			echo "<div class='row'>";
			echo "<div class='cell'>Customer Payment </div> <div class='cell'> Payment Complete </div>";
			echo "</div>";


			$v = $user_data['selected_modem_plan'];
			if( $v == 7149) {

			} else {
				//if( $v == 7150) {
				echo "<div class='row'>";
				echo "<div class='cell'> Equipment Information</div><div class='cell'></div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> Modem Type </div><div class='cell'> <input type=text id='modemtype' name='modemtype' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> Modem S/N </div><div class='cell'> <input type=text id='modemsn' name='modemsn' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> Modem Mac </div><div class='cell'> <input type=text id='modemmac' name='modemmac' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'><input type=\"button\" onclick=\"add_cable_modem($id);\" value=\"Add Cable Modem Info - Rental Modem\"> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> Equipment Information</div><div class='cell'></div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> DSL User </div><div class='cell'> <input type=text id='dsluser' name='dsluser' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> DSL Pass </div><div class='cell'> <input type=text id='dslpass' name='dslpass' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'> DSL Conf Domain </div><div class='cell'> <input type=text id='dsldome' name='dsldome' /> </div>";
				echo "</div><div class='row'>";
				echo "<div class='cell'><input type=\"button\" onclick=\"add_dsl_modem($id);\" value=\"Add DSL Info - DSL Modem Rental \"> </div>";
				echo "</div>";
			}
		}
		*/

		echo "</div></div>";

continue_next:
		if( $status < 205 && $status >= 100 ) {
			echo "<div> <button onclick=\"confirm_payment('$id');\"> Confirm Payment </button></div>";
		}

		if( $status >= 205 && $status < 220 ) {
			echo "<div> <button onclick=\"confirm_submitted('$id');\"> Confirm Submitted </button></div>";
		}

		ShowStates($db, $id);

		echo "<hr>";

		// show the signup name, address, status
		ShowCustomerDetails( $user_data );

		ShowModemInfo( $cart_data, $user_data, $monthly_data );

		ShippingAddress($user_data);

		// show the cart information (the services in the cart)
		ShowRequestedInstallation( $cart_data, $user_data );

		// installation dates
		ShowInstallationDates($user_data);

		// show the cart information (the services in the cart)
		ShowMonthly( $cart_data, $user_data, $monthly_data );
		ShowPaymentInfo( $user_data );

		// show the cart information (the services in the cart)
		ShowUpfront( $upfront_data );
		ShowUpfrontPaymentInfo( $user_data );

echo json_encode( $user_data )  ;
echo "<br><br>";
//echo json_encode( $cart_data );

		/*
		 // add back if need more debug
		echo "<br><br>";
		echo '<table border="1">';
		ShowJSONInfo($user_data);

                $cart_text = base64_decode($obj->cart);
                $cart_data = json_decode($cart_text, true);

		ShowJSONInfo($cart_data);

		ShowJSONInfo($monthly_data);
		 
		echo '</table>';
		 */
        }
	echo "</table>";
	$db->close();

} else {

        header("Location: login.php");

}
?>

</body>
</html>


