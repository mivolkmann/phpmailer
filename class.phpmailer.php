<?php
////////////////////////////////////////////////////
// phpmailer - PHP email class
//
// Version 1.19, Created 06/22/2001
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
    * Sets the Sender email of the message. If not empty, will be sent via -f to sendmail
    * or as 'MAIL FROM' in smtp mode. Default value is "".
    * @public
    * @type string
    */
   var $Sender           = "";

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
    *  Sets the CharSet of the message. Default value is "localhost.localdomain".
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
   var $Version       = "phpmailer [version 1.19]";

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
      if ($this->Sender != "")
         $sendmail = sprintf("%s -f %s -t", $this->Sendmail, $this->Sender);
      else
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
      //$to = substr($this->addr_append("To", $this->to), 4, -2);

      // Cannot add Bcc's to the $to
      $to = $this->to[0][0]; // no extra comma
      for($i = 1; $i < count($this->to); $i++)
         $to .= sprintf(",%s", $this->to[$i][0]);

      if ($this->Sender != "")
      {
         // The fifth parameter to mail is only available in PHP >= 4.0.5
         $params = sprintf("-f %s", $this->Sender);
         $rt = @mail($to, $this->Subject, $body, $header, $params);
      }
      else
      {
         //echo $this->to[0][0];
         $rt = @mail($to, $this->Subject, $body, $header);
      }

      if(!$rt)
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
      if ($this->Sender == "")
         $smtp->Mail(sprintf("<%s>", $this->From));
      else
         $smtp->Mail(sprintf("<%s>", $this->Sender));

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
         $addr_str .= "\r\n";
      }
      else
         $addr_str .= "\r\n";

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
               $message .= $buf_o . "\r\n";
               $buf = $line_part[$e];
            }
         }
         $message .= $buf . "\r\n";
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
      $header[] = sprintf("Date: %s\r\n", date("r")); //D, j M Y H:i:s T"));

      // To created automatically by mail()
      if($this->Mailer != "mail")
         $header[] = $this->addr_append("To", $this->to);

      $header[] = sprintf("From: %s <%s>\r\n", $this->FromName, trim($this->From));
      if(count($this->cc) > 0)
         $header[] = $this->addr_append("Cc", $this->cc);

      // sendmail and mail() extract Bcc from the header before sending
      if((($this->Mailer == "sendmail") || ($this->Mailer == "mail")) && (count($this->bcc) > 0))
         $header[] = $this->addr_append("Bcc", $this->bcc);

      if(count($this->ReplyTo) > 0)
         $header[] = $this->addr_append("Reply-to", $this->ReplyTo);

      // mail() sets the subject itself
      if($this->Mailer != "mail")
         $header[] = sprintf("Subject: %s\r\n", trim($this->Subject));

      $header[] = sprintf("X-Priority: %d\r\n", $this->Priority);
      $header[] = sprintf("X-Mailer: %s\r\n", $this->Version);
      $header[] = sprintf("Return-Path: %s\r\n", trim($this->From));

      // Add custom headers
      for($index = 0; $index < count($this->CustomHeader); $index++)
         $header[] = sprintf("%s\r\n", $this->CustomHeader[$index]);

      if($this->UseMSMailHeaders)
         $header[] = $this->AddMSMailHeaders();

      // Add all attachments
      if(count($this->attachment) > 0)
      {
         // Set message boundary
         $this->boundary = "_b" . md5(uniqid(time()));

         $header[] = sprintf("Content-Type: Multipart/Mixed; charset = \"%s\";\r\n", $this->CharSet);
         $header[] = sprintf(" boundary=\"Boundary-=%s\"\r\n", $this->boundary);
      }
      else
      {
         $header[] = sprintf("Content-Transfer-Encoding: %s\r\n", $this->Encoding);
         $header[] = sprintf("Content-Type: %s; charset = \"%s\";\r\n", $this->ContentType, $this->CharSet);
      }

      $header[] = "MIME-Version: 1.0\r\n";

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

      $this->Body = $this->encode_string($this->Body, $this->Encoding);

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
    * AddAttachment check if attachment is valid and add to list.
    * Returns false if the file was not found.
    * @public
    * @returns bool
    */
   function AddAttachment($path, $name = "", $encoding = "base64", $type = "application/octet-stream") {
      if(!@is_file($path))
      {
         $this->error_handler(sprintf("Could not find %s file on filesystem", $path));
         return false;
      }

      $filename = basename($path);
      if($name == "")
         $name = $filename;

      // Append to $attachment array
      $cur = count($this->attachment);
      $this->attachment[$cur][0] = $path;
      $this->attachment[$cur][1] = $filename;
      $this->attachment[$cur][2] = $name;
      $this->attachment[$cur][3] = $encoding;
      $this->attachment[$cur][4] = $type;

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
      $mime[] = "This is a MIME message. If you are reading this text, you\r\n";
      $mime[] = "might want to consider changing to a mail reader that\r\n";
      $mime[] = "understands how to properly display MIME multipart messages.\r\n\r\n";
      $mime[] = sprintf("--Boundary-=%s\r\n", $this->boundary);
      $mime[] = sprintf("Content-Type: %s; charset = \"%s\";\r\n", $this->ContentType, $this->CharSet);
      $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n\r\n", $this->Encoding);
      $mime[] = sprintf("%s\r\n", $this->Body);

      // Add all attachments
      for($i = 0; $i < count($this->attachment); $i++)
      {
         $path = $this->attachment[$i][0];
         $filename = $this->attachment[$i][1];
         $name = $this->attachment[$i][2];
         $encoding = $this->attachment[$i][3];
         $type = $this->attachment[$i][4];
         $mime[] = sprintf("--Boundary-=%s\r\n", $this->boundary);
         $mime[] = sprintf("Content-Type: %s;\r\n", $type);
         $mime[] = sprintf("name=\"%s\"\r\n", $name);
         $mime[] = sprintf("Content-Transfer-Encoding: %s\r\n", $encoding);
         $mime[] = sprintf("Content-Disposition: attachment; filename=\"%s\"\r\n\r\n", $name);
         if(!$mime[] = sprintf("%s\r\n\r\n", $this->encode_file($path, $encoding)))
            return false;
      }
      $mime[] = sprintf("\r\n--Boundary-=%s--\r\n", $this->boundary);

      return(join("", $mime));
   }

   /**
    * encode_file encode attachment in requested format.  Returns a
    * string if sucessful or false if unsucessful.
    * @private
    * @returns string
    */
   function encode_file ($path, $encoding = "base64") {
      if(!@$fd = fopen($path, "r"))
      {
         $this->error_handler(sprintf("File Error: Could not open file %s", $path));
         return false;
      }
      $file = fread($fd, filesize($path));
      $encoded = $this->encode_string($file, $encoding);
      fclose($fd);

      return($encoded);
   }

   /**
    * encode_string encode string to requested format.  Returns a
    * string if sucessful or false if unsucessful.
    * @private
    * @returns string
    */
   function encode_string ($str, $encoding = "base64") {
      switch(strtolower($encoding)) {
         case "base64":
      // chunk_split is found in PHP >= 3.0.6
            $encoded = chunk_split(base64_encode($str));
            break;

         case "7bit":
         case "8bit":
         case "binary":
            $encoded = $str;
            break;

         case "quoted-printable":
            // Not yet available
         default:
            $this->error_handler(sprintf("Unknown encoding: %s", $encoding));
            return false;
      }
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
         printf("<font color=\"FF0000\">%s</font><br>", $msg);
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
    * AddMSMailHeaders adds all the Microsoft message headers.  Returns string.
    * @public
    * @returns string
    */
   function AddMSMailHeaders() {
      $MSHeader = "";
      if($this->Priority == 1)
         $MSPriority = "High";
      elseif($this->Priority == 5)
         $MSPriority = "Low";
      else
         $MSPriority = "Medium";

      $MSHeader .= sprintf("X-MSMail-Priority: %s\r\n", $MSPriority);
      $MSHeader .= sprintf("Importance: %s\r\n", $MSPriority);

      return($MSHeader);
   }

   /**
    * PrintVersion prints out the version number of phpmailer.  Returns void.
    * @public
    * @returns void
    */
   function PrintVersion() {
      //printf("<h5><a href=\"http://phpmailer.sourceforge.net\">%s</a></h5>", $this->Version);
      printf("%s", $this->Version);
   }

}
// End of class
?>