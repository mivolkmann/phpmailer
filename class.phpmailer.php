<?php
////////////////////////////////////////////////////
// phpmailer - PHP email class
// 
// Version 1.06, 06/01/2001
//
// Class for sending email using either 
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Author: Brent R. Matzelle <bmatzelle@yahoo.com>
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

class phpmailer
{
	/////////////////////////////////////////////////
	// CLASS VARIABLES
	/////////////////////////////////////////////////
	
	// "Public" Variables
	var $Priority         = 3;
	var $CharSet          = "iso-8859-1";
	var $ContentType      = "text/plain";
	var $Encoding         = "8bit";
	var $From             = "root@localhost";
	var $FromName         = "root";
	var $Subject          = "";
	var $Body             = "";
	var $WordWrap         = false;
	var $Mailer           = "mail";
	var $Sendmail         = "/usr/sbin/sendmail";
	var $MailerDebug      = true;
	var $UseMSMailHeaders = false;

	// SMTP "Public" variables
	var $Host        = "localhost";
	var $Port        = 25;
	var $Helo        = "localhost.localdomain";
	var $Timeout     = 10; // Socket timeout in sec.
	var $SMTPDebug   = false;
	
	// "Private" variables
	var $Version       = "phpmailer [version 1.06]";
	var $to            = array();
	var $cc            = array();
	var $bcc           = array();
	var $ReplyTo       = array();
	var $attachment    = array();
	var $CustomHeader  = array();	
	var $boundary      = false;
	
	/////////////////////////////////////////////////
	// VARIABLE METHODS
	/////////////////////////////////////////////////

	// Sets message to HTML
	function IsHTML($bool) {
		if($bool == true)
			$this->ContentType = "text/html";
		else
			$this->ContentType = "text/plain";
	}

	// Sets Mailer to use SMTP
	function IsSMTP() {
		$this->Mailer = "smtp";
	}

	// Sets Mailer to use PHP mail() function
	function IsMail() {
		$this->Mailer = "mail";
	}

	// Sets Mailer to directly use $Sendmail program
	function IsSendmail() {
		$this->Mailer = "sendmail";
	}

	// Sets $Sendmail to qmail MTA
	function IsQmail() {
		//$this->Sendmail = "/var/qmail/bin/qmail-inject";
		$this->Sendmail = "/var/qmail/bin/sendmail";
		$this->Mailer = "sendmail";
	}


	/////////////////////////////////////////////////
	// RECIPIENT METHODS
	/////////////////////////////////////////////////	

	// Add a "to" address
	function AddAddress($address, $name = "") {
		$cur = count($this->to);
		$this->to[$cur][0] = trim($address);
		$this->to[$cur][1] = $name;
	}
	
	// Add a "cc" address
	function AddCC($address, $name = "") {
		$cur = count($this->cc);
		$this->cc[$cur][0] = trim($address);
		$this->cc[$cur][1] = $name;
	}
	
	// Add a "bcc" address
	function AddBCC($address, $name = "") {
		$cur = count($this->bcc);
		$this->bcc[$cur][0] = trim($address);
		$this->bcc[$cur][1] = $name;
	}
	
	// Add a "Reply-to" address
	function AddReplyTo($address, $name = "") {
		$cur = count($this->ReplyTo);
		$this->ReplyTo[$cur][0] = trim($address);
		$this->ReplyTo[$cur][1] = $name;
	}


	/////////////////////////////////////////////////
	// MAIL SENDING METHODS
	/////////////////////////////////////////////////
	
	// Create message and assign to mailer
	function Send() {
		if(count($this->to) < 1)
			$this->error_handler("You must provide at least one recipient email address");

		$header = $this->create_header();
		$body = $this->create_body();
		
      // Choose the mailer
		if($this->Mailer == "sendmail")
			$this->sendmail_send($header, $body);
		elseif($this->Mailer == "mail")
			$this->mail_send($header, $body);
		elseif($this->Mailer == "smtp")
			$this->smtp_send($header, $body);
		else
			$this->error_handler(sprintf("%s mailer is not supported", $this->Mailer));
	}
	
	// Send using the $sendmail program
	function sendmail_send($header, $body) {
		$sendmail = sprintf("%s -t", $this->Sendmail);

		if(!@$mail = popen($sendmail, "w"))
			$this->error_handler(sprintf("Could not execute %s", $this->Sendmail));
		
		fputs($mail, $header);
		fputs($mail, $body);
		pclose($mail);
	}
	
	// Send via the PHP mail() function
	function mail_send($header, $body) {
		// Create mail recipient list
		$to = $this->to[0][0]; // no extra comma
		for($i = 1; $i < count($this->to); $i++)
			$to .= sprintf(",%s", $this->to[$i][0]);
		for($i = 0; $i < count($this->cc); $i++)
			$to .= sprintf(",%s", $this->cc[$i][0]);
		for($i = 0; $i < count($this->bcc); $i++)
			$to .= sprintf(",%s", $this->bcc[$i][0]);
		
		if(!@mail($to, $this->Subject, $body, $header))
			$this->error_handler("Could not instantiate mail()");
	}
	
	// Send message via SMTP using PhpSMTP
	// PhpSMTP written by Chris Ryan
	function smtp_send($header, $body) {
	   // Include SMTP class code, but not twice
      include_once("class.smtp.php"); // Load code only if asked

		$smtp = new SMTP;
		$smtp->do_debug = $this->SMTPDebug;
		
		// Try to connect to all SMTP servers
		$hosts = explode(";", $this->Host);
		$index = 0;
		$connection = false;
		
		// Retry while there is no connection
		while($index < count($hosts) && $connection == false)
		{
			if($smtp->Connect($hosts[$index], $this->Port, $this->Timeout))
				$connection = true;
			//printf("%s host could not connect<br>", $hosts[$index]); //debug only
			$index++;
		}
		if(!$connection)
			$this->error_handler("SMTP Error: could not connect to SMTP host server(s)");
	  	  
		$smtp->Hello($this->Helo);
		$smtp->Mail(sprintf("<%s>", $this->From));
		
		for($i = 0; $i < count($this->to); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->to[$i][0]));
		for($i = 0; $i < count($this->cc); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->cc[$i][0]));
		for($i = 0; $i < count($this->bcc); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->bcc[$i][0]));

		$smtp->Data(sprintf("%s%s", $header, $body));
		$smtp->Quit();		
	}
	

	/////////////////////////////////////////////////
	// MESSAGE CREATION METHODS
	/////////////////////////////////////////////////
	
	// Creates recipient headers
	function addr_append($type, $addr) {
		$addr_str = "";
		$addr_str .= sprintf("%s: %s <%s>", $type, $addr[0][1], $addr[0][0]);
		if(count($addr) > 1)
		{
			for($i = 1; $i < count($addr); $i++)
			{
				$addr_str .= sprintf(", %s <%s>", $addr[$i][1], $addr[$i][0]);
			}
			$addr_str .= "\n";
		}
		else
			$addr_str .= "\n";
		
		return($addr_str);
	}
	
	// Wraps message for use with mailers that don't 
	// automatically perform wrapping
	// Written by philippe@cyberabuse.org
	function wordwrap($message, $length) {
		$line = explode("\n", $message);
		$message = "";
		for ($i=0 ;$i < count($line); $i++) 
		{
			$line_part = explode(" ", trim($line[$i]));
			$buf = "";
			for ($e = 0; $e<count($line_part); $e++) 
			{
				$buf_o = $buf;
				if ($e == 0)
					$buf .= $line_part[$e];
				else 
					$buf .= " " . $line_part[$e];
				if (strlen($buf) > $length and $buf_o != "")
				{
					$message .= $buf_o . "\n";
					$buf = $line_part[$e];
				}
			}
			$message .= $buf . "\n";
		}
		return ($message);
	}
	
	// Assembles and returns the message header
	function create_header() {
		$header = array();
		$header[] = sprintf("Date: %s\n", date("D M j G:i:s T"));
		$header[] = $this->addr_append("To", $this->to);
		$header[] = sprintf("From: %s <%s>\n", $this->FromName, trim($this->From));
		if(count($this->cc) > 0)
			$header[] = $this->addr_append("cc", $this->cc);
		if(count($this->bcc) > 0)
			$header[] = $this->addr_append("bcc", $this->bcc);
		if(count($this->ReplyTo) > 0)
			$header[] = $this->addr_append("Reply-to", $this->ReplyTo);
		$header[] = sprintf("Subject: %s\n", trim($this->Subject));
		$header[] = sprintf("X-Priority: %d\n", $this->Priority);
		$header[] = sprintf("X-Mailer: %s\n", $this->Version);
		$header[] = sprintf("Content-Transfer-Encoding: %s\n", $this->Encoding);
		$header[] = sprintf("Return-Path: %s\n", trim($this->From));
		
		// Add custom headers
		for($index = 0; $index < count($this->CustomHeader); $index++)
		   $header[] = sprintf("%s\n", $this->CustomHeader[$index]);
		
		if($this->UseMSMailHeaders)
		   $header[] = $this->AddMSMailHeaders();
		
		// Add all attachments
		if(count($this->attachment) > 0)
		{
			$header[] = sprintf("Content-Type: Multipart/Mixed; charset = \"%s\";\n", $this->CharSet);
			$header[] = sprintf(" boundary=\"Boundary-=%s\"\n", $this->boundary);
		}
		else
			$header[] = sprintf("Content-Type: %s; charset = \"%s\";\n", $this->ContentType, $this->CharSet);
		
		$header[] = "MIME-Version: 1.0\n\n";
		return(join("", $header));
	}

	// Assembles and returns the message body
	function create_body() {
		// wordwrap the message body if set
		if($this->WordWrap)
			$this->Body = $this->wordwrap($this->Body, $this->WordWrap);

		if(count($this->attachment) > 0)
			$body = $this->attach_all();
		else
			$body = $this->Body;
		
		return($body);		
	}
	
	
	/////////////////////////////////////////////////
	// ATTACHMENT METHODS
	/////////////////////////////////////////////////

	// Check if attachment is valid and add to list
	function AddAttachment($path, $name = "") {
		if(!@is_file($path))
			$this->error_handler(sprintf("Could not find %s file on filesystem", $path));
		
		$filename = basename($path);
		if($name == "")
		   $name = $filename;
		
		// Set message boundary
		$this->boundary = "_b" . md5(uniqid(time()));

		// Append to $attachment array
		$cur = count($this->attachment);
		$this->attachment[$cur][0] = $path;
		$this->attachment[$cur][1] = $filename;
		$this->attachment[$cur][2] = $name;
	}

	// Attach text and binary attachments to body
	function attach_all() {
		// Return text of body
		$mime = array();
		$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
		$mime[] = sprintf("Content-Type: %s\n", $this->ContentType);
		$mime[] = "Content-Transfer-Encoding: 8bit\n\n";
		$mime[] = sprintf("%s\n", $this->Body);
		
		// Add all attachments
		for($i = 0; $i < count($this->attachment); $i++)
		{
			$path = $this->attachment[$i][0];
			$filename = $this->attachment[$i][1];
			$name = $this->attachment[$i][2];
			$mime[] = sprintf("--Boundary-=%s\n", $this->boundary);
			$mime[] = "Content-Type: application/octet-stream;\n";
			$mime[] = sprintf("name=\"%s\"\n", $name);
			$mime[] = "Content-Transfer-Encoding: base64\n";
			$mime[] = sprintf("Content-Disposition: attachment; filename=\"%s\"\n\n", $name);
			$mime[] = sprintf("%s\n\n", $this->encode_file($path));
		}
		$mime[] = sprintf("\n--Boundary-=%s--\n", $this->boundary);
		
		return(join("", $mime));
	}
	
	// Encode attachment in base64 format
	function encode_file ($path) {
		if(!@$fd = fopen($path, "r"))
			$this->error_handler("File Error: Could not open file %s", $path);
		$file = fread($fd, filesize($path));
		
		// chunk_split is found in PHP >= 3.0.6
		$encoded = chunk_split(base64_encode($file));
		fclose($fd);
		
		return($encoded);
	}
	
	/////////////////////////////////////////////////
	// MESSAGE RESET METHODS
	/////////////////////////////////////////////////
	
	// Clears any recipients assigned in the TO array
	function ClearAddresses() {
	   $this->to = array();
	}
	
	// Clears any recipients assigned in the CC array
	function ClearCCs() {
	   $this->cc = array();
	}
	
	// Clears any recipients assigned in the BCC array
	function ClearBCCs() {
	   $this->bcc = array();
	}
	
	// Clears any recipients assigned in the ReplyTo array
	function ClearReplyTos() {
	   $this->ReplyTo = array();
	}
	
	// Clears all recipients assigned int the TO, CC and BCC array
	function ClearAllRecipients() {
	   $this->to = array();
	   $this->cc = array();
	   $this->bcc = array();
	}
	
	// Clear all previously set attachments
	function ClearAttachments() {
	   $this->attachment = array();
	}
	
	// Clear all custom headers
	function ClearCustomHeaders() {
	   $this->CustomHeader = array();
	}
	
	
	/////////////////////////////////////////////////
	// MISCELLANEOUS METHODS
	/////////////////////////////////////////////////

	// Print out error and exit
	function error_handler($msg) {
		if($this->MailerDebug == true)
		{
			print("<h2>Mailer Error</h2>");
			print("Description:<br>");
			printf("<font color=\"FF0000\">%s</font>", $msg);
			exit;
		}
	}
	
	// Add a custom header
	function AddCustomHeader($custom_header) {
	   $this->CustomHeader[] = $custom_header;
	}
	
	// Add all the Microsoft message headers
	function UseMSMailHeaders() {
	   $MSHeader = "";
	   if($this->Priority == 1)
	      $MSPriority = "High";
	   elseif($this->Priority == 5)
	      $MSPriority = "Low";
	   else
	      $MSPriority = "Medium";
	      
	   $MSHeader .= sprintf("X-MSMail-Priority: %s\n", $MSPriority);
	   $MSHeader .= sprintf("Importance: %s\n", $MSPriority);
	   return($MSHeader);
	}
	
	// Print out the version number of phpmailer
	function PrintVersion() {
	   //printf("<h5><a href=\"http://phpmailer.sourceforge.net\">%s</a></h5>", $this->Version);
	   printf("%s", $this->Version);
	}
}

// End of class
?>