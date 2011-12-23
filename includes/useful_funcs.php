<?php

require_once($website_dir."includes/class.phpmailer.php");
require_once($website_dir."includes/smsparam.php");

function homepageRedirect() {
	header('Location:'.$homepage);
}

// trims whitespace, slashes, prepares for database input
function safeSql($input) {
        return mysql_real_escape_string(stripslashes(trim($input)));
}

//////////////////////////////////////// 
// 
// PHP function to validate US phone number: 
// (c) 2003 
// No restrictions have been placed on 
// the use of this code 
// 
// Updated Friday Jan 9 2004 to optionally ignore
// the area code:
//
// Input: a single string parameter and an
// optional boolean variable (default=true)
// Output: 10 digit telephone number 
// or boolean false(0) 
// 
// The function will return the numerical part 
// of the alphanumeric string parameter with 
// the following sequence of characters: 
// any number of spaces [optional], a single 
// open parentheses [optional], any number of 
// spaces [optional], 3 digits (area 
// code), any number of spaces [optional], a 
// single close parentheses [optional], a single 
// dash [optional], any number of spaces 
// [optional], 3 digits, any number of spaces 
// [optional], a single dash [optional], any 
// number of spaces [optional], 4 digits, any 
// number of spaces [optional]: 
// 
//////////////////////////////////////// 
function validate_phone($phonenumber,$useareacode=true) 
{ 
if ( preg_match("/^[ ]*[(]{0,1}[ ]*[0-9]{3,3}[ ]*[)]{0,1}[-]{0,1}[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/",$phonenumber) || (preg_match("/^[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/",$phonenumber) && !$useareacode)) return eregi_replace("[^0-9]", "", $phonenumber); 
return false; 
}

/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email
address format and the domain exists.

Source: http://www.linuxjournal.com/article/9585?page=0,3
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

function getUrl($url, $cookie, $fields = NULL) {
	$options = array(
		CURLOPT_URL => $url,
		CURLOPT_COOKIEJAR => $cookie,
		CURLOPT_COOKIEFILE => $cookie,
		CURLOPT_RETURNTRANSFER => true,
		// this is required
		CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty) Firefox/4.2', // lol - booked
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYPEER => false
	);

    $fields_string = '';

	if(isset($fields)) {
		//url-ify the data for the POST
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&';
		}
		rtrim($fields_string,'&');
		
		$options[CURLOPT_POST] = count($fields);
		$options[CURLOPT_POSTFIELDS] = $fields_string;
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
}

function jobMineMe($userid, $password) {
	$pwd = '/tmp/';

	// create a temporary cookie file for the user - om nom nom nom nom nom nom
	$cookie = $pwd.$userid.'_session';
	$fh = fopen($cookie, 'w') or die('Can\'t temporarily store session.');
	fclose($fh);

	// set base URL and POST variables
	$jm_base_url = 'https://jobmine.ccol.uwaterloo.ca/psp/SS/';//servlets/iclientservlet/SS/';
	$jm_frame_url = 'https://jobmine.ccol.uwaterloo.ca/psc/SS/';
	$login_fields = array("cmd" => urlencode("login"), "languageCd" => urlencode("ENG"), "sessionId" => urlencode(""), "httpPort" => urlencode(""), "timezoneOffset" => urlencode("0"), "userid" => urlencode($userid), "pwd" => urlencode($password), "submit" => urlencode("Submit"));

	// initialize cookies
	getUrl($jm_base_url, $cookie, array());

	// log in to jobmine
	getUrl($jm_base_url, $cookie, $login_fields);

	// browse to application page
	$app_pg = getUrl($jm_frame_url."EMPLOYEE/WORK/c/UW_CO_STUDENTS.UW_CO_APP_SUMMARY.GBL?pslnkid=UW_CO_APP_SUMMARY_LINK&FolderPath=PORTAL_ROOT_OBJECT.UW_CO_APP_SUMMARY_LINK&IsFolder=false&IgnoreParamTempl=FolderPath%2cIsFolder&PortalActualURL=https%3a%2f%2fjobmine.ccol.uwaterloo.ca%2fpsc%2fSS%2fEMPLOYEE%2fWORK%2fc%2fUW_CO_STUDENTS.UW_CO_APP_SUMMARY.GBL%3fpslnkid%3dUW_CO_APP_SUMMARY_LINK&PortalContentURL=https%3a%2f%2fjobmine.ccol.uwaterloo.ca%2fpsc%2fSS%2fEMPLOYEE%2fWORK%2fc%2fUW_CO_STUDENTS.UW_CO_APP_SUMMARY.GBL%3fpslnkid%3dUW_CO_APP_SUMMARY_LINK&PortalContentProvider=WORK&PortalCRefLabel=Applications&PortalRegistryName=EMPLOYEE&PortalServletURI=https%3a%2f%2fjobmine.ccol.uwaterloo.ca%2fpsp%2fSS%2f&PortalURI=https%3a%2f%2fjobmine.ccol.uwaterloo.ca%2fpsc%2fSS%2f&PortalHostNode=WORK&NoCrumbs=yes&PortalKeyStruct=yes", $cookie);//?ICType=Panel&Menu=UW_CO_STUDENTS&Market=GBL&PanelGroupName=UW_CO_APP_SUMMARY&RL=&target=main0&navc=5170", $cookie);

	// if login unsuccessful, unlink cookie, return false
	if(!strstr($app_pg, "<title>Student App Summary</title>")) {
		unlink($cookie);
		return false;
	}
	
	// log out of jobmine
	getUrl($jm_base_url."EMPLOYEE/WORK/?cmd=logout", $cookie);//?cmd=logout", $cookie);

	// delete the cookie file
	unlink($cookie);
	
	return $app_pg;
}

function md5NoSession($page) {
	// hack to get rid of the session id from the page and store the rest as an md5 hash for comparison - yes, i know, hacky, but it works... for now...
	return md5(preg_replace("<input type='hidden' name='ICSID' id='ICSID' value=(.*)/>", "", $page));
}

function parsePage($page_html) {
	// load the page into the DOM
	$jobs = array();
	$dom = new domDocument();
	@$dom->loadHTML($page_html);
	$dom->preserveWhiteSpace = false;

	$table = $dom->getElementsByTagName('table')->item(19); 
	$rows = $table->getElementsByTagName('tr');

	$i = true;
	foreach($rows as $row) {
		// hack to skip headers
		if($i == true) { $i = false; continue; }

		$cols = $row->getElementsByTagName('td');
		$jobs[(int)safeSql($cols->item(0)->nodeValue)] = array(
			'title'		=> safeSql($cols->item(1)->nodeValue), 
			'employer'	=> safeSql($cols->item(2)->nodeValue), 
			'job_status'	=> safeSql($cols->item(5)->nodeValue), 
			'app_status'	=> safeSql($cols->item(6)->nodeValue)
		);
	}

	return $jobs;
}

function phpMailerSend($to, $subject, $body) {
	// let's send an e-mail!!
	$mail = new PHPMailer();
	$mail->IsSMTP();									// set mailer to use SMTP
	$mail->Host 	= $mailer_host;						// specify main and backup server
	$mail->SMTPAuth = true;								// turn on SMTP authentication
	$mail->Username = $mailer_user;						// SMTP username
	$mail->Password = $mailer_pass;						// SMTP password
	$mail->From		= $mailer_user;
	$mail->FromName = "JobMine Interview Notifier";
	$mail->AddAddress($to);
	$mail->AddReplyTo($mailer_user);
	$mail->IsHTML(false);
	$mail->Subject = $subject;
	$mail->Body = $body;

	return $mail->Send();
}

function sendSms($number, $interview_count = 0, $message = '') {
	/*
	if($message == '' && $interview_count != 0) {
		$message = "You have ".$interview_count." new interview(s). Please log in to JobMine for more details.";
	}

	if($message != '') {
		$url = 'https://api.tropo.com/1.0/sessions?action=create&token='.$tropo_api_token.'&number='.$number.'&message='.urlencode($message);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		curl_close($ch);
	}
	*/
}


?>
