<?PHP

function EmailGetCustomerDetails( $user_data ) {

	$ret = '<h2 style="display: block; margin: 0px; font-size: 28px; line-height: 34px; height:60px; text-decoration : underline; padding-top: 12px; padding-bottom: 0px; margin-top: 30px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-style: normal; font-weight: 700; text-transform: none; color: #222222; text-align: center; background-color: #D3D3D3;">Customer Details</h2>';

	$ret .= '<table  style="max-width:720px;margin:0 auto;border-collapse: collapse; border:1px solid #D3D3D3;"  class="order-info-split-table"  width="100%" cellspacing="0" cellpadding="0" border="0">';
	$ret .= '<tbody>';
	$ret .= "<tr> <td style='min-width:320px;padding: 12px; text-align: left;' valign='middle' align='left'><strong>Customer First Name</strong></td>";
	$ret .= "<td  style='padding: 12px; text-align: left;' valign='middle' align='left'>".$customer_data[$key]['value']."</td>";
	$ret .= "</tr>";
	$ret .= "<tr> <td></td><td></td> </tr> </tbody> </table>";

        $ret .= '<h2 style="display: block; margin: 0px; font-size: 28px; line-height: 34px; height:60px; text-decoration : underline; padding-top: 12px; padding-bottom: 0px; margin-top: 30px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-style: normal; font-weight: 700; text-transform: none; color: #222222; text-align: center; background-color: #D3D3D3">Upfront Billing Details</h2>';

	return $ret;
}

function SendCustomerProcessingOrder( $user_data ) {

	$to = $user_data>email;
	$subject = "Hi " . $user_data->firstname;
	$message = "";
	$message 
}

//Set the reciever's email address
$to = "ihab.khalil@gmail.com";

//Set the subject of the email
$subject = "It is a testing email";

//Set the email body
$message = "It is testing email body";

//Set the header information
$headers = "From: emailtesting1@diallog.com\r\n";
$headers .= "Reply-To: emailtesting1@diallog.com\r\n";


//Send email using message mail() function

if(mail($to,$subject,$message,$headers))

{

echo "Email has sent successfully.\r\n";

}

else{

echo "Email has not sent. <br />";

}



?>

