<?php 
    namespace AlexPCooper;

//    require('vendor/autoload.php');

    use ZBateson\MailMimeParser\MailMimeParser;
    use ZBateson\MailMimeParser\Header\HeaderConsts;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class newsletters2kindle
    {
        public $kindle_email;
        public $imap_email;
        public $imap_host;
        public $imap_port;
        public $imap_dir;
        public $imap_user;
        public $imap_pass;

        public $debug;
        public $delete_mail_after;

        private $imap_conn;
        private $mail_message;
        private $pdf_document;
        private $mail_subject;
        private $mail_from;

        function __construct() 
        {
            $this->debug                = false;
            $this->delete_mail_after    = true;
        }

        public function checkMail()
        {
            if ($this->checkMailbox())
            {
                $this->getMessage();
                $this->makePDF();
                $this->sendToKindle();

                if ($this->delete_mail_after)
                {
                    imap_delete($this->imap_conn, 1);
                }
            }
            elseif ($this->debug)
            {
                echo 'No newsletters / emails found';
            }

            if ($this->imap_conn)
            {
                imap_close($this->imap_conn); 
            }
        }

        private function checkMailbox()
        {
            $this->imap_conn = imap_open("{".trim($this->imap_host).":".trim($this->imap_port)."/imap/ssl}".$this->imap_dir, $this->imap_user, $this->imap_pass) or die('Failed to open connection: ' . imap_last_error());

            $msgs_number = 0;
            if ($this->imap_conn)
            {
                // get the total amount of messages
                $mbox_check = imap_check($this->imap_conn);
                $msgs_number = $mbox_check->Nmsgs;
            }

            return $msgs_number;
                
        }
        
        private function getMessage()
        {
            // get message body
            $imap_body = imap_fetchbody($this->imap_conn, 1, "");
    
            // clean up, as best as we can, the raw HTML / styling from the email body
            $imap_body = str_replace('type=3D"text"', 'type="text', $imap_body);
            $imap_body = str_replace('<style type=3D"text/css">', '<style type="text/css">', $imap_body);
            $imap_body = str_replace('<style amf:inline=3D"amf:inline" type=3D"text/css">', '<style type="text/css">', $imap_body);
            $imap_body = str_replace('<style amf:inline="amf:inline" type="text/css">', '<style type="text/css">', $imap_body);
            $imap_body = strip_tags($imap_body, '<style><a><p>><br><br /><b><strong><u><i><em><img><h1><h2><h3><h4><h5><h6>');
            $imap_body = preg_replace('#<style type="text/css">.*?</style>#s', '', $imap_body);
    
            // create MailMimeParser
            $mail_parser = new MailMimeParser();
            $this->mail_message = $mail_parser->parse($imap_body, true);

            // var_dump($imap_body); die();
            // var_dump($this->mail_message->getHtmlContent()); die();

            return true;
        }


        private function makePDF()
        {

            // use the email subject as the subject of the PDF
            $this->mail_subject = trim(utf8_encode(imap_utf8($this->replace_4byte($this->mail_message->getHeaderValue(HeaderConsts::SUBJECT)))));
            if (strlen(trim($this->mail_subject)) == 0)
            {
                $this->mail_subject = 'Newsletter';
            }

            $this->mail_from = utf8_encode(imap_utf8($this->mail_message->getHeader(HeaderConsts::FROM)->getPersonName()));
            if (strlen(trim($this->mail_from)) == 0)
            {
                $this->mail_from = imap_utf8($this->mail_message->getHeader(HeaderConsts::FROM));
            }
            $this->mail_from = trim(str_replace('From: ', '', $this->mail_from));



            $this->pdf_document = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'default_font_size' => 25, 'orientation' => 'P', 'ignore_table_widths' => true, 'shrink_tables_to_fit' => false ]);
        
            // med-specific commands to ensure we get the best result in a PDF
            $this->pdf_document->shrink_tables_to_fit = 0;
            $this->pdf_document->SetDisplayMode('fullwidth');
            $this->pdf_document->allow_charset_conversion=true;
            $this->pdf_document->charset_in='UTF-8';

            $this->pdf_document->SetSubject($this->mail_subject); 
            $this->pdf_document->SetTitle($this->mail_subject);
            $this->pdf_document->SetAuthor($this->mail_from);
            $this->pdf_document->SetCreator('Newsletters To Kindle');

            $this->pdf_document->WriteHTML('<h1>'.$this->mail_subject.'</h1><h2>'.utf8_encode($this->mail_from).'</h2>');
            $this->pdf_document->AddPage();

            // chunk the email body because otherwise you can get issues with blank pages
            foreach (str_split($this->mail_message->getHtmlContent(), 10000) as $chunk)
            {
                $this->pdf_document->WriteHTML(mb_convert_encoding($chunk, "HTML-ENTITIES", "UTF-8"));
            }
    
            // $this->pdf_document->Output(); die();

            return $this->pdf_document;
        }
        

        private function sendToKindle()
        {
            $mail = new PHPMailer(true);

            try 
            {
                // Mail Server settings
                $mail->Host       = $this->imap_host;
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $this->imap_user;
                $mail->Password   = $this->imap_pass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = $this->imap_port;  
    
                //Recipients
                $mail->setFrom($this->imap_email);
                $mail->addAddress($this->kindle_email);

                if ($this->debug)
                {
                    $mail->addAddress($this->imap_email);
                }

                //Attachments
                $mail->AddStringAttachment( $this->pdf_document->Output('', 'S') , utf8_encode($this->mail_subject).'.pdf', 'base64', 'application/pdf');
    
                //Content
                $mail->Subject = 'Convert'; // has to be this for ePub / Kindle format (or it provides it as PDF);
                $mail->Body    = $this->mail_subject;
                $mail->AltBody = $this->mail_subject;
    
                $mail->send();
    
                if ($this->debug)
                {
                    echo 'Message "'.$this->mail_subject.'" has been sent to '.$this->kindle_email;
                }
                return true;
            } 
            catch (Exception $e) 
            {
                if ($this->debug)
                {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                return false;
            }
    

        }


        // to remove 4byte characters like emojis etc..
        // https://stackoverflow.com/questions/12807176/php-writing-a-simple-removeemoji-function
        private function replace_4byte($string) 
        {
            return preg_replace('%(?:
                  \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )%xs', '', $string);    
        }
    }