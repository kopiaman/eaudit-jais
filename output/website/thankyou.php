<?php include_once('head.php'); 
include('../include/x_smtp.php');
require '../include/phpmailer/PHPMailerAutoload.php';

$note=" Sila Klik butang dibawah sekiranya anda telah menjawab maklum balas";

$fid=$_GET['fid'];

$sql_status= "
SELECT
audit_form.ref,
audit_form.aStatus,
`user`.fullName AS nameAuditor,
`user`.email AS emailAuditor,
auditee.fullname AS nameAuditee,
auditee.umail AS emailAuditee
FROM audit_form
LEFT JOIN `user` ON audit_form.userID = `user`.userID
LEFT JOIN auditee ON audit_form.userAuditee = auditee.aid
WHERE audit_form.fid=$fid
";
$query_status=mysql_query($sql_status,$conn);
$row_status=mysql_fetch_array($query_status);

$auditorEmail=$row_status['emailAuditor'];
$auditeeEmail=$row_status['emailAuditee'];
//pengiraan maklujm balas
$iStatus=$row_status['aStatus'];


$sql_set="SELECT fieldName AS url FROM set_field WHERE fieldValue='mail-url'";
$query_set=mysql_query($sql_set,$conn);
$set=mysql_fetch_array($query_set);

$url=$set['url'];

$today=date('Y-m-d H:i:s');
//update Status Audit Form serta tarikh maklum balas ke kolum yang berkaitan
if($iStatus=='Query Dihantar'){
$newStatus='Maklum Balas 1'; 
$dateAnswer="dateAnswer='$today'";
}else if($iStatus=='Peringatan Mesra'){
$newStatus='Maklum Balas 1'; 
$dateAnswer="dateAnswer='$today'";
}else if($iStatus=='Peringatan 1'){
$newStatus='Maklum Balas 1'; 
$dateAnswer="dateAnswer='$today'";
}else if($iStatus=='Peringatan 2'){
$newStatus='Maklum Balas 1'; 
$dateAnswer="dateAnswer='$today'";
}else if($iStatus=='Susulan 1'){
$newStatus='Maklum Balas 2'; 
$dateAnswer="dateAnswer2='$today'";
}else if ($iStatus=='Susulan 2'){
$newStatus='Maklum Balas 3'; 
$dateAnswer="dateAnswer3='$today'";
}else{
$newStatus=$iStatus;
$dateAnswer=$today;
};



if((isset($_POST["MM_abstract"])) && ($_POST["MM_abstract"]=="abstractForm")) 
{ 
   
	$to2=$auditorEmail; 
	$subject2 =" $newStatus - Pemerhatian Audit:  $row_status[ref] ";
	$headers2 ="From: " . $auditeeEmail . "\r\n";
	$headers2 .="Content-type: text/html;charset=UTF-8" ."  \r\n";
	
	$message2 ="<html><body>";
	$message2 .="السلام عليكم ورحمة الله وبركاته  <br/><br/>";
	$message2 .="Mohon semakan Pemerhatian Audit daripada auditi seperti berikut :-  <br/> <br/>";
	$message2 .="No. Query: $row_status[ref] <br/>  <br/>";
	$message2 .="LINK : <a href='$url' target='_blank'> $url</a> <br/> <br/>";
	$message2 .="Sekian, Terima Kasih <br/><br/>";
	
	$message2 .='" مڠهارڤ كريضأن الله "';
	$message2 .="<br/>";
	$message2.='"أمانه، ڤريهاتين دان مسرا " ';
	
	$message2 .= "</p></body></html>";


    //update ke new status
	 $sql_abstractAdd ="UPDATE audit_form SET aStatus='$newStatus',$dateAnswer WHERE fid='$fid'";
	 $abstractQuery=mysql_query($sql_abstractAdd,$conn);	
	 
	 
		    if($abstractQuery)
			{

			$note="Terima kasih atas maklum balas anda.  "; 
			//$mailto=mail($to2, $subject2, $message2, $headers2);	

			include('../include/x_smtp_send.php');
/*
			 		$mail = new PHPMailer;
                    $mail->isSMTP();                                      
					$mail->Host =SMTPSERVER;                       
					$mail->SMTPAuth = true;  
					$mail->CharSet = 'UTF-8';                              
					$mail->Username = SMTPUSER;                   
					$mail->Password = SMTPPWD;            
					$mail->SMTPSecure = 'tls';                            
					$mail->Port = PORT;                                   
					$mail->setFrom(SETFROMMAIL,SETFROMMAILNAME);      
					$mail->WordWrap = 50;                                 
					$mail->isHTML(true);                                  
					$mail->AddAddress( $to2 , $to2 );
					$mail->Subject = $subject2;
					$mail->Body    = $message2;
					$mail->msgHTML($message2);
					 
					if(!$mail->send()) 
					{
					   echo 'Message could not be sent.';
					   echo 'Mailer Error: ' . $mail->ErrorInfo;
					   exit;
					}
					*/
 
		  }else{
			$note="Anda telah menghantar maklum balas. Terima Kasih";
			//$mailto=mail($to2, $subject2, $message2, $headers2);
			};
};
?>
<body role="document">
   <section id="contactArea">
    	<div class="container">     
       	<h1 class="text-center">PENGESAHAN</h1>
           <span class="border"> <i class="fa fa-plane"></i> </span> 
         <div class="row">
        
				
                    <div class="col-sm-12">
              <?php  echo '<h2>'.$note.'</h2>' ?>
          <?php  if(!isset($_POST["MM_abstract"])){  //hide form if already submitted 
		 
		  ?>        
                       <form id="abstractForm" class="form-horizontal" method="post" role="form" enctype="multipart/form-data">
                        <input type="hidden" class="form-control" id="MM_abstract" name="MM_abstract" value="abstractForm"  >
						
                        
                       
                        <p><input type="submit" class="btn" value="HANTAR JAWAPAN"></p>
   					 </form>
                    </div>
                    <?php } ?>
		</div></div>
      </section>
	
    <!-- start footer area -->
 <?php include_once('footer.php') ?>
    <!-- end footer area -->
</body>
  
</html>
