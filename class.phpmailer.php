<?php
////////////////////////////////////////////////////
// PHPMailer - PHP email class
//
// Class for sending email using either
// sendmail, PHP mail(), or SMTP.  Methods are
// based upon the standard AspEmail(tm) classes.
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

/**
 * PHPMailer - PHP email transport class
 * @author Brent R. Matzelle
 */
class PHPMailer
{
    /////////////////////////////////////////////////
    // PUBLIC VARIABLES
    /////////////////////////////////////////////////

    /**
     * Email priority (1 = High, 3 = Normal, 5 = low).
     * @var int
     */
    var $Priority          = 3;

    /**
     * Sets the CharSet of the message.
     * @var string
     */
    var $CharSet           = "iso-8859-1";

    /**
     * Sets the Content-type of the message.
     * @var string
     */
    var $ContentType        = "text/plain";

    /**
     * Sets the Encoding of the message. Options for this are "8bit",
     * "7bit", "binary", "base64", and "quoted-printable".
     * @var string
     */
    var $Encoding          = "8bit";

    /**
     * Holds the most recent mailer error message.
     * @var string
     */
    var $ErrorInfo         = "";

    /**
     * Sets the From email address for the message.
     * @var string
     */
    var $From               = "root@localhost";

    /**
     * Sets the From name of the message.
     * @var string
     */
    var $FromName           = "Root User";

    /**
     * Sets the Sender email of the message. If not empty, will be sent via -f to sendmail
     * or as 'MAIL FROM' in smtp mode.
     * @var string
     */
    var $Sender            = "";

    /**
     * Sets the Subject of the message.
     * @var string
     */
    var $Subject           = "";

    /**
     * Sets the Body of the message.  This can be either an HTML or text body.
     * If HTML then run IsHTML(true).
     * @var string
     */
    var $Body               = "";

    /**
     * Sets the text-only body of the message.  This automatically sets the
     * email to multipart/alternative.  This body can be read by mail
     * clients that do not have HTML email capability such as mutt. Clients
     * that can read HTML will view the normal Body.
     * @var string
     */
    var $AltBody           = "";

    /**
     * Sets word wrapping on the body of the message to a given number of 
     * characters.
     * @var int
     */
    var $WordWrap          = 0;

    /**
     * Method to send mail: ("mail", "sendmail", or "smtp").
     * @var string
     */
    var $Mailer            = "mail";

    /**
     * Sets the path of the sendmail program.
     * @var string
     */
    var $Sendmail          = "/usr/sbin/sendmail";
    
    /**
     * Path to PHPMailer plugins.  This is now only useful if the SMTP class 
     * is in a different directory than the PHP include path.  
     * @var string
     */
    var $PluginDir         = "";

    /**
     *  Holds PHPMailer version.
     *  @var string
     */
    var $Version           = "1.65";

    /**
     * Sets the email address that a reading confirmation will be sent.
     * @var string
     */
    var $ConfirmReadingTo  = "";

    /**
     *  Sets the line endings of the message.
     *  @var string
     */
    var $LE           = "\n";

    /**
     *  Sets the hostname to use in Message-Id and Received headers
     *  and as default HELO string. If empty, the value returned
     *  by SERVER_NAME is used or 'localhost.localdomain'.
     *  @var string
     */
    var $Hostname          = "";


    /////////////////////////////////////////////////
    // SMTP VARIABLES
    /////////////////////////////////////////////////

    /**
     *  Sets the SMTP hosts.  All hosts must be separated by a
     *  semicolon.  You can also specify a different port
     *  for each host by using this format: [hostname:port]
     *  (e.g. "smtp1.example.com:25;smtp2.example.com").
     *  Hosts will be tried in order.
     *  @var string
     */
    var $Host        = "localhost";

    /**
     *  Sets the default SMTP server port.
     *  @var int
     */
    var $Port        = 25;

    /**
     *  Sets the SMTP HELO of the message (Default is $Hostname).
     *  @var string
     */
    var $Helo        = "";

    /**
     *  Sets SMTP authentication. Utilizes the Username and Password variables.
     *  @var bool
     */
    var $SMTPAuth     = false;

    /**
     *  Sets SMTP username.
     *  @var string
     */
    var $Username     = "";

    /**
     *  Sets SMTP password.
     *  @var string
     */
    var $Password     = "";

    /**
     *  Sets the SMTP server timeout in seconds. This function will not 
     *  work with the win32 version.
     *  @var int
     */
    var $Timeout      = 10;

    /**
     *  Sets SMTP class debugging on or off.
     *  @var bool
     */
    var $SMTPDebug    = false;

    /**
     * Prevents the SMTP connection from being closed after each mail 
     * sending.  If this is set to true then to close the connection 
     * requires an explicit call to SmtpClose(). 
     * @var bool
     */
    var $SMTPKeepAlive = false;

    /////////////////////////////////////////////////
    // PRIVATE VARIABLES
    /////////////////////////////////////////////////

    /**
     * Holds the SMTP instance.
     * @access private
     * @var SMTP
     */
    var $smtp            = NULL;
    
    /**
     *  Holds all "To" addresses.
     *  @access private
     *  @var array
     */
    var $to              = array();

    /**
     *  Holds all "CC" addresses.
     *  @access private
     *  @var array
     */
    var $cc              = array();

    /**
     *  Holds all "BCC" addresses.
     *  @access private
     *  @var array
     */
    var $bcc             = array();

    /**
     *  Holds all "Reply-To" addresses.
     *  @var array
     */
    var $ReplyTo         = array();

    /**
     *  Holds all string and binary attachments.
     *  @access private
     *  @var array
     */
    var $attachment      = array();

    /**
     *  Holds all custom headers.
     *  @access private
     *  @var array
     */
    var $CustomHeader    = array();

    /**
     *  Holds the type of the message.
     *  @access private
     *  @var string
     */
    var $message_type    = "";

    /**
     *  Holds the message boundaries.
     *  @access private
     *  @var string array
     */
    var $boundary        = array();

    /////////////////////////////////////////////////
    // VARIABLE METHODS
    /////////////////////////////////////////////////

    /**
     * Sets message type to HTML.  Returns void.
     * @return void
     */
    function IsHTML($bool) {
        if($bool == true)
            $this->ContentType = "text/html";
        else
            $this->ContentType = "text/plain";
    }

    /**
     * Sets Mailer to send message using SMTP.
     * Returns void.
     * @return void
     */
    function IsSMTP() {
        $this->Mailer = "smtp";
    }

    /**
     * Sets Mailer to send message using PHP mail() function.
     * Returns void.
     * @return void
     */
    function IsMail() {
        $this->Mailer = "mail";
    }

    /**
     * Sets Mailer to send message using the $Sendmail program.
     * Returns void.
     * @return void
     */
    function IsSendmail() {
        $this->Mailer = "sendmail";
    }

    /**
     * Sets Mailer to send message using the qmail MTA.  Returns void.
     * @return void
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
     * Adds a "To" address.  Returns void.
     * @return void
     */
    function AddAddress($address, $name = "") {
        $cur = count($this->to);
        $this->to[$cur][0] = trim($address);
        $this->to[$cur][1] = $name;
    }

    /**
     * Adds a "Cc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  This is a PHP bug that has been submitted
     * on http://bugs.php.net. The *NIX version of PHP
     * functions correctly. Returns void.
     * @return void
    */
    function AddCC($address, $name = "") {
        $cur = count($this->cc);
        $this->cc[$cur][0] = trim($address);
        $this->cc[$cur][1] = $name;
    }

    /**
     * Adds a "Bcc" address. Note: this function works
     * with the SMTP mailer on win32, not with the "mail"
     * mailer.  This is a PHP bug that has been submitted
     * on http://bugs.php.net. The *NIX version of PHP
     * functions correctly.
     * Returns void.
     * @return void
     */
    function AddBCC($address, $name = "") {
        $cur = count($this->bcc);
        $this->bcc[$cur][0] = trim($address);
        $this->bcc[$cur][1] = $name;
    }

    /**
     * Adds a "Reply-to" address.  Returns void.
     * @return void
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
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false.  Use the ErrorInfo
     * variable to view description of the error.  Returns bool.
     * @return bool
     */
    function Send() {
        $header = "";
        $body = "";

        if((count($this->to) + count($this->cc) + count($this->bcc)) < 1)
        {
            $this->error_handler("You must provide at least one recipient email address");
            return false;
        }

        // Set whether the message is multipart/alternative
        if(!empty($this->AltBody))
            $this->ContentType = "multipart/alternative";

        // Attach sender information & date
        $header = $this->received();
        $header .= sprintf("Date: %s%s", $this->rfc_date(), $this->LE);
        $header .= $this->CreateHeader();

        if(!$body = $this->CreateBody())
            return false;

        // Choose the mailer
        if($this->Mailer == "sendmail")
        {
          if(!$this->SendmailSend($header, $body))
              return false;
        }
        elseif($this->Mailer == "mail")
        {
          if(!$this->MailSend($header, $body))
              return false;
        }
        elseif($this->Mailer == "smtp")
        {
          if(!$this->SmtpSend($header, $body))
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
     * Sends mail using the $Sendmail program.  Returns bool.
     * @access private
     * @return bool
     */
    function SendmailSend($header, $body) {
        if ($this->Sender != "")
            $sendmail = sprintf("%s -oi -f %s -t", $this->Sendmail, $this->Sender);
        else
            $sendmail = sprintf("%s -oi -t", $this->Sendmail);

        if(!@$mail = popen($sendmail, "w"))
        {
            $this->error_handler(sprintf("Could not execute %s", $this->Sendmail));
            return false;
        }

        fputs($mail, $header);
        fputs($mail, $body);
        
        $result = pclose($mail) >> 8 & 0xFF;
        if($result != 0)
        {
            $this->error_handler(sprintf("Could not execute %s", $this->Sendmail));
            return false;
        }

        return true;
    }

    /**
     * Sends mail using the PHP mail() function.  Returns bool.
     * @access private
     * @return bool
     */
    function MailSend($header, $body) {
        // Cannot add Bcc's to the $to
        $to = $this->to[0][0]; // no extra comma
        for($i = 1; $i < count($this->to); $i++)
            $to .= sprintf(",%s", $this->to[$i][0]);

        if ($this->Sender != "" && PHP_VERSION >= "4.0")
        {
            $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $this->Sender);
        }

        if ($this->Sender != "" && PHP_VERSION >= "4.0.5")
        {
            $params = sprintf("-oi -f %s", $this->Sender);
            $rt = @mail($to, $this->encode_header($this->Subject), $body, $header, $params);
        }
        else
            $rt = @mail($to, $this->encode_header($this->Subject), $body, $header);

        if (isset($old_from))
            ini_set("sendmail_from", $old_from);

        if(!$rt)
        {
            $this->error_handler("Could not instantiate mail()");
            return false;
        }

        return true;
    }

    /**
     * Sends mail via SMTP using PhpSMTP (Author:
     * Chris Ryan).  Returns bool.  Returns false if there is a
     * bad MAIL FROM, RCPT, or DATA input.
     * @access private
     * @return bool
     */
    function SmtpSend($header, $body) {
        // Include SMTP class code, but not twice
        include_once($this->PluginDir . "class.smtp.php");
        $error = "";
        $bad_rcpt = array();

        if($this->smtp == NULL)
        {
            if(!$this->SmtpConnect())
                return false;
        }

        // Must perform HELO before authentication
        if ($this->Helo != '')
            $this->smtp->Hello($this->Helo);
        else
            $this->smtp->Hello($this->ServerHostname());

        // If user requests SMTP authentication
        if($this->SMTPAuth)
        {
            if(!$this->smtp->Authenticate($this->Username, $this->Password))
            {
                $this->error_handler("SMTP Error: Could not authenticate");
                return false;
            }
        }

        $smtp_from = ($this->Sender == "") ? $this->From : $this->Sender;
        if(!$this->smtp->Mail(sprintf("<%s>", $smtp_from)))
        {
            $error = sprintf("SMTP Error: From address [%s] failed", $smtp_from);
            $this->error_handler($error);
            $this->smtp->Reset();
            return false;
        }

        // Attempt to send attach all recipients
        for($i = 0; $i < count($this->to); $i++)
        {
            if(!$this->smtp->Recipient(sprintf("<%s>", $this->to[$i][0])))
                $bad_rcpt[] = $this->to[$i][0];
        }
        for($i = 0; $i < count($this->cc); $i++)
        {
            if(!$this->smtp->Recipient(sprintf("<%s>", $this->cc[$i][0])))
                $bad_rcpt[] = $this->cc[$i][0];
        }
        for($i = 0; $i < count($this->bcc); $i++)
        {
            if(!$this->smtp->Recipient(sprintf("<%s>", $this->bcc[$i][0])))
                $bad_rcpt[] = $this->bcc[$i][0];
        }

        // Create error message
        if(count($bad_rcpt) > 0)
        {
            for($i = 0; $i < count($bad_rcpt); $i++)
            {
                if($i != 0) { $error .= ", "; }
                $error .= $bad_rcpt[$i];
            }
            $error = "SMTP Error: The following recipients failed [".$error."]";
            $this->error_handler($error);
            $this->smtp->Reset();
            return false;
        }

        if(!$this->smtp->Data(sprintf("%s%s", $header, $body)))
        {
            $this->error_handler("SMTP Error: Data not accepted");
            $this->smtp->Reset();
            return false;
        }
        if($this->SMTPKeepAlive == true)
            $this->smtp->Reset();
        else
            $this->SmtpClose();

        return true;
    }

    /**
     * Initiates a connection to an SMTP server.  Returns false if the 
     * operation failed.
     * @access private
     * @return bool
     */
    function SmtpConnect() {
        if($this->smtp == NULL) { $this->smtp = new SMTP(); }

        $this->smtp->do_debug = $this->SMTPDebug;
        $hosts = explode(";", $this->Host);
        $index = 0;
        $connection = ($this->smtp->Connected()) ? true : false; 

        // Retry while there is no connection
        while($index < count($hosts) && $connection == false)
        {
            if(strstr($hosts[$index], ":"))
                list($host, $port) = explode(":", $hosts[$index]);
            else
            {
                $host = $hosts[$index];
                $port = $this->Port;
            }

            if($this->smtp->Connect($host, $port, $this->Timeout))
                $connection = true;
            $index++;
        }
        if(!$connection)
            $this->error_handler("SMTP Error: could not connect to SMTP host");

        return $connection;
    }

    /**
     * Closes the active SMTP session if one exists.
     * @return void
     */
    function SmtpClose() {
        if($this->smtp != NULL)
        {
            if($this->smtp->Connected())
            {
                $this->smtp->Quit();
                $this->smtp->Close();
            }
        }
    }
    
    /////////////////////////////////////////////////
    // MESSAGE CREATION METHODS
    /////////////////////////////////////////////////

    /**
     * Creates recipient headers.  Returns string.
     * @access private
     * @return string
     */
    function addr_append($type, $addr) {
        $addr_str = $type . ": ";
        $addr_str .= $this->addr_format($addr[0]);
        if(count($addr) > 1)
        {
            for($i = 1; $i < count($addr); $i++)
                $addr_str .= sprintf(", %s", $this->addr_format($addr[$i]));
            $addr_str .= $this->LE;
        }
        else
            $addr_str .= $this->LE;

        return $addr_str;
    }
    
    /**
     * Creates a semicolon delimited list for use in pqm files.
     * @access private
     * @return string
     */
    function addr_list($list_array) {
        $addr_list = "";
        for($i = 0; $i < count($list_array); $i++)
        {
            if($i > 0) { $addr_list .= ";"; }
            $addr_list .= $list_array[$i][0];
        }
        
        return $addr_list;
    }
    
    /**
     * Formats an address correctly. 
     * @access private
     * @return string
     */
    function addr_format($addr) {
        if(empty($addr[1]))
            $formatted = $addr[0];
        else
            $formatted = sprintf('%s <%s>', $this->encode_header($addr[1], 'phrase'), $addr[0]);

        return $formatted;
    }

    /**
     * Wraps message for use with mailers that do not
     * automatically perform wrapping and for quoted-printable.
     * Original written by philippe.  Returns string.
     * @access private
     * @return string
     */
    function word_wrap($message, $length, $qp_mode = false) {
        $soft_break = ($qp_mode) ? sprintf(" =%s", $this->LE) : $this->LE;

        $message = $this->fix_eol($message);
        if (substr($message, -1) == $this->LE)
            $message = substr($message, 0, -1);

        $line = explode($this->LE, $message);
        $message = "";
        for ($i=0 ;$i < count($line); $i++)
        {
          $line_part = explode(" ", $line[$i]);
          $buf = "";
          for ($e = 0; $e<count($line_part); $e++)
          {
              $word = $line_part[$e];
              if ($qp_mode and (strlen($word) > $length))
              {
                $space_left = $length - strlen($buf) - 1;
                if ($e != 0)
                {
                    if ($space_left > 20)
                    {
                        $len = $space_left;
                        if (substr($word, $len - 1, 1) == "=")
                          $len--;
                        elseif (substr($word, $len - 2, 1) == "=")
                          $len -= 2;
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);
                        $buf .= " " . $part;
                        $message .= $buf . sprintf("=%s", $this->LE);
                    }
                    else
                    {
                        $message .= $buf . $soft_break;
                    }
                    $buf = "";
                }
                while (strlen($word) > 0)
                {
                    $len = $length;
                    if (substr($word, $len - 1, 1) == "=")
                        $len--;
                    elseif (substr($word, $len - 2, 1) == "=")
                        $len -= 2;
                    $part = substr($word, 0, $len);
                    $word = substr($word, $len);

                    if (strlen($word) > 0)
                        $message .= $part . sprintf("=%s", $this->LE);
                    else
                        $buf = $part;
                }
              }
              else
              {
                $buf_o = $buf;
                $buf .= ($e == 0) ? $word : (" " . $word); 

                if (strlen($buf) > $length and $buf_o != "")
                {
                    $message .= $buf_o . $soft_break;
                    $buf = $word;
                }
              }
          }
          $message .= $buf . $this->LE;
        }

        return $message;
    }
    
    /**
     * Set the body wrapping.
     * @access private
     * @return void
     */
    function SetWordWrap() {
        if($this->WordWrap < 1)
            return;
            
        switch($this->message_type)
        {
           case "alt":
           case "alt_attachment":
              $this->AltBody = $this->word_wrap($this->AltBody, $this->WordWrap);
              break;
           default:
              $this->Body = $this->word_wrap($this->Body, $this->WordWrap);
              break;
        }
    }

    /**
     * Assembles message header.  Returns a string if successful
     * or false if unsuccessful.
     * @access private
     * @return string
     */
    function CreateHeader() {
        $header = array();
        
        // Set the boundaries
        $uniq_id = md5(uniqid(time()));
        $this->boundary[1] = "b1_" . $uniq_id;
        $this->boundary[2] = "b2_" . $uniq_id;

        if($this->Sender == "")
            $header[] = sprintf("Return-Path: %s%s", trim($this->From), $this->LE);
        else
            $header[] = sprintf("Return-Path: %s%s", trim($this->Sender), $this->LE);
        
        // To be created automatically by mail()
        if($this->Mailer != "mail")
        {
            if(count($this->to) > 0)
                $header[] = $this->addr_append("To", $this->to);
            else if (count($this->cc) == 0)
                $header[] = "To: undisclosed-recipients:;".$this->LE;
        }

        $from = array();
        $from[0][0] = trim($this->From);
        $from[0][1] = $this->FromName;
        $header[] = $this->addr_append("From", $from); 

        if(count($this->cc) > 0)
            $header[] = $this->addr_append("Cc", $this->cc);

        // sendmail and mail() extract Bcc from the header before sending
        if((($this->Mailer == "sendmail") || ($this->Mailer == "mail")) && (count($this->bcc) > 0))
            $header[] = $this->addr_append("Bcc", $this->bcc);

        if(count($this->ReplyTo) > 0)
            $header[] = $this->addr_append("Reply-to", $this->ReplyTo);

        // mail() sets the subject itself
        if($this->Mailer != "mail")
            $header[] = sprintf("Subject: %s%s", $this->encode_header(trim($this->Subject)), $this->LE);

        $header[] = sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->ServerHostname(), $this->LE);
        $header[] = sprintf("X-Priority: %d%s", $this->Priority, $this->LE);
        $header[] = sprintf("X-Mailer: PHPMailer [version %s]%s", $this->Version, $this->LE);
        
        if($this->ConfirmReadingTo != "")
        {
            $header[] = sprintf("Disposition-Notification-To: <%s>%s", 
                            trim($this->ConfirmReadingTo), $this->LE);
        }

        // Add custom headers
        for($index = 0; $index < count($this->CustomHeader); $index++)
        {
            $header[] = sprintf("%s: %s%s", trim($this->CustomHeader[$index][0]), 
                        $this->encode_header(trim($this->CustomHeader[$index][1])), 
                        $this->LE);
        }

        $header[] = sprintf("MIME-Version: 1.0%s", $this->LE);

        // Determine what type of message this is        
        if(count($this->attachment) < 1 && strlen($this->AltBody) < 1)
            $this->message_type = "plain";
        else
        {
            if(count($this->attachment) > 0)
                $this->message_type = "attachments";
            if(strlen($this->AltBody) > 0 && count($this->attachment) < 1)
                $this->message_type = "alt";
            if(strlen($this->AltBody) > 0 && count($this->attachment) > 0)
                $this->message_type = "alt_attachments";
        }
        
        switch($this->message_type)
        {
            case "plain":
                $header[] = sprintf("Content-Transfer-Encoding: %s%s", 
                                    $this->Encoding, $this->LE);
                $header[] = sprintf("Content-Type: %s; charset=\"%s\"",
                                    $this->ContentType, $this->CharSet);
                break;
            case "attachments":
            case "alt_attachments":
                if($this->EmbeddedImageCount() > 0)
                {
                    $header[] = sprintf("Content-Type: %s;%s\ttype=\"text/html\";%s\tboundary=\"%s\"%s", 
                                    "multipart/related", $this->LE, $this->LE, 
                                    $this->boundary[1], $this->LE);
                }
                else
                {
                    $header[] = sprintf("Content-Type: %s;%s",
                                    "multipart/mixed", $this->LE);
                    $header[] = sprintf("\tboundary=\"%s\"%s", $this->boundary[1], $this->LE);
                }
                break;
            case "alt":
                $header[] = sprintf("Content-Type: %s;%s",
                                    "multipart/alternative", $this->LE);
                $header[] = sprintf("\tboundary=\"%s\"%s", $this->boundary[1], $this->LE);
                break;
        }

        // No additional lines when using mail() function
        if($this->Mailer != "mail")
            $header[] = $this->LE.$this->LE;

        return join("", $header);
    }

    /**
     * Assembles the message body.  Returns a string if successful
     * or false if unsuccessful.
     * @access private
     * @return string
     */
    function CreateBody() {
        $body = array();

        $this->SetWordWrap();

        switch($this->message_type)
        {
            case "alt":
                $body[] = $this->GetBoundary($this->boundary[1], "", 
                                             "text/plain", "");
                $body[] = $this->encode_string($this->AltBody, $this->Encoding);
                $body[] = $this->LE.$this->LE;
                $body[] = $this->GetBoundary($this->boundary[1], "", 
                                             "text/html", "");
                
                $body[] = $this->encode_string($this->Body, $this->Encoding);
                $body[] = $this->LE.$this->LE;
    
                $body[] = $this->EndBoundary($this->boundary[1]);
                break;
            case "plain":
                $body[] = $this->encode_string($this->Body, $this->Encoding);
                break;
            case "attachments":
                $body[] = $this->GetBoundary($this->boundary[1], "", "", "");
                $body[] = $this->encode_string($this->Body, $this->Encoding);
                $body[] = $this->LE;
     
                if(!$body[] = $this->AttachAll())
                    return false;
                break;
            case "alt_attachments":
                $body[] = sprintf("--%s%s", $this->boundary[1], $this->LE);
                $body[] = sprintf("Content-Type: %s;%s" .
                                  "\tboundary=\"%s\"%s",
                                   "multipart/alternative", $this->LE, 
                                   $this->boundary[2], $this->LE.$this->LE);
    
                // Create text body
                $body[] = $this->GetBoundary($this->boundary[2], "", 
                                             "text/plain", "") . $this->LE;

                $body[] = $this->encode_string($this->AltBody, $this->Encoding);
                $body[] = $this->LE.$this->LE;
    
                // Create the HTML body
                $body[] = $this->GetBoundary($this->boundary[2], "", 
                                             "text/html", "") . $this->LE;
    
                $body[] = $this->encode_string($this->Body, $this->Encoding);
                $body[] = $this->LE.$this->LE;

                $body[] = $this->EndBoundary($this->boundary[2]);
                
                if(!$body[] = $this->AttachAll())
                    return false;
                break;
        }

        return join("", $body);
    }

    /**
     * Returns the start of a message boundary.
     * @access private
     */
    function GetBoundary($boundary, $charSet, $contentType, $encoding) {
        $result = array();
        if($charSet == "") { $charSet = $this->CharSet; }
        if($contentType == "") { $contentType = $this->ContentType; }
        if($encoding == "") { $encoding = $this->Encoding; }

        $result[] = "--" . $boundary;
        $result[] = sprintf("Content-Type: %s; charset = \"%s\"", 
                            $contentType, $charSet);
        $result[] = "Content-Transfer-Encoding: " . $encoding;
        $result[] = $this->LE;
       
        return join($this->LE, $result);
    }
    
    /**
     * Returns the end of a message boundary.
     * @access private
     */
    function EndBoundary($boundary) {
        return $this->LE . "--" . $boundary . "--" . $this->LE; 
    }

    /////////////////////////////////////////////////
    // ATTACHMENT METHODS
    /////////////////////////////////////////////////

    /**
     * Adds an attachment from a path on the filesystem.
     * Checks if attachment is valid and then adds
     * the attachment to the list.
     * Returns false if the file could not be found
     * or accessed.
     * @return bool
     */
    function AddAttachment($path, $name = "", $encoding = "base64", 
                           $type = "application/octet-stream") {
        if(!@is_file($path))
        {
            $this->error_handler(sprintf("Could not access [%s] file", $path));
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
        $this->attachment[$cur][5] = false; // isStringAttachment
        $this->attachment[$cur][6] = "attachment";
        $this->attachment[$cur][7] = 0;

        return true;
    }

    /**
     * Attaches all fs, string, and binary attachments to the message.
     * Returns a string if successful or false if unsuccessful.
     * @access private
     * @return string
     */
    function AttachAll() {
        // Return text of body
        $mime = array();

        // Add all attachments
        for($i = 0; $i < count($this->attachment); $i++)
        {
            // Check for string attachment
            $bString = $this->attachment[$i][5];
            if ($bString)
                $string = $this->attachment[$i][0];
            else
                $path = $this->attachment[$i][0];

            $filename    = $this->attachment[$i][1];
            $name        = $this->attachment[$i][2];
            $encoding    = $this->attachment[$i][3];
            $type        = $this->attachment[$i][4];
            $disposition = $this->attachment[$i][6];
            $cid         = $this->attachment[$i][7];
            
            $mime[] = sprintf("--%s%s", $this->boundary[1], $this->LE);
            $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $name, $this->LE);
            $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->LE);

            if($disposition == "inline")
                $mime[] = sprintf("Content-ID: <%s>%s", $cid, $this->LE);

            $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", 
                              $disposition, $name, $this->LE.$this->LE);

            // Encode as string attachment
            if($bString)
            {
                if(!$mime[] = $this->encode_string($string, $encoding))                
                    return false;
                $mime[] = $this->LE.$this->LE;
            }
            else
            {
                if(!$mime[] = $this->encode_file($path, $encoding))                
                    return false;
                $mime[] = $this->LE.$this->LE;
            }
        }

        $mime[] = sprintf("--%s--%s", $this->boundary[1], $this->LE);

        return join("", $mime);
    }
    
    /**
     * Encodes attachment in requested format.  Returns a
     * string if successful or false if unsuccessful.
     * @access private
     * @return string
     */
    function encode_file ($path, $encoding = "base64") {
        if(!@$fd = fopen($path, "rb"))
        {
            $this->error_handler(sprintf("File Error: Could not open file %s", $path));
            return false;
        }
        $file_buffer = fread($fd, filesize($path));
        $file_buffer = $this->encode_string($file_buffer, $encoding);
        fclose($fd);

        return $file_buffer;
    }

    /**
     * Encodes string to requested format. Returns a
     * string if successful or false if unsuccessful.
     * @access private
     * @return string
     */
    function encode_string ($str, $encoding = "base64") {
        switch(strtolower($encoding)) {
          case "base64":
              // chunk_split is found in PHP >= 3.0.6
              $encoded = chunk_split(base64_encode($str), 76, $this->LE);
              break;

          case "7bit":
          case "8bit":
              $encoded = $this->fix_eol($str);
              if (substr($encoded, -(strlen($this->LE))) != $this->LE)
                $encoded .= $this->LE;
              break;

          case "binary":
              $encoded = $str;
              break;

          case "quoted-printable":
              $encoded = $this->encode_qp($str);
              break;

          default:
              $this->error_handler(sprintf("Unknown encoding: %s", $encoding));
              return false;
        }
        return $encoded;
    }

    /**
     * Encode a header string to best of Q, B, quoted or none.  Returns a string.
     * @access private
     * @return string
     */
    function encode_header ($str, $position = 'text') {
      $x = 0;
      
      switch (strtolower($position)) {
        case 'phrase':
          if (preg_match_all('/[\200-\377]/', $str, $matches) == 0) {
            // Can't use addslashes as we don't know what value has magic_quotes_sybase.
            $encoded = addcslashes($str, '\000-\037\177');
            $encoded = preg_replace('/([\"])/', '\\"', $encoded);

            if (($str == $encoded) && (preg_match_all('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str, $matches) == 0))
              return ($encoded);
            else
              return ("\"$encoded\"");
          }
          $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
          break;
        case 'comment':
          $x = preg_match_all('/[()"]/', $str, $matches);
          // Fall-through
        case 'text':
        default:
          $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
          break;
      }

      if ($x == 0)
        return ($str);

      $maxlen = 75 - 7 - strlen($this->CharSet);
      // Try to select the encoding which should produce the shortest output
      if (strlen($str)/3 < $x) {
        $encoding = 'B';
        $encoded = base64_encode($str);
        $maxlen -= $maxlen % 4;
        $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
      } else {
        $encoding = 'Q';
        $encoded = $this->encode_q($str, $position);
        $encoded = $this->word_wrap($encoded, $maxlen, true);
        $encoded = str_replace("=".$this->LE, "\n", trim($encoded));
      }

      $encoded = preg_replace('/^(.*)$/m', " =?".$this->CharSet."?$encoding?\\1?=", $encoded);
      $encoded = trim(str_replace("\n", $this->LE, $encoded));
      
      return $encoded;
    }
    
    /**
     * Encode string to quoted-printable.  Returns a string.
     * @access private
     * @return string
     */
    function encode_qp ($str) {
        $encoded = $this->fix_eol($str);
        if (substr($encoded, -(strlen($this->LE))) != $this->LE)
            $encoded .= $this->LE;

        // Replace every high ascii, control and = characters
        $encoded = preg_replace('/([\000-\010\013\014\016-\037\075\177-\377])/e',
                  "'='.sprintf('%02X', ord('\\1'))", $encoded);
        // Replace every spaces and tabs when it's the last character on a line
        $encoded = preg_replace("/([\011\040])".$this->LE."/e",
                  "'='.sprintf('%02X', ord('\\1')).'".$this->LE."'", $encoded);

        // Maximum line length of 76 characters before CRLF (74 + space + '=')
        $encoded = $this->word_wrap($encoded, 74, true);

        return $encoded;
    }

    /**
     * Encode string to q encoding.  Returns a string.
     * @access private
     * @return string
     */
    function encode_q ($str, $position = "text") {
        // There should not be any EOL in the string
        $encoded = preg_replace("[\r\n]", "", $str);

        switch (strtolower($position)) {
          case "phrase":
            $encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
          case "comment":
            $encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
          case "text":
          default:
            // Replace every high ascii, control =, ? and _ characters
            $encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',
                  "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
        }
        
        // Replace every spaces to _ (more readable than =20)
        $encoded = str_replace(" ", "_", $encoded);

        return $encoded;
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     * @return void
     */
    function AddStringAttachment($string, $filename, $encoding = "base64", 
                                 $type = "application/octet-stream") {
        // Append to $attachment array
        $cur = count($this->attachment);
        $this->attachment[$cur][0] = $string;
        $this->attachment[$cur][1] = $filename;
        $this->attachment[$cur][2] = $filename;
        $this->attachment[$cur][3] = $encoding;
        $this->attachment[$cur][4] = $type;
        $this->attachment[$cur][5] = true; // isString
        $this->attachment[$cur][6] = "attachment";
        $this->attachment[$cur][7] = 0;
    }
    
    /**
     * Adds an embedded attachment.  This can include images, sounds, and 
     * just about any other document.  
     * @param cid this is the Content Id of the attachment.  Use this to identify
     *        the Id for accessing the image in an HTML form.
     * @return bool
     */
    function AddEmbeddedImage($path, $cid, $name = "", $encoding = "base64", 
                              $type = "application/octet-stream") {
    
        if(!@is_file($path))
        {
            $this->error_handler(sprintf("Could not access [%s] file", $path));
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
        $this->attachment[$cur][5] = false; // isStringAttachment
        $this->attachment[$cur][6] = "inline";
        $this->attachment[$cur][7] = $cid;
    
        return true;
    }
    
    /**
     * Returns the number of embedded images in an email.
     * @access private
     * @return int
     */
    function EmbeddedImageCount() {
        $result = 0;
        for($i = 0; $i < count($this->attachment); $i++)
        {
            if($this->attachment[$i][6] == "inline")
                $result++;
        }
        
        return $result;
    }

    /////////////////////////////////////////////////
    // MESSAGE RESET METHODS
    /////////////////////////////////////////////////

    /**
     * Clears all recipients assigned in the TO array.  Returns void.
     * @return void
     */
    function ClearAddresses() {
        $this->to = array();
    }

    /**
     * Clears all recipients assigned in the CC array.  Returns void.
     * @return void
     */
    function ClearCCs() {
        $this->cc = array();
    }

    /**
     * Clears all recipients assigned in the BCC array.  Returns void.
     * @return void
     */
    function ClearBCCs() {
        $this->bcc = array();
    }

    /**
     * Clears all recipients assigned in the ReplyTo array.  Returns void.
     * @return void
     */
    function ClearReplyTos() {
        $this->ReplyTo = array();
    }

    /**
     * Clears all recipients assigned in the TO, CC and BCC
     * array.  Returns void.
     * @return void
     */
    function ClearAllRecipients() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
    }

    /**
     * Clears all previously set filesystem, string, and binary
     * attachments.  Returns void.
     * @return void
     */
    function ClearAttachments() {
        $this->attachment = array();
    }

    /**
     * Clears all custom headers.  Returns void.
     * @return void
     */
    function ClearCustomHeaders() {
        $this->CustomHeader = array();
    }


    /////////////////////////////////////////////////
    // MISCELLANEOUS METHODS
    /////////////////////////////////////////////////

    /**
     * Adds the error message to the error container.
     * Returns void.
     * @access private
     * @return void
     */
    function error_handler($msg) {
        $this->ErrorInfo = $msg;
    }

    /**
     * Returns the proper RFC 822 formatted date. Returns string.
     * @access private
     * @return string
     */
    function rfc_date() {
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz/3600)*100 + ($tz%3600)/60;
        $result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

        return $result;
    }

    /**
     * Returns received header for message tracing. Returns string.
     * @access private
     * @return string
     */
    function received() {
        if ($this->get_server_var('SERVER_NAME') != '')
        {
            $protocol = ($this->get_server_var('HTTPS') == 'on') ? 'HTTPS' : 'HTTP';
            $remote = $this->get_server_var('REMOTE_HOST');
            if($remote == "")
                $remote = 'phpmailer';
            $remote .= ' (['.$this->get_server_var('REMOTE_ADDR').'])';
        }
        else
        {
            $protocol = 'local';
            $remote = $this->get_server_var('USER');
            if($remote == '')
                $remote = 'phpmailer';
        }

        $result = sprintf("Received: from %s %s\tby %s " .
                          "with %s (PHPMailer);%s\t%s%s", $remote, $this->LE,
                          $this->ServerHostname(), $protocol, $this->LE,
                          $this->rfc_date(), $this->LE);

        return $result;
    }
    
    /**
     * Returns the appropriate server variable.  Should work with both 
     * PHP 4.1.0+ as well as older versions.  Returns an empty string 
     * if nothing is found.
     * @access private
     * @return mixed
     */
    function get_server_var($varName) {
        global $HTTP_SERVER_VARS;
        global $HTTP_ENV_VARS;

        if(!isset($_SERVER))
        {
            $_SERVER = $HTTP_SERVER_VARS;
            if(!isset($_SERVER["REMOTE_ADDR"]))
                $_SERVER = $HTTP_ENV_VARS; // must be Apache
        }
        
        if(isset($_SERVER[$varName]))
            return $_SERVER[$varName];
        else
            return "";
    }

    /**
     * Returns the server hostname or 'localhost.localdomain' if unknown.
     * @access private
     * @return string
     */
    function ServerHostname() {
        if ($this->Hostname != "")
            $result = $this->Hostname;
        elseif ($this->get_server_var('SERVER_NAME') != "")
            $result = $this->get_server_var('SERVER_NAME');
        else
            $result = "localhost.localdomain";
            
        return $result;
    }

    /**
     * Changes every end of line from CR or LF to CRLF.  Returns string.
     * @access private
     * @return string
     */
    function fix_eol($str) {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $str = str_replace("\n", $this->LE, $str);
        return $str;
    }

    /**
     * Adds a custom header.  Returns void.
     * @return void
     */
    function AddCustomHeader($custom_header) {
        $this->CustomHeader[] = explode(":", $custom_header, 2);
    }
}

?>
