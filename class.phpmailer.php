<?php
////////////////////////////////////////////////////
// phpmailer - PHP email class
// 
// Version 1.07, 06/05/2001
//
// Class for sending email using either 
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// Author: Brent R. Matzelle <bmatzelle@yahoo.com>
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * phpmailer - PHP email transport class
 * @author Brent R. Matzelle
 */
class phpmailer
{
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////
	
	/**
    * Email priority (1 = High, 3 = Normal, 5 = low). Default value is 3.
    * @public
    * @type int
    */
	var $Priority         = 3;

	/**
    * Sets the CharSet of the message. Default value is "iso-8859-1".
    * @public
    * @type string
    */	
	var $CharSet          = "iso-8859-1";

	/**
    * Sets the Content-type of the message. Default value is "text/plain".
    * @public
    * @type string
    */	
	var $ContentType      = "text/plain";

	/**
    * Sets the Encoding of the message. Default value is "8bit".
    * @public
    * @type string
    */
	var $Encoding         = "8bit";

	/**
    * Sets the From email of the message. Default value is "root@localhost".
    * @public
    * @type string
    */
	var $From             = "root@localhost";

	/**
    * Sets the From name of the message. Default value is "root".
    * @public
    * @type string
    */
	var $FromName         = "root";

	/**
    * Sets the Subject of the message. Default value is "".
    * @public
    * @type string
    */
	var $Subject          = "";

	/**
    * Sets the Body of the message. Default value is "".
    * @public
    * @type string
    */
	var $Body             = "";

	/**
    * Sets word wrapping on the message. Default value is false (off).
    * @public
    * @type string
    */
	var $WordWrap         = false;

	/**
    * Method to send mail: ("mail", "sendmail", or "smtp"). 
    * Default value is "mail".
    * @public
    * @type string
    */
	var $Mailer           = "mail";

	/**
    * Sets the path of the sendmail program. Default value is 
    * "/usr/sbin/sendmail".
    * @public
    * @type string
    */
	var $Sendmail         = "/usr/sbin/sendmail";

	/**
    *  Turns phpmailer debugging on or off. Default value is false (off).
    *  @public
    *  @type bool
    */
	var $MailerDebug      = true;

	/**
    *  Turns Microsoft mail client headers on and off. Default value is false (off).
    *  @public
    *  @type bool
    */
	var $UseMSMailHeaders = false;


	/////////////////////////////////////////////////
	// SMTP VARIABLES
	/////////////////////////////////////////////////
	
	/**
    *  Sets the SMTP host. Default value is "localhost".
    *  @public
    *  @type string
    */
	var $Host        = "localhost";

	/**
    *  Sets the SMTP server port. Default value is 25.
    *  @public
    *  @type int
    */
	var $Port        = 25;

	/**
    *  Sets the CharSet of the message. Default value is 
    *  @public
    *  @type string
    */
	var $Helo        = "localhost.localdomain";

	/**
    *  Sets the SMTP server timeout. Default value is 10.
    *  @public
    *  @type int
    */
	var $Timeout     = 10; // Socket timeout in sec.

	/**
    *  Sets SMTP class debugging on or off. Default value is false (off).
    *  @public
    *  @type bool
    */
	var $SMTPDebug   = false;

	
	/////////////////////////////////////////////////
	// PRIVATE VARIABLES
	/////////////////////////////////////////////////
	
	/**
    *  Holds phpmailer version.
    *  @type string
    */
	var $Version       = "phpmailer [version 1.07]";

	/**
    *  Holds all "To" addresses.
    *  @type array
    */
	var $to            = array();

	/**
    *  Holds all "CC" addresses.
    *  @type array
    */
	var $cc            = array();

	/**
    *  Holds all "BCC" addresses.
    *  @type array
    */
	var $bcc           = array();

	/**
    *  Holds all "Reply-To" addresses.
    *  @type array
    */
	var $ReplyTo       = array();

	/**
    *  Holds all attachments.
    *  @type array
    */
	var $attachment    = array();

	/**
    *  Holds all custom headers.
    *  @type array
    */
	var $CustomHeader  = array();	

	/**
    *  Holds the message boundary.
    *  @type string
    */
	var $boundary      = false;
	
	/////////////////////////////////////////////////
	// VARIABLE METHODS
	/////////////////////////////////////////////////

	/**
	 * IsHTML method sets message type to HTML.  Returns void.
	 * @public
	 * @returns void
	 */
	function IsHTML($bool) {
		if($bool == true)
			$this->ContentType = "text/html";
		else
			$this->ContentType = "text/plain";
	}

	/**
	 * IsSMTP method sets Mailer to use SMTP.  Returns void.
	 * @public
	 * @returns void
	 */
	function IsSMTP() {
		$this->Mailer = "smtp";
	}

	/**
	 * IsMail method sets Mailer to use PHP mail() function.  Returns void.
	 * @public
	 * @returns void
	 */
	function IsMail() {
		$this->Mailer = "mail";
	}

	/**
	 * IsSendmail method sets Mailer to use $Sendmail program.  Returns void.
	 * @public
	 * @returns void
	 */
	function IsSendmail() {
		$this->Mailer = "sendmail";
	}

	/**
	 * IsQmail method sets Mailer to use qmail MTA.  Returns void.
	 * @public
	 * @returns void
	 */
	function IsQmail() {
		//$this->Sendmail = "/var/qmail/bin/qmail-inject";
		$this->Sendmail = "/var/qmail/bin/sendmail";
		$this->Mailer = "sendmail";
	}


	/////////////////////////////////////////////////
	// RECIPIENT METHODS
	/////////////////////////////////////////////////	

	/**
	 * AddAddress method adds a "to" address.  Returns void.
	 * @public
	 * @returns void
	 */
	function AddAddress($address, $name = "") {
		$cur = count($this->to);
		$this->to[$cur][0] = trim($address);
		$this->to[$cur][1] = $name;
	}

	/**
	 * AddCC method adds a "cc" address.  Returns void.
	 * @public
	 * @returns void
	 */
	function AddCC($address, $name = "") {
		$cur = count($this->cc);
		$this->cc[$cur][0] = trim($address);
		$this->cc[$cur][1] = $name;
	}

	/**
	 * AddBCC method adds a "bcc" address.  Returns void.
	 * @public
	 * @returns void
	 */	
	function AddBCC($address, $name = "") {
		$cur = count($this->bcc);
		$this->bcc[$cur][0] = trim($address);
		$this->bcc[$cur][1] = $name;
	}

	/**
	 * AddReplyTo method adds a "Reply-to" address.  Returns void.
	 * @public
	 * @returns void
	 */	
	function AddReplyTo($address, $name = "") {
		$cur = count($this->ReplyTo);
		$this->ReplyTo[$cur][0] = trim($address);
		$this->ReplyTo[$cur][1] = $name;
	}


	/////////////////////////////////////////////////
	// MAIL SENDING METHODS
	/////////////////////////////////////////////////

	/**
	 * Send method creates message and assigns Mailer.  Returns bool.
	 * @public
	 * @returns bool
	 */
	function Send() {
		if(count($this->to) < 1)
		{
			$this->error_handler("You must provide at least one recipient email address");
			return false;
		}

		$header = $this->create_header();
		if(!$body = $this->create_body())
		   return false;
		
      // Choose the mailer
		if($this->Mailer == "sendmail")
		{
		   if(!$this->sendmail_send($header, $body))
		      return false;
		}
		elseif($this->Mailer == "mail")
		{
			if(!$this->mail_send($header, $body))
			   return false;
	   }
		elseif($this->Mailer == "smtp")
		{
			if(!$this->smtp_send($header, $body))
			   return false;
		}
		else
		{
			$this->error_handler(sprintf("%s mailer is not supported", $this->Mailer));
			return false;
		}
		
		return true;
	}

	/**
	 * sendmail_send method sends mail using the $Sendmail program.  Returns bool.
	 * @private
	 * @returns bool
	 */	
	function sendmail_send($header, $body) {
		$sendmail = sprintf("%s -t", $this->Sendmail);

		if(!@$mail = popen($sendmail, "w"))
		{
			$this->error_handler(sprintf("Could not execute %s", $this->Sendmail));
			return false;
		}
		
		fputs($mail, $header);
		fputs($mail, $body);
		pclose($mail);
		
		return true;
	}

	/**
	 * mail_send method sends mail using the PHP mail() function.  Returns bool.
	 * @private
	 * @returns bool
	 */
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
		{
			$this->error_handler("Could not instantiate mail()");
			return false;
		}
		
		return true;
	}

	/**
	 * smtp_send method sends mail via SMTP using PhpSMTP (Author:
	 * Chris Ryan).  Returns bool.
	 * @private
	 * @returns bool
	 */	
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
		{
			$this->error_handler("SMTP Error: could not connect to SMTP host server(s)");
			return false;
		}
	  	  
		$smtp->Hello($this->Helo);
		$smtp->Mail(sprintf("<%s>", $this->From));
		
		for($i = 0; $i < count($this->to); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->to[$i][0]));
		for($i = 0; $i < count($this->cc); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->cc[$i][0]));
		for($i = 0; $i < count($this->bcc); $i++)
			$smtp->Recipient(sprintf("<%s>", $this->bcc[$i][0]));

		if(!$smtp->Data(sprintf("%s%s", $header, $body)))
		{
		   $this->error_handler("SMTP Error: Data not accepted");
		   return false;
		}
		$smtp->Quit();
		
		return true;
	}
	

	/////////////////////////////////////////////////
	// MESSAGE CREATION METHODS
	/////////////////////////////////////////////////

	/**
	 * addr_append method creates recipient headers.  Returns string.
	 * @private
	 * @returns string
	 */	
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

	/**
	 * wordwrap wraps message for use with mailers that don't 
	 * automatically perform wrapping.  Written by philippe.  Returns string.
	 * @private
	 * @returns string
	 */
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

	/**
	 * create_header assembles message header.  Returns a string if sucessful 
	 * or false if unsucessful.
	 * @private
	 * @returns string
	 */
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

	/**
	 * create_body assembles the message body.  Returns a string if sucessful 
	 * or false if unsucessful.
	 * @private
	 * @returns string
	 */
	function create_body() {
		// wordwrap the message body if set
		if($this->WordWrap)
			$this->Body = $this->wordwrap($this->Body, $this->WordWrap);

		if(count($this->attachment) > 0)
		{
			if(!$body = $this->attach_all())
			   return false;
		}
		else
			$body = $this->Body;
		
		return($body);		
	}
	
	
	/////////////////////////////////////////////////
	// ATTACHMENT METHODS
	/////////////////////////////////////////////////

	/**
	 * AddAttachment check if attachment is valid and add to list.  Returns bool.
	 * @public
	 * @returns bool
	 */
	function AddAttachment($path, $name = "") {
		if(!@is_file($path))
		{
			$this->error_handler(sprintf("Could not find %s file on filesystem", $path));
			return false;
		}
		
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
		
		return true;
	}

	/**
	 * attach_all attach text and binary attachments to body.  Returns a 
	 * string if sucessful or false if unsucessful.
	 * @private
	 * @returns string
	 */
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
			if(!$mime[] = sprintf("%s\n\n", $this->encode_file($path)))
			   return false;
		}
		$mime[] = sprintf("\n--Boundary-=%s--\n", $this->boundary);
		
		return(join("", $mime));
	}

	/**
	 * encode_file encode attachment in base64 format.  Returns a 
	 * string if sucessful or false if unsucessful.
	 * @private
	 * @returns string
	 */
	function encode_file ($path) {
		if(!@$fd = fopen($path, "r"))
		{
			$this->error_handler("File Error: Could not open file %s", $path);
			return false;
		}
		$file = fread($fd, filesize($path));
		
		// chunk_split is found in PHP >= 3.0.6
		$encoded = chunk_split(base64_encode($file));
		fclose($fd);
		
		return($encoded);
	}
	
	/////////////////////////////////////////////////
	// MESSAGE RESET METHODS
	/////////////////////////////////////////////////

	/**
	 * ClearAddresses clears all recipients assigned in the TO array.  Returns void.
	 * @public
	 * @returns void
	 */
	function ClearAddresses() {
	   $this->to = array();
	}

	/**
	 * ClearCCs clears all recipients assigned in the CC array.  Returns void.
	 * @public
	 * @returns void
	 */	
	function ClearCCs() {
	   $this->cc = array();
	}
	
	/**
	 * ClearBCCs clears all recipients assigned in the BCC array.  Returns void.
	 * @public
	 * @returns void
	 */
	function ClearBCCs() {
	   $this->bcc = array();
	}

	/**
	 * ClearReplyTos clears all recipients assigned in the ReplyTo array.  Returns void.
	 * @public
	 * @returns void
	 */	
	function ClearReplyTos() {
	   $this->ReplyTo = array();
	}

	/**
	 * ClearAllRecipients clears all recipients assigned in the TO, CC and BCC 
	 * array.  Returns void.
	 * @public
	 * @returns void
	 */
	function ClearAllRecipients() {
	   $this->to = array();
	   $this->cc = array();
	   $this->bcc = array();
	}

	/**
	 * ClearAddresses clears all previously set attachments.  Returns void.
	 * @public
	 * @returns void
	 */
	function ClearAttachments() {
	   $this->attachment = array();
	}

	/**
	 * ClearCustomHeaders clears all custom headers.  Returns void.
	 * @public
	 * @returns void
	 */
	function ClearCustomHeaders() {
	   $this->CustomHeader = array();
	}
	
	
	/////////////////////////////////////////////////
	// MISCELLANEOUS METHODS
	/////////////////////////////////////////////////

	/**
	 * error_handler prints out structured errors.  Returns void.
	 * @private
	 * @returns void
	 */
	function error_handler($msg) {
		if($this->MailerDebug == true)
		{
			print("<h3>Mailer Error</h3>");
			print("Description:<br>");
			printf("<font color=\"FF0000\">%s</font>", $msg);
		}
	}

	/**
	 * AddCustomHeader adds a custom header.  Returns void.
	 * @public
	 * @returns void
	 */
	function AddCustomHeader($custom_header) {
	   $this->CustomHeader[] = $custom_header;
	}

	/**
	 * UseMSMailHeaders adds all the Microsoft message headers.  Returns void.
	 * @public
	 * @returns void
	 */
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

	/**
	 * PrintVersion prints out the version number of phpmailer.  Returns void.
	 * @public
	 * @returns void
	 */
	// Print out the version number of phpmailer
	function PrintVersion() {
	   //printf("<h5><a href=\"http://phpmailer.sourceforge.net\">%s</a></h5>", $this->Version);
	   printf("%s", $this->Version);
	}
}

// End of class
?>