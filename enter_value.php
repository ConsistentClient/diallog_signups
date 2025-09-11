<?PHP



session_start();
if( $_SESSION["user"] ) {

        include_once('connect.php');
        include_once "helper.php";

        $uid = $_GET["uid"] ;
        $state = $_GET["state"] ;
	$user = $_SESSION["user"];


	if( $uid <= 0) {
		return;
	}

	if( $state <= 100 ) {
		echo "Error invalid state $state";
	}

	if( isset( $_GET["submit_values"] ) ) {
		$data = $_GET["data"];
		$sql = "INSERT INTO state_change (sid, LastUpdated, state, state_data, user ) VALUES ($uid, NOW(), $state, '$data', '$user');";
		$db = connect_db();
		$res = $db->query($sql);
		if( $res == false ) {
			echo "failed";
		} else {
			$sql = "UPDATE signup SET status=$state WHERE id=$uid;";
			$db->query($sql);
			echo "success";
		}
		$db->close();
		exit(0);
	}
?>

<html>
<head>
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
</div>


<?PHP
	function show_form_for_confirm_cancellation ( $uid, $credit_card ) {
		echo " <h1>Confirm Cancellation</h1>";
		echo "<br><br>";
		echo "<table>";
		echo "<tr><td><input type='hidden' id='state' value='701' ></td></tr>";
		echo "<tr><td><label> Reason </label></td><td><select id='reason' onchange='show_reason();'> <option value='ordered_by_mistake'>Customer put the order by mistake</option><option value='unable_to_install'>Unable to install at customer address</option><option value='changed_their_mind'>Customer changed their mind</option><option value='other'>Other</option></td></tr>";
		echo "<tr id='hidden_reason' style='display:none' ><td><label> Reason for cancellation </label></td><td><input type='text' id='reasonext'> </td></tr>";
		echo "<tr><td><label> Refund method </label></td><td><select id='type'> <option value='etransfer'>Refunded via e-transfer</option><option value='credit_card'>Refunded back to credit card</option><option value='bank_transfer'>Refunded back via bank transfer</option><option value='norefund'> No Refund </option></td></tr>";
		echo "<tr><td><label> Payment Refunded </label></td><td><input type='text' id='amount' placeholder='Amount Refunded'></td></tr>";
		echo "<tr><td><label> Refund Date </label></td><td><input type='date' id='refund_date' ></td></tr>";
		echo "<tr><td><label> Refund Transaction Number/ Reference Number</label></td><td><input type='text' id='ref_num' placeholder=''></td></tr>";

		echo "<tr><td><button onclick='ConfirmCancelled($uid);' > Confirm Cancellation </button></td></tr>";
		echo "</table><br><br>";
	}

	function show_form_for_confirm_order( $uid, $credit_card, $amount_due ) {
		echo " <h1>Enter Order Confirmed</h1>";
		echo "<br><br>";
		echo "<table>";
		echo "<tr><td><input type='hidden' id='state' value='105' ></td></tr>";
		echo "<tr><td><label> Transaction Number/ Reference Number</label></td><td><input type='text' id='ref_num' placeholder=''></td></tr>";
		echo "<tr><td><label> Amount </label></td><td><input type='text' id='amount' placeholder='Amount Paid'></td></tr>";
		echo "<tr><td><button onclick='ConfirmOrder($uid);' > Confirm Order </button></td></tr>";
		echo "</table><br><br>";
	}
	function show_form_for_confirm_payment_etransfer_submit( $uid, $credit_card, $amount_due ) {
		echo " <h1>Enter Confirmation of Payment </h1>";
		echo "<br><br>";
		echo "<table>";
		echo "<tr><td><input type='hidden' id='state' value='205' ></td></tr>";
		echo "<tr><td><label> Payment Confirmation Amount </label></td><td><input type='text' id='amount' placeholder='Amount paid'></td></tr>";
		echo "<tr><td><label> Transaction Number/ Reference Number</label></td><td><input type='text' id='ref_num' placeholder=''></td></tr>";
		if( $credit_card == true ) {
			echo "<tr><td><input type='hidden' id='type' value='credit card' > </td></tr>";
			echo "<tr><td><button onclick='ConfirmCreditCard($uid, \"$amount_due\");' > Confirm Payment </button></td></tr>";
		} else {
			echo "<tr><td><input type='hidden' id='type' value='e-transfer' > </td></tr>";
			echo "<tr><td><label> e-transfer from name </label></td><td><input type='text' id='etransfer_name' placeholder=''></td></tr>";
			echo "<tr><td><label> e-transfer from email </label></td><td><input type='text' id='etransfer_email' placeholder=''></td></tr>";
			echo "<tr><td><button onclick='ConfirmEtransfer($uid, \"$amount_due\");' > Confirm Payment </button></td></tr>";
		}
		echo "</table><br><br>";
	}

	function show_form_for_confirm_install_complete( $uid ) {
		echo " <h1>Enter Confirmation of Complete Installation </h1>";
		echo "<br><br>";
		echo "<table>";

		echo "<tr><td><input type='hidden' id='state' value='305' ></td></tr>";
		echo "<tr><td><label> Provider </label></td><td><select id='provider' onchange='show_provider();'> <option value='Bell'>Bell</option><option value='Rogers'>Rogers</option><option value='Shaw'>Shaw</option><option value='Cogeco'>Cogeco</option><option value='other'>Other</option></td></tr>";
		echo "<tr id='hidden_provider' style='display:none' ><td><label> Provider Detail </label></td><td><input type='text' id='providerext'> </td></tr>";

		echo "<tr><td><label> Local Account Number (ECN or Surf ID) </label></td><td><input type='text' id='accid' placeholder='Account number assigned at provider'></td></tr>";
		echo "<tr><td><label> Complete Date </label></td><td><input type='date' id='complete' placeholder='Booked Date --- if available'></td></tr>";

		echo "<tr><td><button onclick='ConfirmComplete($uid );'> Confirm Installation Complete </button></td></tr>";
		echo "</table><br><br>";
	}

	function show_form_for_confirm_submitted( $uid ) {
		echo " <h1>Enter Confirmation of Payment </h1>";
		echo "<br><br>";
		echo "<table>";
		echo "<tr><td><input type='hidden' id='state' value='220' ></td></tr>";
		echo "<tr><td><label> Provider </label></td><td><select id='provider' onchange='show_provider();'> <option value='Bell'>Bell</option><option value='Rogers'>Rogers</option><option value='Shaw'>Shaw</option><option value='Cogeco'>Cogeco</option><option value='other'>Other</option></td></tr>";
		echo "<tr id='hidden_provider' style='display:none' ><td><label> Provider Detail </label></td><td><input type='text' id='providerext'> </td></tr>";
		echo "<tr><td><label> Provider Account Number </label></td><td><input type='text' id='accid' placeholder='Account number assigned at provider'></td></tr>";
		echo "<tr><td><label> Booked Date </label></td><td><input type='date' id='booking' placeholder='Booked Date --- if available'></td></tr>";
		echo "<tr><td><button onclick='ConfirmSubmitted($uid );' > Confirm Submmitted </button></td></tr>";
		echo "</table><br><br>";
	}

	$sql = "SELECT * FROM signup WHERE id ='$uid';";
	$db = connect_db();
	$res = $db->query($sql);
	while( $obj = $res->fetch_object() )  {

		$id = $obj->id;
		$unique_id = $obj->unique_id;
		$status = $obj->status;
		$user_data = GetUserData($unique_id); 
                $upfront_text = base64_decode($obj->upfront);
                $upfront_data = json_decode( $upfront_text, true );
		$payinfo = GetUpfrontPaymentInfo($user_data);

		// if state is to confirm the payment
		if($state == 205 ) {
			show_form_for_confirm_payment_etransfer_submit( $id, $payinfo["cc"], GetGrandTotal($upfront_data) );
		}

		if($state == 105 ) {
			show_form_for_confirm_order( $id, $payinfo["cc"], GetGrandTotal($upfront_data) );
		}

		if( $state == 220 ) {
			show_form_for_confirm_submitted( $id );
		}

		if($state == 305 ) {
			show_form_for_confirm_install_complete( $id );
		}

		if( $state == 701 ) {
			show_form_for_confirm_cancellation( $id, $payinfo["cc"]);
		}

		ShowStates($db, $id);

		ShowCustomerDetails( $user_data );

		ShowUpfront($upfront_data);

		ShowUpfrontPaymentInfo($user_data);
	}

	$db->close();

?>

	<script>
	function show_provider() {
		var provider = document.getElementById("provider");
		var provider_selected = provider.options[provider.selectedIndex].value;
		var prov = document.getElementById("hidden_provider");
		if( provider_selected === "other" ) {
			prov.style.display = "block";
		} else {
			prov.style.display = "none";
			document.getElementById("providerext").value = "";
		}
	}

	function show_reason() {
		document.getElementById('refund_date').valueAsDate = new Date();
		var provider = document.getElementById("reason");
		var provider_selected = provider.options[provider.selectedIndex].value;
		var prov = document.getElementById("hidden_reason");
		if( provider_selected === "other" ) {
			prov.style.display = "block";
		} else {
			prov.style.display = "none";
			document.getElementById("reasonext").value = "";
		}
	}

	function SendRequest(uid, data) {

		var state = document.getElementById("state").value;

		param = "?submit_values=1&state=" + state + "&uid=" + uid + "&data=" + data;

		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if( this.readyState == 4 && this.status == 200 ) {
				res = xhr.responseText;
				if( res.trim() === "success" ) {
					alert(" Saved ");
					document.body.innerHTML = res;
				} else {
					alert( res );
				}
			}
		};

		xhr.open("GET", "enter_value.php" + param);
		//xhr.setRequestHeader("Accept", "application/json");
		//xhr.setRequestHeader("content-type", "application/json");
		xhr.send();
	}

	function ConfirmComplete(id) {
		var provider = document.getElementById("provider");
		var provider_selected = provider.options[provider.selectedIndex].value;
		var provider_extended = document.getElementById("providerext").value;
		var accid = document.getElementById("accid").value;
		var booking = document.getElementById("complete").value;

		const obj = {provider:provider_selected, provider_ext:provider_extended, localaccountid:accid, completion_date:booking};
		data = JSON.stringify(obj);
		data = btoa(data);
		SendRequest( id, data );
	}

	function ConfirmSubmitted( id ) {
		var provider = document.getElementById("provider");
		var provider_selected = provider.options[provider.selectedIndex].value;
		var provider_extended = document.getElementById("providerext").value;
		var accid = document.getElementById("accid").value;
		var booking = document.getElementById("booking").value;

		const obj = {provider:provider_selected, provider_ext:provider_extended, accountid:accid, booking_date:booking};
		data = JSON.stringify(obj);
		data = btoa(data);
		SendRequest( id, data );
	}

	function ConfirmEtransfer(id, due) {
		var amount_paid = document.getElementById("amount").value;
		var ref_numv = document.getElementById("ref_num").value;
		var etrname = document.getElementById("etransfer_name").value;
		var etremail = document.getElementById("etransfer_email").value;
		var typev = document.getElementById("type").value;

		amount_paid = amount_paid.trim();
		due = due.replace(/\$/g, "");
		amount_paid = amount_paid.replace(/\$/g, "");
		amount_paid = parseFloat(amount_paid).toFixed(2);
		if( due != amount_paid ) {
			c = confirm("The amount paid " + amount_paid + " do not match the due " + due);
			if( c == false ) {
				return;
			}
		}

		const obj = {type:typev, amount:amount_paid, ref_num:ref_numv, etransfer_name:etrname, etransfer_email:etremail};
		data = JSON.stringify(obj);
		data = btoa(data);
		SendRequest( id, data );
	}
	function ConfirmOrder(id) {
		var amount = document.getElementById("amount").value;
		var ref_numv = document.getElementById("ref_num").value;

		amount = amount.replace(/\$/g, "");
		const obj = {amount:amount, ref_num:ref_numv};
		data = JSON.stringify(obj);

		data = btoa(data);
		SendRequest( id, data );
	}
	function ConfirmCancelled(id) {
		var amount = document.getElementById("amount").value;
		var ref_numv = document.getElementById("ref_num").value;
		var typev = document.getElementById("type").value;
		var refund_datev = document.getElementById("refund_date").value;
		var reason = document.getElementById("reason");
		var reason_selected = reason.options[reason.selectedIndex].value;
		var reasonext = document.getElementById("reasonext").value;
		reasonextb64 = btoa(reasonext);

		amount = amount.replace(/\$/g, "");
		const obj = {reason:reason_selected, reasonext:reasonextb64, type_refund:typev, amount_refunded:amount, ref_num:ref_numv, refund_date:refund_datev };
		data = JSON.stringify(obj);

		data = btoa(data);
		SendRequest( id, data );
	}

	function ConfirmCreditCard(id, due) {
		var amount_paid = document.getElementById("amount").value;
		var ref_numv = document.getElementById("ref_num").value;
		var typev = document.getElementById("type").value;

		amount_paid = amount_paid.trim();
		due = due.replace(/\$/g, "");
		amount_paid = amount_paid.replace(/\$/g, "");
		amount_paid = parseFloat(amount_paid).toFixed(2);
		if( due != amount_paid ) {
			c = confirm("The amount paid " + amount_paid + " do not match the due " + due);
			if( c == false ) {
				return;
			}
		}
		const obj = {type:typev, amount:amount_paid, ref_num:ref_numv };
		data = JSON.stringify(obj);
		data = btoa(data);

		SendRequest( id, data );
	}

	</script>
</body>

</html>
<?PHP
}
?>

