<?php

class Mailer
{
   private
   $message_type, $sign_cert_file, $sign_key_file, $sign_key_pass, $callback, $messageId,
   $helo, $from, $fromName, $host, $altBody, $singleTo, $subject, $confirmReadingTo,
   $sender, $body, $sendmail, $mailer , $contentType, $errorInfo,
   $SMTPPort, $SMTPDebugging, $SMTPHost, $SMTPSecure, $SMTPUsername, $SMTPPassword,
   $SMTPTimeout = 10,
   $IMAPPassword, $IMAPUsername, $IMAPHost, $IMAPBox, $IMAPPort, $IMAPSecure, $IMAPFlags,
   $IMAPCopy = false,
   $priority = 3,
   $charSet = 'utf8',
   $encoding = '8bit',
   $wordWrap = 0,
   $singleToArray = array(),
   $LE = "\n",
   $mailerDKIM = null,
   $smtp = null,
   $to = array(),
   $cc = array(),
   $bcc = array(),
   $replyTo = array(),
   $all_recipients = array(),
   $attachment = array(),
   $customHeader = array(),
   $boundary = array(),
   $error_count = 0,
   $exceptions = false;

   const
   STOP_MESSAGE  = 0,
   STOP_CONTINUE = 1,
   STOP_CRITICAL = 2;

   protected function __construct($from, $fromName, $hostname, $singleTo = false, $mailSendTyp = '', $isHTML = true)
   {
      $this->hostname = $hostname;
      $this->singleTo = $singleTo;

      $this->setFrom($from, $fromName);

      switch($mailSendTyp)
      {
         case 'smtp':
            $this->mailer = 'smtp';
            break;

         case 'sendmail':
            $this->sendmail = '/usr/sbin/sendmail';
            $this->mailer = 'sendmail';
            break;

         default:
            $this->mailer = 'mail';
      }

      if($isHTML)
      {
         $this->contentType = 'text/html';
      }
      else
      {
         $this->contentType = 'text/plain';
      }
   }

   public function setSMTPData($user, $pass, $host, $port = 25, $secure = 'tls', $debuggingMode = false)
   {
      $this->SMTPUsername = $user;
      $this->SMTPPassword = $pass;
      $this->SMTPHost = $host;
      $this->SMTPPort = $port;
      $this->SMTPSecure = $secure;
      $this->SMTPDebugging = $debuggingMode;
   }

   public function setIMAPData($host, $postBox, $user, $pass, $port = 143, $secure = '', $flags = '')
   {
      $this->IMAPCopy = true;
      $this->IMAPHost = $host;
      $this->IMAPBox = $postBox;
      $this->IMAPUsername = $user;
      $this->IMAPPassword = $pass;
      $this->IMAPPort = $port;
      $this->IMAPSecure = $secure;
      $this->IMAPFlags = $flags;
   }

   public function addAddress($address, $name = '')
   {
      return $this->_addAnAddress('to', $address, $name);
   }

   public function addCC($address, $name = '')
   {
      return $this->_addAnAddress('cc', $address, $name);
   }

   public function addBCC($address, $name = '')
   {
      return $this->_addAnAddress('bcc', $address, $name);
   }

   public function addReplyTo($address, $name = '')
   {
      return $this->_addAnAddress('ReplyTo', $address, $name);
   }

   private function _addAnAddress($kind, $address, $name = '')
   {
      if(!preg_match('/^(to|cc|bcc|ReplyTo)$/', $kind))
      {
         print 'Invalid recipient array: ' .$kind;
         return false;
      }

      $address = trim($address);
      $name = trim(preg_replace('/[\r\n]+/', '', $name));

      if(!filter_var($address, FILTER_VALIDATE_EMAIL))
      {
         $this->_setError('ungültige Adresse: '.$address);

         print 'ungültige Adresse: '.$address;
         return false;
      }

      if($kind != 'ReplyTo')
      {
         if(!isset($this->all_recipients[strtolower($address)]))
         {
            array_push($this->$kind, array($address, $name));
            $this->all_recipients[strtolower($address)] = true;
            return true;
         }
      }
      else
      {
         if(!array_key_exists(strtolower($address), $this->replyTo))
         {
            $this->replyTo[strtolower($address)] = array($address, $name);
            return true;
         }
      }

      return false;
   }

   public function setFrom($address, $name = '', $auto = 1)
   {
      $address = trim($address);
      $name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim

      if(!filter_var($address, FILTER_VALIDATE_EMAIL))
      {
         $this->_setError('ungültige Adresse: '. $address);

         print 'ungültige Adresse: '.$address;
         return false;
      }

      $this->from = $address;
      $this->fromName = $name;

      if($auto)
      {
         if(empty($this->replyTo))
         {
            $this->_addAnAddress('ReplyTo', $address, $name);
         }

         if(empty($this->sender))
         {
            $this->sender = $address;
         }
      }

      return true;
   }

   public function setConfirmReadingTo($address)
   {
      $this->confirmReadingTo = $address;
   }

   public function setSubject($text)
   {
      $this->subject = $text;
   }

   public function setMailContent($message)
   {
      $this->body = $message;
   }

   public function setDKIM($domain, $privateKey, $identity = '', $passphrase = '')
   {
      if(!empty($domain) && !empty($privateKey))
      {
         $this->mailerDKIM = new MailerDKIM($domain, $privateKey, $identity, $passphrase);
      }
   }

   public function sign($cert_filename, $key_filename, $key_pass)
   {
      $this->sign_cert_file = $cert_filename;
      $this->sign_key_file = $key_filename;
      $this->sign_key_pass = $key_pass;
   }

   public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
   {
      $filename = basename($path);

      $this->attachment[] = array(
                                  0 => $path,
                                  1 => $filename,
                                  2 => empty($name) ? $filename : $name,
                                  3 => $encoding,
                                  4 => $type,
                                  5 => false,
                                  6 => 'attachment',
                                  7 => 0
                                 );
      return true;
   }

   public function addCustomHeader($custom_header)
   {
      $this->customHeader[] = explode(':', $custom_header, 2);
   }

   public function setCallbackFunction($callback)
   {
      $this->callback = $callback;
   }

   public function setMessageId($id)
   {
      $this->messageId = $id;
   }

   public function setAltBody($text)
   {
      $this->altBody = $text;
   }

   public function send()
   {
      try
      {
         if((count($this->to) + count($this->cc) + count($this->bcc)) < 1)
         {
            throw new phpmailerException('Bitte geben Sie mindestens eine Empfänger E-Mailadresse an.', self::STOP_CRITICAL);
         }

         if(!empty($this->altBody))
         {
            $this->contentType = 'multipart/alternative';
         }

         $this->error_count = 0;
         $this->_setMessageType();
         $header = $this->_createHeader();
         $body = $this->_createBody();

         if($this->mailerDKIM != null)
         {
            $header = str_replace("\r\n", "\n", $this->mailerDKIM->sign($header, $this->subject, $body)).$header;
         }

         switch($this->mailer)
         {
            case 'sendmail':
               return $this->_sendmailSend($header, $body);

            case 'smtp':
               return $this->_SMTPSend($header, $body);

            default:
               return $this->_mailSend($header, $body);
         }
      }
      catch(Exception $e)
      {
         return $e;
      }
   }

   private function _sendmailSend($header, $body)
   {
      if(!empty($this->sender))
      {
         $sendmail = sprintf("%s -oi -f %s -t", escapeshellcmd($this->sendmail), escapeshellarg($this->sender));
      }
      else
      {
         $sendmail = sprintf("%s -oi -t", escapeshellcmd($this->sendmail));
      }

      if($this->singleTo === true)
      {
         foreach($this->singleToArray as $key => $val)
         {
            if(!@$mail = popen($sendmail, 'w'))
            {
               throw new phpmailerException('Konnte folgenden Befehl nicht ausführen: '.$this->sendmail, self::STOP_CRITICAL);
            }

            fputs($mail, "To: ".$val."\n");
            fputs($mail, $header);
            fputs($mail, $body);
            $result = pclose($mail);

            $this->__doCallback($result == 0 ? 1 : 0, $val, $this->cc, $this->bcc, $this->subject, $body, $this->messageId);

            if($result != 0)
            {
               throw new phpmailerException('Konnte folgenden Befehl nicht ausführen: '.$this->sendmail, self::STOP_CRITICAL);
            }
        }
      }
      else
      {
         if(!@$mail = popen($sendmail, 'w'))
         {
           throw new phpmailerException('Konnte folgenden Befehl nicht ausführen: '.$this->sendmail, self::STOP_CRITICAL);
         }

         fputs($mail, $header);
         fputs($mail, $body);
         $result = pclose($mail);

         $this->__doCallback($result == 0 ? 1 : 0, $this->_addrAppend('', $this->to), $this->cc, $this->bcc, $this->subject, $body, $this->messageId);

         if($result != 0)
         {
            throw new phpmailerException('Konnte folgenden Befehl nicht ausführen: '.$this->sendmail, self::STOP_CRITICAL);
         }
      }

      return true;
   }

   private function _mailSend($header, $body)
   {
      $toArr = array();

      foreach($this->to as $t)
      {
         $toArr[] = $this->_addrFormat($t);
      }

      $to = implode(', ', $toArr);
      $s = $this->_encodeHeader($this->_secureHeader($this->subject));
      $params = sprintf("-oi -f %s", $this->sender);

      $old_from = ini_get('sendmail_from');
      ini_set('sendmail_from', $this->sender);

      if($this->singleTo === true && count($toArr) > 1)
      {
         foreach ($toArr as $key => $val)
         {
            $rt = mail($val, $s, $body, $header, $params);
            $this->__doCallback($rt, $val, $this->cc, $this->bcc, $header, $s, $body, $params, $this->messageId, true);
         }
      }
      else
      {
         $rt = mail($to, $s, $body, $header, $params);
         $this->__doCallback($rt, $to, $this->cc, $this->bcc, $header, $s, $body, $params, $this->messageId, true);
      }

      if(isset($old_from))
      {
         ini_set('sendmail_from', $old_from);
      }

      if(!$rt)
      {
         throw new phpmailerException('Die Mail-Funktion konnte nicht aufgerufen werden', self::STOP_CRITICAL);
      }

      return true;
   }

   private function _SMTPSend($header, $body)
   {
      $bad_rcpt = array();

      if(!$this->_SMTPConnect())
      {
         throw new phpmailerException('SMTP-Verbindung fehlgeschlagen', self::STOP_CRITICAL);
      }

      $smtp_from = empty($this->sender) ? $this->from : $this->sender;

      if(!$this->smtp->mail($smtp_from))
      {
         throw new phpmailerException('Die folgende Absenderadresse ist nicht korrekt: '.$smtp_from, self::STOP_CRITICAL);
      }

      foreach($this->to as $to)
      {
         if(!$this->smtp->recipient($to[0]))
         {
            $bad_rcpt[] = $to[0];
            $this->__doCallback(0, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
         else
         {
            $this->__doCallback(1, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
      }

      foreach($this->cc as $cc)
      {
         if(!$this->smtp->recipient($cc[0]))
         {
            $bad_rcpt[] = $cc[0];
            $this->__doCallback(0, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
         else
         {
            $this->__doCallback(1, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
      }

      foreach($this->bcc as $bcc)
      {
         if(!$this->smtp->recipient($bcc[0]))
         {
            $bad_rcpt[] = $bcc[0];
            $this->__doCallback(0, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
         else
         {
            $this->__doCallback(1, $to[0], '', '', $this->subject, $body, $this->messageId);
         }
      }

      if(count($bad_rcpt) > 0 )
      {
         $badaddresses = implode(', ', $bad_rcpt);
         throw new phpmailerException('SMTP Fehler: Die folgenden Empfänger sind nicht korrekt: '.$badaddresses);
      }

      if(!$this->smtp->data($header.$body))
      {
         throw new phpmailerException('SMTP Fehler: Daten werden nicht akzeptiert.', self::STOP_CRITICAL);
      }

      return true;
   }

   private function _SMTPConnect()
   {
      if(is_null($this->smtp))
      {
         $this->smtp = new SMTP($this->SMTPDebugging);
      }

      $connection = $this->smtp->connected();

      try
      {
         while(!$connection)
         {
            $hostinfo = array();

            $tls = ($this->SMTPSecure == 'tls');
            $ssl = ($this->SMTPSecure == 'ssl');

            if($this->smtp->connect(($ssl ? 'ssl://':'').$this->SMTPHost, $this->SMTPPort, $this->SMTPTimeout))
            {
               $hello = (isset($this->helo) ? $this->helo : $this->_serverHostname());
               $this->smtp->hello($hello);

               if($tls)
               {
                  if(!$this->smtp->startTLS())
                  {
                     throw new phpmailerException('SMTP Fehler: TLS kann nicht gestartet werden.');
                  }

                  $this->smtp->hello($hello);
               }

               $connection = true;

               if(!$this->smtp->authenticate($this->SMTPUsername, $this->SMTPPassword))
               {
                  throw new phpmailerException('SMTP Fehler: Authentifizierung fehlgeschlagen.');
               }
            }

            if(!$connection)
            {
               throw new phpmailerException('SMTP Fehler: Konnte keine Verbindung zum SMTP-Host herstellen.');
            }
         }
      }
      catch(phpmailerException $e)
      {
         $this->smtp->reset();
         throw $e;
      }

      return true;
   }

   private function _SMTPClose()
   {
      if(!is_null($this->smtp))
      {
         if($this->smtp->connected())
         {
            $this->smtp->quit();
            $this->smtp->close();
         }
      }
   }

   private function _addrAppend($type, $addr)
   {
      $addr_str = $type . ': ';
      $addresses = array();

      foreach($addr as $a)
      {
         $addresses[] = $this->_addrFormat($a);
      }

      $addr_str .= implode(', ', $addresses);
      $addr_str .= $this->LE;

      return $addr_str;
   }

   private function _addrFormat($addr)
   {
      if(empty($addr[1]))
      {
         return $this->_secureHeader($addr[0]);
      }
      else
      {
         return $this->_encodeHeader($this->_secureHeader($addr[1]), 'phrase') . " <" . $this->_secureHeader($addr[0]) . ">";
      }
   }

   private function _wrapText($message, $length, $qp_mode = false)
   {
      $soft_break = ($qp_mode) ? sprintf(' =%s', $this->LE) : $this->LE;

      $message = $this->_fixEOL($message);

      if(substr($message, -1) == $this->LE)
      {
         $message = substr($message, 0, -1);
      }

      $line = explode($this->LE, $message);
      $lineAnz = count($line);
      $message = '';

      for($i = 0 ;$i < $lineAnz; $i++)
      {
         $line_part = explode(' ', $line[$i]);
         $linePartAnz = count($line_part);
         $buf = '';

         for($e = 0; $e < $linePartAnz; $e++)
         {
            $word = $line_part[$e];

            if($qp_mode && strlen($word) > $length)
            {
               $space_left = $length - strlen($buf) - 1;

               if($e != 0)
               {
                  if($space_left > 20)
                  {
                     $len = $this->_UTF8CharBoundary($word, $space_left);
                     $part = substr($word, 0, $len);
                     $word = substr($word, $len);
                     $buf .= ' ' . $part;
                     $message .= $buf . sprintf("=%s", $this->LE);
                  }
                  else
                  {
                     $message .= $buf.$soft_break;
                  }

                  $buf = '';
               }

               while(strlen($word) > 0)
               {
                  $len = $this->_UTF8CharBoundary($word, $length);
                  $part = substr($word, 0, $len);
                  $word = substr($word, $len);

                  if(strlen($word) > 0)
                  {
                     $message .= $part.sprintf('=%s', $this->LE);
                  }
                  else
                  {
                     $buf = $part;
                  }
               }
            }
            else
            {
               $buf_o = $buf;
               $buf .= ($e == 0) ? $word : (' ' . $word);

               if(strlen($buf) > $length && $buf_o != '')
               {
                  $message .= $buf_o.$soft_break;
                  $buf = $word;
               }
            }
         }

         $message .= $buf.$this->LE;
      }

      return $message;
   }

   private function _UTF8CharBoundary($encodedText, $maxLength)
   {
      $foundSplitPos = false;
      $lookBack = 3;

      while(!$foundSplitPos)
      {
         $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
         $encodedCharPos = strpos($lastChunk, '=');

         if($encodedCharPos !== false)
         {
            $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
            $dec = hexdec($hex);

            if($dec < 128)
            {
               $maxLength = ($encodedCharPos == 0) ? $maxLength :
               $maxLength - ($lookBack - $encodedCharPos);
               $foundSplitPos = true;
            }
            else if($dec >= 192)
            {
               $maxLength = $maxLength - ($lookBack - $encodedCharPos);
               $foundSplitPos = true;
            }
            else if($dec < 192)
            {
               $lookBack += 3;
            }
         }
         else
         {
            $foundSplitPos = true;
         }
      }

      return $maxLength;
   }

   private function _setWordWrap()
   {
      if($this->wordWrap < 1)
      {
         return;
      }

      switch($this->message_type)
      {
         case 'alt':
         case 'alt_attachments':
            $this->altBody = $this->_wrapText($this->altBody, $this->wordWrap);
            break;

         default:
            $this->body = $this->_wrapText($this->body, $this->wordWrap);
            break;
      }
   }

   private function _createHeader($ignoreMailSend = false, $newBoundary = true)
   {
      $result = '';

      if($newBoundary)
      {
         $uniq_id = md5(uniqid(time()));
         $this->boundary[1] = 'b1_'.$uniq_id;
         $this->boundary[2] = 'b2_'.$uniq_id;
      }

      $result .= $this->_headerLine('Date', $this->_RFCDate());
      $result .= $this->_headerLine('Return-Path', trim(empty($this->sender) ? $this->from : $this->sender));

      if($this->mailer != 'mail' || $ignoreMailSend)
      {
         if($this->singleTo === true)
         {
            foreach($this->to as $t)
            {
               $this->singleToArray[] = $this->_addrFormat($t);
            }
         }
         else
         {
            if(count($this->to) > 0)
            {
               $result .= $this->_addrAppend('To', $this->to);
            }
            else if(count($this->cc) == 0)
            {
               $result .= $this->_headerLine('To', 'undisclosed-recipients:;');
            }
         }
      }

      $from = array();
      $from[0][0] = trim($this->from);
      $from[0][1] = $this->fromName;
      $result .= $this->_addrAppend('From', $from);

      if(count($this->cc) > 0)
      {
         $result .= $this->_addrAppend('Cc', $this->cc);
      }

      if((($this->mailer == 'sendmail') || ($this->mailer == 'mail')) && (count($this->bcc) > 0))
      {
         $result .= $this->_addrAppend('Bcc', $this->bcc);
      }

      if(count($this->replyTo) > 0)
      {
         $result .= $this->_addrAppend('Reply-to', $this->replyTo);
      }

      if($this->mailer != 'mail' || $ignoreMailSend)
      {
         $result .= $this->_headerLine('Subject', $this->_encodeHeader($this->_secureHeader($this->subject)));
      }

      if(!empty($this->messageId))
      {
         $result .= $this->_headerLine('Message-ID', $this->messageId);
      }
      else
      {
         $result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->_serverHostname(), $this->LE);
         $this->messageId = sprintf("<%s@%s>%s", $uniq_id, $this->_serverHostname(), $this->LE);
      }

      $result .= $this->_headerLine('X-Priority', $this->priority);

      if(!empty($this->confirmReadingTo))
      {
         $result .= $this->_headerLine('Disposition-Notification-To', '<' . trim($this->confirmReadingTo) . '>');
      }

      for($index = 0; $index < count($this->customHeader); $index++)
      {
         $result .= $this->_headerLine(trim($this->customHeader[$index][0]), $this->_encodeHeader(trim($this->customHeader[$index][1])));
      }

      if(!$this->sign_key_file)
      {
         $result .= $this->_headerLine('MIME-Version', '1.0');
         $result .= $this->_getMailMIME();
      }

      return $result;
   }

   private function _getMailMIME()
   {
      $result = '';

      switch($this->message_type)
      {
         case 'plain':
            $result .= $this->_headerLine('Content-Transfer-Encoding', $this->encoding);
            $result .= sprintf("Content-Type: %s; charset=\"%s\"", $this->contentType, $this->charSet);
            break;

         case 'attachments':
         case 'alt_attachments':
            $result .= $this->_headerLine('Content-Type', 'multipart/mixed;');
            $result .= $this->_textLine("\tboundary=\"" . $this->boundary[1] . '"');
            break;

         case 'alt':
            $result .= $this->_headerLine('Content-Type', 'multipart/alternative;');
            $result .= $this->_textLine("\tboundary=\"" . $this->boundary[1] . '"');
            break;
      }

      if($this->mailer != 'mail')
      {
         $result .= $this->LE.$this->LE;
      }

      return $result;
   }

   private function _createBody($ignoreAttachment = false)
   {
      $body = '';

      if($this->sign_key_file)
      {
         $body .= $this->_getMailMIME();
      }

      $this->_setWordWrap();

      switch(!$ignoreAttachment ? $this->message_type : 'alt')
      {
         case 'alt':
            $body .= $this->_getBoundary($this->boundary[1], '', 'text/plain', '');
            $body .= $this->_encodeString($this->altBody, $this->encoding);
            $body .= $this->LE.$this->LE;
            $body .= $this->_getBoundary($this->boundary[1], '', 'text/html', '');
            $body .= $this->_encodeString($this->body, $this->encoding);
            $body .= $this->LE.$this->LE;
            $body .= $this->_endBoundary($this->boundary[1]);
            break;

         case 'plain':
            $body .= $this->_encodeString($this->body, $this->encoding);
            break;

         case 'attachments':
            $body .= $this->_getBoundary($this->boundary[1], '', '', '');
            $body .= $this->_encodeString($this->body, $this->encoding);
            $body .= $this->LE;
            $body .= $this->_attachAll();
            break;

         case 'alt_attachments':
            $body .= sprintf("--%s%s", $this->boundary[1], $this->LE);
            $body .= sprintf("Content-Type: %s;%s" . "\tboundary=\"%s\"%s", 'multipart/alternative', $this->LE, $this->boundary[2], $this->LE.$this->LE);
            $body .= $this->_getBoundary($this->boundary[2], '', 'text/plain', '').$this->LE;
            $body .= $this->_encodeString($this->altBody, $this->encoding);
            $body .= $this->LE.$this->LE;
            $body .= $this->_getBoundary($this->boundary[2], '', 'text/html', '').$this->LE;
            $body .= $this->_encodeString($this->body, $this->encoding);
            $body .= $this->LE.$this->LE;
            $body .= $this->_endBoundary($this->boundary[2]);
            $body .= $this->_attachAll();
            break;
      }

      if($this->_isError())
      {
         $body = '';
      }
      else if($this->sign_key_file)
      {
         try
         {
            $file = tempnam('', 'mail');
            file_put_contents($file, $body);
            $signed = tempnam("", "signed");

            if(@openssl_pkcs7_sign($file, $signed, "file://".$this->sign_cert_file, array("file://".$this->sign_key_file, $this->sign_key_pass), NULL))
            {
               @unlink($file);
               @unlink($signed);
               $body = file_get_contents($signed);
            }
            else
            {
               @unlink($file);
               @unlink($signed);
               throw new phpmailerException('Fehler beim Signieren: '.openssl_error_string());
            }
         }
         catch (phpmailerException $e)
         {
            $body = '';
         }
      }

      return $body;
   }

   private function _getBoundary($boundary, $charSet, $contentType, $encoding)
   {
      $result = '';

      if(empty($charSet))
      {
         $charSet = $this->charSet;
      }

      if(empty($contentType))
      {
         $contentType = $this->contentType;
      }

      if(empty($encoding))
      {
         $encoding = $this->encoding;
      }

      $result .= $this->_textLine('--'.$boundary);
      $result .= sprintf("Content-Type: %s; charset = \"%s\"", $contentType, $charSet);
      $result .= $this->LE;
      $result .= $this->_headerLine('Content-Transfer-Encoding', $encoding);
      $result .= $this->LE;

      return $result;
   }

   private function _endBoundary($boundary)
   {
      return $this->LE.'--'.$boundary.'--'.$this->LE;
   }

   private function _setMessageType()
   {
      if(count($this->attachment) < 1 && strlen($this->altBody) < 1)
      {
         $this->message_type = 'plain';
      }
      else
      {
         if(count($this->attachment) > 0)
         {
            $this->message_type = 'attachments';
         }

         if(strlen($this->altBody) > 0 && count($this->attachment) < 1)
         {
            $this->message_type = 'alt';
         }

         if(strlen($this->altBody) > 0 && count($this->attachment) > 0)
         {
            $this->message_type = 'alt_attachments';
         }
      }
   }

   private function _headerLine($name, $value)
   {
      return $name.': '.$value.$this->LE;
   }

   private function _textLine($value)
   {
      return $value.$this->LE;
   }

   private function _attachAll()
   {
      $mime = array();
      $cidUniq = array();
      $incl = array();

      foreach ($this->attachment as $attachment)
      {
         $bString = $attachment[5];

         if($bString)
         {
            $string = $attachment[0];
         }
         else
         {
            $path = $attachment[0];
         }

         if(in_array($attachment[0], $incl))
         {
            continue;
         }

         $filename = $attachment[1];
         $name = $attachment[2];
         $encoding = $attachment[3];
         $type = $attachment[4];
         $disposition = $attachment[6];
         $cid = $attachment[7];
         $incl[] = $attachment[0];

         if($disposition == 'inline' && isset($cidUniq[$cid]))
         {
            continue;
         }

         $cidUniq[$cid] = true;

         $mime[] = sprintf("--%s%s", $this->boundary[1], $this->LE);
         $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $this->_encodeHeader($this->_secureHeader($name)), $this->LE);
         $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->LE);

         if($disposition == 'inline')
         {
            $mime[] = sprintf("Content-ID: <%s>%s", $cid, $this->LE);
         }

         $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, $this->_encodeHeader($this->_secureHeader($name)), $this->LE.$this->LE);

         if($bString)
         {
            $mime[] = $this->_encodeString($string, $encoding);

            if($this->_isError())
            {
               return '';
            }

            $mime[] = $this->LE.$this->LE;
         }
         else
         {
            $mime[] = $this->_encodeFile($path, $encoding);

            if($this->_isError())
            {
               return '';
            }

            $mime[] = $this->LE.$this->LE;
         }
      }

      $mime[] = sprintf("--%s--%s", $this->boundary[1], $this->LE);

      return join('', $mime);
   }

   private function _encodeFile($path, $encoding = 'base64')
   {
      try
      {
         if(!is_readable($path))
         {
            throw new phpmailerException('Datei Fehler: konnte folgende Datei nicht öffnen: '.$path, self::STOP_CONTINUE);
         }

         $magic_quotes = get_magic_quotes_runtime();
         set_magic_quotes_runtime(0);

         $file_buffer = file_get_contents($path);
         $file_buffer = $this->_encodeString($file_buffer, $encoding);
         set_magic_quotes_runtime($magic_quotes);

         return $file_buffer;
      }
      catch(Exception $e)
      {
         $this->_setError($e->getMessage());
         return '';
      }
   }

   private function _encodeString($str, $encoding = 'base64')
   {
      $encoded = '';

      switch(strtolower($encoding))
      {
         case 'base64':
            $encoded = chunk_split(base64_encode($str), 76, $this->LE);
            break;

         case '7bit':
         case '8bit':
            $encoded = $this->_fixEOL($str);
            if(substr($encoded, -(strlen($this->LE))) != $this->LE)
            {
               $encoded .= $this->LE;
            }
            break;

         case 'binary':
            $encoded = $str;
            break;

         case 'quoted-printable':
            $encoded = $this->_encodeQP($str);
            break;

         default:
            $this->_setError($this->_lang('encoding').$encoding);
      }

      return $encoded;
   }

   private function _encodeHeader($str, $position = 'text')
   {
      $x = 0;

      switch(strtolower($position))
      {
         case 'phrase':
            if(!preg_match('/[\200-\377]/', $str))
            {
               $encoded = addcslashes($str, "\0..\37\177\\\"");

               if(($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str))
               {
                  return $encoded;
               }
               else
               {
                  return '"'.$encoded.'"';
               }
            }

            $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
            break;

         case 'comment':
            $x = preg_match_all('/[()"]/', $str, $matches);

         case 'text':
         default:
            $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
            break;
      }

      if($x == 0)
      {
         return $str;
      }

      $maxlen = 75 - 7 - strlen($this->charSet);

      if(strlen($str) / 3 < $x)
      {
         $encoding = 'B';

         if($this->_hasMultiBytes($str))
         {
            $encoded = $this->_base64EncodeWrapMB($str);
         }
         else
         {
            $encoded = base64_encode($str);
            $maxlen -= $maxlen % 4;
            $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
         }
      }
      else
      {
         $encoding = 'Q';
         $encoded = $this->_encodeQ($str, $position);
         $encoded = $this->_wrapText($encoded, $maxlen, true);
         $encoded = str_replace('='.$this->LE, "\n", trim($encoded));
      }

      $encoded = preg_replace('/^(.*)$/m', ' =?'.$this->charSet.'?'.$encoding.'?$1?=', $encoded);
      $encoded = trim(str_replace("\n", $this->LE, $encoded));

      return $encoded;
   }

   private function _hasMultiBytes($str)
   {
      return (strlen($str) > mb_strlen($str, $this->charSet));
   }

   private function _base64EncodeWrapMB($str)
   {
      $start = '=?'.$this->charSet.'?B?';
      $end = '?=';
      $encoded = '';

      $mb_length = mb_strlen($str, $this->charSet);
      $length = 75 - strlen($start) - strlen($end);
      $ratio = $mb_length / strlen($str);
      $offset = $avgLength = floor($length * $ratio * .75);

      for($i = 0; $i < $mb_length; $i += $offset)
      {
         $lookBack = 0;

         do
         {
            $offset = $avgLength - $lookBack;
            $chunk = mb_substr($str, $i, $offset, $this->charSet);
            $chunk = base64_encode($chunk);
            $lookBack++;
         }
         while(strlen($chunk) > $length);

         $encoded .= $chunk.$this->LE;
      }

      $encoded = substr($encoded, 0, -strlen($this->LE));

      return $encoded;
   }

   private function _encodeQPphp($input = '', $line_max = 76, $space_conv = false)
   {
      $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
      $lines = preg_split('/(?:\r\n|\r|\n)/', $input);
      $eol = "\r\n";
      $escape = '=';
      $output = '';

      while(list(, $line) = each($lines))
      {
         $linlen = strlen($line);
         $newline = '';

         for($i = 0; $i < $linlen; $i++)
         {
            $c = substr($line, $i, 1);
            $dec = ord($c);

            if (($i == 0) && ($dec == 46))
            {
               $c = '=2E';
            }

            if($dec == 32)
            {
               if($i == ($linlen - 1))
               {
                  $c = '=20';
               }
               else if($space_conv)
               {
                  $c = '=20';
               }
            }
            else if(($dec == 61) || ($dec < 32 ) || ($dec > 126))
            {
               $h2 = floor($dec / 16);
               $h1 = floor($dec % 16);
               $c = $escape.$hex[$h2].$hex[$h1];
            }

            if((strlen($newline) + strlen($c)) >= $line_max)
            {
               $output .= $newline.$escape.$eol;
               $newline = '';

               if($dec == 46)
               {
                  $c = '=2E';
               }
            }

            $newline .= $c;
         }

         $output .= $newline.$eol;
      }

      return $output;
   }

   private function _encodeQP($string, $line_max = 76, $space_conv = false)
   {
      $filters = stream_get_filters();

      if(!in_array('convert.*', $filters))
      {
         return $this->_encodeQPphp($string, $line_max, $space_conv);
      }

      $fp = fopen('php://temp/', 'r+');
      $string = preg_replace('/\r\n?/', $this->LE, $string);
      $params = array('line-length' => $line_max, 'line-break-chars' => $this->LE);
      $s = stream_filter_append($fp, 'convert.quoted-printable-encode', STREAM_FILTER_READ, $params);
      fputs($fp, $string);
      rewind($fp);
      $out = stream_get_contents($fp);
      stream_filter_remove($s);
      $out = preg_replace('/^\./m', '=2E', $out);

      return $out;
   }

   private function _encodeQ($str, $position = 'text')
   {
      $encoded = preg_replace('/[\r\n]*/', '', $str);

      switch(strtolower($position))
      {
         case 'phrase':
            $encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;

         case 'comment':
            $encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);

         case 'text':
         default:
            $encoded = preg_replace('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/e',
                                    "'='.sprintf('%02X', ord('\\1'))", $encoded);
            break;
      }

      $encoded = str_replace(' ', '_', $encoded);

      return $encoded;
   }

   public function clearAddresses()
   {
      foreach($this->to as $to)
      {
         unset($this->all_recipients[strtolower($to[0])]);
      }

      $this->to = array();
   }

   public function clearCCs()
   {
      foreach($this->cc as $cc)
      {
         unset($this->all_recipients[strtolower($cc[0])]);
      }

      $this->cc = array();
   }

   public function clearBCCs()
   {
      foreach($this->bcc as $bcc)
      {
         unset($this->all_recipients[strtolower($bcc[0])]);
      }

      $this->bcc = array();
   }

   public function clearReplyTos()
   {
      $this->replyTo = array();
   }

   public function clearAllRecipients()
   {
      $this->to = array();
      $this->cc = array();
      $this->bcc = array();
      $this->all_recipients = array();
   }

   public function clearAttachments()
   {
      $this->attachment = array();
   }

   public function clearCustomHeaders()
   {
      $this->customHeader = array();
   }

   private function _setError($msg)
   {
      $this->error_count++;

      if($this->Mailer == 'smtp' and !is_null($this->smtp))
      {
         $lasterror = $this->smtp->getError();

         if(!empty($lasterror) && array_key_exists('smtp_msg', $lasterror))
         {
            $msg .= '<p>'.'Fehler vom SMTP Server: '.$lasterror['smtp_msg']."</p>\n";
         }
      }

      $this->errorInfo = $msg;
   }

   private function _RFCDate()
   {
      $tz = date('Z');
      $tzs = ($tz < 0) ? '-' : '+';
      $tz = abs($tz);
      $tz = (int)($tz / 3600) * 100 + ($tz % 3600) / 60;

      return sprintf("%s %s%04d", date('D, j M Y H:i:s'), $tzs, $tz);
   }

   private function _serverHostname()
   {
      if(!empty($this->hostname))
      {
         $result = $this->hostname;
      }
      else if(isset($_SERVER['SERVER_NAME']))
      {
         $result = $_SERVER['SERVER_NAME'];
      }
      else
      {
         $result = 'localhost.localdomain';
      }

      return $result;
   }

   private function _isError()
   {
      return $this->error_count > 0;
   }

   private function _fixEOL($str)
   {
      $str = str_replace("\r\n", "\n", $str);
      $str = str_replace("\r", "\n", $str);
      $str = str_replace("\n", $this->LE, $str);

      return $str;
   }

   private function _secureHeader($str)
   {
      $str = str_replace("\r", '', $str);
      $str = str_replace("\n", '', $str);

      return trim($str);
   }

   protected function __doCallback($isSent, $to, $cc, $bcc, $header, $subject, $body, $params, $messageid, $isMailSend = false)
   {
      if($this->IMAPCopy)
      {
         $this->_IMAPCopy($header, $body, $params, $isMailSend);
      }

      $hasAttachment = false;

      if($this->message_type == 'attachments' || $this->message_type == 'alt_attachments')
      {
         $body = $this->_createBody(true);
         $hasAttachment = true;
      }

      $this->callback($isSent, $to, $cc, $bcc, $subject, $body, $messageid, $hasAttachment);
   }

   private function _IMAPCopy($header, $message, $params, $isMailSend)
   {
      try
      {
         if($isMailSend)
         {
            $header = $this->_createHeader(true, false);
         }

         $mailbox = '{'.$this->IMAPHost.':'.$this->IMAPPort.(!empty($this->IMAPSecure) ? '/imap/'.$this->IMAPSecure.'/novalidate-cert' : '').'}'.$this->IMAPBox;
         $stream = imap_open($mailbox, $this->IMAPUsername, $this->IMAPPassword);
         $content = $header.$this->LE.$this->LE.$message;

         if(!empty($this->IMAPFlags))
         {
            imap_append($stream, $mailbox, $content, $this->IMAPFlags);
         }
         else
         {
            imap_append($stream, $mailbox, $content);
         }

         imap_close($stream);
      }
      catch(Exception $e)
      {
         print $e;
      }
   }
}

class phpmailerException extends Exception
{
   public function errorMessage()
   {
      return '<strong>'.$this->getMessage()."</strong><br />\n";
   }
}

?>