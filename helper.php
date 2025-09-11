<?PHP


function show_timestamp( $last_update) {
	$dt = new DateTime( $last_update, new DateTimeZone('UTC') );
	$dt->setTimezone( new DateTimeZone( 'America/Toronto' ) );
	return $dt->format('Y-m-d H:i:s T');
}

function GetUserData($unique_id) {

	$path = "/home/ubuntu/signups/$unique_id";
	$path_user_data = "$path.user_data";

	$user_data = false;
	$user_data_f = file_get_contents($path_user_data);
	if( $user_data_f != false ) {
		$user_data_text = base64_decode($user_data_f);
		$user_data = json_decode( $user_data_text, true );
	}
	return $user_data;
}

function GetCartData($unique_id) {

	$path = "/home/ubuntu/signups/$unique_id";
	$path_cart = "$path.cart";

	$user_data = "";
	$user_data_f = file_get_contents($path_cart);
	if( $user_data_f) {
		$user_data_text = base64_decode($user_data_f);
		$user_data = json_decode( $user_data_text, true );
	}
	return $user_data;
}

function GetCancelledReason($db, $id ) {

	$ret = "";
	$sql = "SELECT * FROM state_change WHERE sid=$id AND state=701 ORDER BY state DESC";
	$res = $db->query( $sql );
        while( $obj = $res->fetch_object() )  {
		$data = json_decode( base64_decode( $obj->state_data ), true );
		if( strlen( $data["reasonext"]) > 0 ) {
			$reason = $data["reason"] . " " . base64_decode( $data["reasonext"] );
		} else {
			$reason = $data["reason"];
		}
		$amount = $data["amount_refunded"];
		$da = $data["refund_date"];
		$ret = "Reason : $reason - Refunded: $amount - Refunded Date: $da";
		return $ret;
	}

	return "";

}

function GetSubmittedInfo($db, $id ) {

	$ret = "";
	$sql = "SELECT * FROM state_change WHERE sid=$id AND state=220 ORDER BY state DESC";
	$res = $db->query( $sql );
        while( $obj = $res->fetch_object() )  {
		$data = json_decode( base64_decode( $obj->state_data ), true );
		$provider = $data["provider"] . " " . $data["provider_ext"];
		$accid = $data["accountid"];
		$da = $data["booking_date"];
		$ret = "Provider: $provider - AccId: $accid - BookedDate: $da";
		return $ret;
	}

	return "";

}

function GetPaymentRefSummary( $db, $id ) {

	$ret = "";
	$sql = "SELECT * FROM state_change WHERE sid=$id AND state=205 ORDER BY state DESC";
	$res = $db->query( $sql );
        while( $obj = $res->fetch_object() )  {
		$data = json_decode( base64_decode( $obj->state_data ), true );
		$type = $data["type"];
		$ref = $data["ref_num"];
		$amount = $data["amount"];
		$ret = "Via: $type - Ref: $ref - Amount: $$amount";
		return $ret;
	}

	return "";
}

function GetHighestState( $db, $id ) {

	$ret = array();
	$sql = "SELECT * FROM state_change WHERE sid=$id ORDER BY state DESC";
	$res = $db->query( $sql );
        while( $obj = $res->fetch_object() )  {
		$ret["id"] = $id;
		$ret["state"] = $obj->state;
		$ret["data"] = json_decode( $obj->data );
		return $ret;
	}

}

function GetDisplayAccountInfo( $db, $id ) {

	$ret1 = "";
	$ret2 = "";
	$ret = "";

	$sql = "SELECT * FROM state_change WHERE sid=$id AND ( state=305 OR state=220 ) ";
	$res = $db->query( $sql );
	while( $obj = $res->fetch_object() )  {
		if( $obj->state == 220 ) {
			$data = json_decode( base64_decode( $obj->state_data ), true );
			$accid = $data["accountid"];
			$ret1 = " ProvAccId: $accid";
			error_log(" Got $ret1" );
		}
		if( $obj->state == 305 ) {
			$data = json_decode( base64_decode( $obj->state_data ), true );
			$provider = $data["provider"] . " " . $data["provider_ext"];
			$accid = $data["localaccountid"];
			$ret2 = "Provider: $provider - LocalAccId: $accid";
			error_log(" Got $ret2" );
		}
	}

	$ret = "$ret2 - $ret1";
	return $ret;
}

function GetUpfrontPaymentInfo($user_data) {

	$ret = array();

	if( strcmp( $user_data['upfront_bill_payment_option'], "cc" ) == 0 ) {

		$v = $user_data['upfront_payment'];
		if( strcmp( $v, "true" ) == 0 ) {
			$ret["cc"] = true;
			$ret["etransfer"] = false;
			$ret["payment_method"] = "Credit Card";
			$ret["ccd"] = base64_decode( $user_data['ccd'] );
                        $ret["transaction date"] =  $user_data['order_complete_timestamp'];
                        $ret["transaction response"] = $user_data['upfront_payment_msg'];
                        $ret["transaction code"] = $user_data['upfront_payment_code'];
                        $ret["transaction number"] = $user_data['upfront_payment_ref'];
                        $ret["transaction amount"] = $user_data['upfront_payment_amount'];
		}

	} else {

		if( strcmp( $user_data['upfront_bill_payment_option'], "email-transfer") == 0 ) {
			$ret["etransfer"] = true;
			$ret["cc"] = false;
			$ret["payment_method"] = "e-transfer";
                        $ret["transaction number"] = "Waiting E-Transfer";
		}

	}

	return $ret;

}

function GetGrandTotal($upfront_data ) {

	foreach( $upfront_data as $k1 => $v1 ) {
		$item = $k1;
		if( strcmp( $item, "grand_total") == 0 ) {
			return number_format( $v1[1], 2);
		}
	}

	return false;
}

function ShowUpfront($upfront_data) {

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

function ShowUpfrontPaymentInfo( $user_data ) {
	echo "<h2> Upfront Payment Info </h2>";
	echo '<div class="table border">';
	echo '<div class="row header blue">';
	echo "<div class='cell'> Field </div><div class='cell'> Value </div> ";
	echo "</div>";

	if( strcmp( $user_data['upfront_bill_payment_option'], "cc" ) == 0 ) {
		echo "<div class='row'> <div class='cell'> Upfront Payment Method</div><div class='cell'>Credit Card</div></div>";
	}

	if( strcmp( $user_data['upfront_bill_payment_option'], "email-transfer") == 0 ) {

		$v = $user_data['upfront_bill_payment_option'];
		echo "<div class='row'><div class='cell'>Upfront Payment Method</div><div class='cell'> $v </div></div>";

		$v = $user_data['order_complete_timestamp'];
		if( strlen( $v ) > 0 ) {
			$vorig = date( 'Y-m-d H:i:s T', $v);
			$v = show_timestamp( $vorig );
			echo "<div class='row'><div class='cell'> Upfront Payment Date </div><div class='cell'> $v / $vorig </div></div>";
		}

	} else {

		$v = base64_decode( $user_data['upfront_billing_card_number'] );
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'>Upfront Payment Card Num</div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['upfront_payment'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'>Upfront Payment Successfull</div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['upfront_bill_payment_option'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Payment Method </div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['order_complete_timestamp'];
		if( strlen( $v ) > 0 ) {
			$vorig = date( 'Y-m-d H:i:s T', $v);
			$v = show_timestamp( $vorig );
			echo "<div class='row'><div class='cell'> Upfront Payment Date </div><div class='cell'> $v  / $vorig </div></div>";
		}

		$v = $user_data['upfront_payment_msg'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Credit Card Conf Msg </div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['upfront_payment_code'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Credit Card Transaction Code </div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['upfront_payment_ref'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Credit Card Transaction Ref </div><div class='cell'> $v </div></div>";
		}

		$v = $user_data['upfront_payment_amount'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Credit Card Transaction Amount </div><div class='cell'> $$v </div></div>";
		}

		$v = $user_data['upfront_payment_date'];
		if( strlen( $v ) > 0 ) {
			echo "<div class='row'><div class='cell'> Upfront Credit Card Transaction Date </div><div class='cell'> $$v </div></div>";
		}
	}

	echo "</div>";
}

function GetOrderStepName($order_step) {
	if( intval( $order_step ) == 1) {
		return "1-Customer Details";
	} else if( intval( $order_step ) == 2 ) {
		return "2-Internet Plan";
	} else if( intval( $order_step ) == 3 ) {
		return "$order_step-Installation Date";
	} else if( intval( $order_step ) == 4 ) {
		return "$order_step-Phone Plan";
	} else if( intval( $order_step ) == 5 ) {
		return "$order_step-Modem Plan";
	} else if( intval( $order_step ) == 6 ) {
		return "$order_step-Monthly Payment Opt";
	} else if( intval( $order_step ) == 7 ) {
		return "$order_step-Init Payment Opt";
	}
}

function ShowCustomerDetails($json_data) {
	echo "<h1> Customer Info </h1>";
	echo '<div class="table">';
	echo '<div class="row header">';
	echo "<div class='cell'> Stage </div><div class='cell'> First Name</div><div class='cell'>Last Name</div>";
	echo "<div class='cell'> Email </div><div class='cell'> Phone</div><div class='cell'> Unit # </div>";
	echo "<div class='cell'> Street </div> <div class='cell'> City </div><div class='cell'> Prov </div>";
	echo "<div class='cell'> Postal </div> <div class='cell'>Buzzer</div> </div>";

	echo "<div class='row'>";
	echo "<div class='cell'> " . GetOrderStepName( $json_data['order_step'] ) . "</div>";
	echo "<div class='cell'> " . $json_data['first_name'] . "</div>";
	echo "<div class='cell'> " . $json_data['last_name'] . "</div>";
	echo "<div class='cell'> " . $json_data['email'] . "</div>";
	echo "<div class='cell'> " . $json_data['phone'] . "</div>";
	echo "<div class='cell'> " . $json_data['unit_num'] . "</div>";
	echo "<div class='cell'> " . $json_data['street_number'] . " " . $json_data['street_name'] . " " .
		$json_data['street_dir'] . " " . $json_data['street_type']. "</div>";

	echo "<div class='cell'>" . $json_data['city'] . "</div>";
	echo "<div class='cell'>" . $json_data['prov'] . "</div>";
	echo "<div class='cell'>" . $json_data['postal_code'] . "</div>";
	echo "<div class='cell'> " . $json_data['buzzer_code'] . "</div>";

	echo "</div>";
	echo "</div>";
}

function ShowStates($db, $id) {
	echo "<h1> State Changes Logs </h1>";
	echo '<div class="table">';
	echo '<div class="row header green">';
	echo "<div class='cell'>ID</div><div class='cell'>State</div><div class='cell'>Last Update</div><div class='cell'>Values</div><div class='cell'>User</div>";
	echo "</div>";

	$sql = "SELECT * FROM state_change WHERE sid=$id ORDER BY state DESC";
	$res = $db->query( $sql );
	while( $obj = $res->fetch_object() )  {
		$user = $obj->user;
		$last_update = show_timestamp( $obj->LastUpdated );
		$state = $obj->state;
		$data = base64_decode( $obj->state_data );

		if( $state == 205 ) {
			$state = "Confirm Payment - $state";
		} else if( $state == 220 ) {
			$state = "Confirm Submitted - $state";
		}
		echo "<div class='row'>";
		echo "<div class='cell'> $id </div>";
		echo "<div class='cell'> $state </div>";
		echo "<div class='cell'> $last_update</div>";
		echo "<div class='cell'> $data </div>";
		echo "<div class='cell'> $user </div>";
		echo "</div>";
	}

	echo "</div>";
}

?>

