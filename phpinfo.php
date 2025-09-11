<?php

session_start();
if($_SESSION["user"]) {

	include_once('connect.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<script>
	function gotologout() {
		   window.location.href = "logout.php";
	}

	function show_signup(uid) {
                   window.location.href = "show_signup.php?uid=" + uid;
	}

	function confirm_order(uid) {
                   window.open( "enter_value.php?state=105&uid=" + uid, '_blank');
	}

	function get_csv() {
                   window.open( "get_csv.php", '_blank');
	}

	function confirm_payment(uid) {
                   window.open( "enter_value.php?state=205&uid=" + uid, '_blank');
	}

	function cancel_order (uid) {
                   window.open( "enter_value.php?state=701&uid=" + uid, '_blank');
	}

	function complete_install_order (uid) {
                   window.open( "enter_value.php?state=305&uid=" + uid, '_blank');
	}

	function confirm_submitted(uid) {
                   window.open( "enter_value.php?state=220&uid=" + uid, '_blank');
	}

	function search () {
		var data = document.getElementById("search").value;
		if( data.length > 0 ) {
			var datab = btoa( data );
			window.location.href = "phpinfo.php?search=" + datab;
		} else {
			window.location.href = "phpinfo.php";
		}
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
<div><input id="button" type="button" onclick="gotologout()" value="Log Out"></div>

<br><br>
<br><br>
<br>

<h1>Online Signups</h1>

<br><br>

<?php
	include_once "helper.php";

	function show_ccd( $value ) {

		if( $value == false ) {
			return "";
		}

		$v = trim( $value );
		if( strlen ( $v ) == 0 ) {
			return "";
		}

		$v = base64_decode( $v );
		if( strlen( $v ) == 0 ) {
			return "";
		}
		if( $v[0] == '?' ) {

		} else {
			return $v;
		}
		
		if( $v[0] == '?' ) {
			$v = substr($v, 1);
		}

		$ret = "";
		//$ret = "$v | ";
		$ccd = "";
		parse_str( $v, $param );
		if( isset( $param['ccd'] ) ) { 
			$ccd = $param['ccd'];
		}
		if( isset( $param['CCD'] ) ) { 
			$ccd = $param['ccd'];
		}

		if( isset( $param['add-plan'] ) ) {
			$ccd = show_ccd( $ccd );
		}

		$ret .= $ccd;
		return $ret;
	}

	function print_avail_checks( $db ) {
		echo "<h2> Avail Checks </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header\"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div>";
		echo "<div class='cell'> CCD </div>";
		echo "<div class='cell'>Address</div>";
		echo "</div>";
		$sql = "select * from avail_checks ORDER BY LastUpdated DESC LIMIT 100;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$status = $obj->success;
			$last_update = show_timestamp( $obj->LastUpdated );
			$user_data_b64 = base64_decode( $obj->user_data ); 
			$user_data = json_decode( $user_data_b64 ); 
			$address = base64_decode( $obj->address ); 
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status </div>";
			echo "<div class='cell'> $last_update </div>";
			$v = "";
			if( isset( $user_data->ccd ) ) {
				$v = show_ccd( $user_data->ccd );
			}
			echo "<div class='cell'>$v</div>";
			echo "<div class='cell'> $address </div>";
			echo "<div class='cell'> $user_data_b64 </div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printnotsyncedyet($db ) {
		echo "<h2> Signup DID NOT SYNC TO HUBSPOT YET -- probably have invalid data </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header blue\"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div> <div class='cell'> CCD </div>";
		echo "</div>";
		$sql = "select * from signup WHERE signup_update > hupspot_update ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );
			$user_data = GetUserData($unique_id); 
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "</div>";
				continue;
			}
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			$v = show_ccd( $user_data['ccd'] );
			echo "<div class='cell'>$v</div>";
			echo "<div> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printcancelled($db ) {
		echo "<h2> Cancelled Orders </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header blue\"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div> <div class='cell'> Reason </div>";
		echo "</div>";
		$sql = "select * from signup WHERE status > 700 AND status < 800 ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );
			$user_data = GetUserData($unique_id); 
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "<div class='cell'></div>";
			echo "</div>";
				continue;
			}
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			$v = GetCancelledReason( $db, $id );
			echo "<div class='cell'>$v</div>";
			echo "<div> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printnotcomplete( $db ) {
		echo "<h2> Signup Not Completed By Customer   <button id=\"openPopup\">Export CSV </button> </h2>";

		echo '<dialog id="popup">
    <form method="dialog" id="rangeForm">
      <label>Start:
	<input type="date" id="start" value=' . date('Y') . '-01-01' . ' required>
      </label>
      <br>
      <label>End:
	<input type="date" id="end" value=' . date('Y-m-d') . ' required>
      </label>
      <br><br>
      <button type="submit">OK</button>
      <button type="button" onclick="popup.close()">Cancel</button>
    </form>
  </dialog> ';
		echo "<div class='table' >";
		echo "<div class=\"row header blue\"><div class='cell'>Id</div><div class='cell'>Stopped at Step</div><div class='cell'>Current Step</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div> <div class='cell'> CCD </div>";
		echo "</div>";
		$sql = "SELECT * FROM signup WHERE status < 100 ORDER BY LastUpdated DESC LIMIT 1000;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );

			$user_data = GetUserData($unique_id);
			$highestlevel = $user_data["higheststate"];

			//if( $highestlevel >= 7 && ( strtotime( $obj->LastUpdated ) > ( time() - (14 * 24 * 60 * 60) ) ) )
			//{
			//	$sqll = "UPDATE signup SET status = 100 WHERE id=$id";
				//echo "HERE $obj->LastUpdated";
			//	$db->query( $sqll );
			//}

			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $highestlevel  </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "</div>";
				continue;
			}
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			$v = show_ccd( $user_data['ccd'] );
			echo "<div class='cell'>$v</div>";
			echo "<div class='cell'> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "<div class='cell'> <button onclick=\"confirm_order('$id');\"> Confirm Order </button></div>";
			echo "</div>";
		}
		echo "</div>";


	}
	
	function printsubmmited($db) {
		echo "<h2> Submitted Orders </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header blue\"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div>";
	       	echo "<div class='cell'> Transaction Number </div>";
	       	echo "<div class='cell'> Payment Info </div>";
		echo "</div>";
		$sql = "SELECT * FROM signup WHERE status = 220  ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );

			$user_data = GetUserData($unique_id);
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "</div>";
				continue;
			}
			$transaction = GetUpfrontPaymentInfo($user_data);
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			echo "<div class='cell'>" .  GetSubmittedInfo( $db, $id ) . "</div>";
			echo "<div class='cell'>" .  GetPaymentRefSummary( $db, $id ) . "</div>";
			echo "<div class='cell'> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "<div class='cell'> <button onclick=\"complete_install_order('$id');\"> Install Complete </button></div>";
			echo "<div class='cell'> <button onclick=\"cancel_order('$id');\"> Cancel Order </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printreadytosubmit($db) {
		echo "<h2> Signup Orders with Payment Confirmation -- Waiting for Submission </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header blue\"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div>";
	       	echo "<div class='cell'> Transaction Number </div>";
	       	echo "<div class='cell'> CCD </div>";
		echo "</div>";
		$sql = "SELECT * FROM signup WHERE status > 200 AND status < 220  ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );

			$user_data = GetUserData($unique_id);
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "</div>";
				continue;
			}
			$transaction = GetUpfrontPaymentInfo($user_data);
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			echo "<div class='cell'>" .  GetPaymentRefSummary( $db, $id ) . "</div>";
			$v = show_ccd( $user_data['ccd'] );
			echo "<div class='cell'>$v</div>";
			echo "<div class='cell'> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "<div class='cell'> <button onclick=\"confirm_submitted('$id');\"> Confirm Submitted </button></div>";
			echo "<div class='cell'> <button onclick=\"cancel_order('$id');\"> Cancel Order </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printcompleted($db) {
		echo "<h2> Signup Orders - Waiting for Payment Confirmation</h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header \"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div>";
	       	echo "<div class='cell'> Transaction Number </div>";
	       	echo "<div class='cell'> CCD </div>";
		echo "</div>";
		$sql = "SELECT * FROM signup WHERE status >= 100 AND status < 200 ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );

			$user_data = GetUserData($unique_id);
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "</div>";
				continue;
			}
			$transaction = GetUpfrontPaymentInfo($user_data);
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			echo "<div class='cell'>" .  $transaction['transaction number'] . "</div>";
			$v = show_ccd( $user_data['ccd'] );
			echo "<div class='cell'>$v</div>";
			echo "<div class='cell'> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "<div class='cell'> <button onclick=\"confirm_payment('$id');\"> Confirm Payment </button></div>";
			echo "<div class='cell'> <button onclick=\"cancel_order('$id');\"> Cancel Order </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	function printfinished($db) {
		echo "<h2> Signup Orders Completed Orders </h2>";
		echo "<div class='table' >";
		echo "<div class=\"row header \"><div class='cell'>Id</div><div class='cell'>Status</div>";
		echo "<div class='cell'> Last Update </div><div class='cell'>First Name</div>";
		echo "<div class='cell'> Last Name </div><div class='cell'> Email </div>";
	       	echo "<div class='cell'> Transaction Number </div>";
	       	echo "<div class='cell'> CCD </div>";
		echo "</div>";
		$sql = "SELECT * FROM signup WHERE status >= 300 AND status < 400 ORDER BY LastUpdated DESC;";
		$res = $db->query( $sql );
		while( $obj = $res->fetch_object() )  {
			$id = $obj->id;
			$unique_id = $obj->unique_id;
			$status = $obj->status;
			$last_update = show_timestamp( $obj->LastUpdated );

			$user_data = GetUserData($unique_id);
			echo "<div class='row'>";
			echo "<div class='cell'> $id </div>";
			echo "<div class='cell'> $status (Order Received) [" . $user_data['upfront_bill_payment_option'] . "] </div>";
			echo "<div class='cell'> $last_update </div>";
			if( $user_data == false ) {
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "<div class='cell'></div>";
				echo "</div>";
				continue;
			}
			$v = GetDisplayAccountInfo($db, $id );
			echo "<div class='cell'>" . $user_data['first_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['last_name'] . "</div>";
			echo "<div class='cell'>" .  $user_data['email'] . "</div>";
			echo "<div class='cell'>" .  $v . "</div>";
			$v = show_ccd( $user_data['ccd'] );
			echo "<div class='cell'>$v</div>";
			echo "<div class='cell'> <button onclick=\"show_signup('$id');\"> Show </button></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	$search = "";
	if( isset( $_GET["search"] ) ) {
		$searchb = $_GET["search"];
		$search = base64_decode( $searchb );
		echo "Searching for \"$search\" ";
	}

	$db = connect_db();

	printcompleted($db);
	echo "<hr>";

	printreadytosubmit($db);
        echo "<hr>";

	printsubmmited($db);
        echo "<hr>";

	printnotcomplete($db);
	echo "<hr>";

	printfinished($db);
        echo "<hr>";

	printcancelled($db);
	echo "<hr>";

	printnotsyncedyet($db);
	echo "<hr>";

	print_avail_checks($db);
} else {

	header("Location: login.php");

}

?>

</body>
<script>
const popup = document.getElementById("popup");
document.getElementById("openPopup").addEventListener("click", () => {
popup.showModal();
});

document.getElementById("rangeForm").addEventListener("submit", (e) => {
e.preventDefault();

let start = document.getElementById("start").value;
let end   = document.getElementById("end").value;

popup.close();


let url = "/get_csv.php?start=" + encodeURIComponent(start) + "&end=" + encodeURIComponent(end);
window.open(url, "_blank"); // opens in a new tab


});
</script>
</html>

