# Newsletters to Kindle
Uses IMAP to check a mailbox, converts HTML newsletter emails to PDFs and sends them to an Amazon Kindle email address

[![Latest Stable Version](https://poser.pugx.org/alexpcooper/newsletters-to-kindle/v/stable)](https://packagist.org/packages/mpdf/mpdf)
[![Total Downloads](https://poser.pugx.org/alexpcooper/newsletters-to-kindle/downloads)](https://packagist.org/packages/mpdf/mpdf)
[![License](https://poser.pugx.org/alexpcooper/newsletters-to-kindle/license)](https://packagist.org/packages/alexpcooper/newsletters-to-kindle)


### Process
1. Checks a folder in an email inbox, via IMAP
2. Collects the email and converts it into a PDF
3. Sends the PDF to Amazon, to convert into an ePub format and place in your Amazon Kindle library
4. Deletes the email


## Installation

Official installation method is via composer and its packagist package [alexpcooper/newsletters-to-kindle](https://packagist.org/packages/alexpcooper/newsletters-to-kindle).

```
$ composer require alexpcooper/newsletters-to-kindle
```


## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$newsletter2kindle = new newsletters2kindle();
$newsletter2kindle->kindle_email = 'your-kindle-email@kindle.com';
$newsletter2kindle->imap_email   = 'your-email@address.com';
$newsletter2kindle->imap_user    = 'your-email@address.com';
$newsletter2kindle->imap_pass    = 'emailpassword';
$newsletter2kindle->imap_host    = 'imap.host.com';
$newsletter2kindle->imap_port    = 993;
$newsletter2kindle->imap_dir     = 'Inbox';

$newsletter2kindle->checkMail();

```

### Parameters

| Parameter 	| Explanation 	|
|---	|---	|
| kindle_email 	| Used to send the PDF email to, so that it goes to your Amazon account 	|
| imap settings  	| Used to both check an email directory, such as your inbox, for an email to convert, as well as to send the PDF to   	|
| imap_dir  	| This can be your inbox or a subfolder, eg. "Inbox", "[Gmail]/All Mail", "my-newsletters", etc.  	|


## Debug

As the process of sending an email to a 3rd party (Amazon) is "fire and forget", without any confirmation or failure, an optional parameter will output what's happening, in the event of needing to troubleshoot. Add this prior to calling `checkMail()`.
```php

$newsletter2kindle->debug     = true;

```

## Troubleshooting
* Ensure that your "kindle_email" address is correct as it appears on your Amazon account for receiving documents (see [Edit Your Send to Kindle Email Address](https://www.amazon.co.uk/gp/help/customer/display.html?ref_=hp_left_v4_sib&nodeId=G7V489F2ZZU9JJGE))
* Ensure that your sending email address is permitted to send documents to your Amazon Kindle (see [Add an Email Address to Receive Documents in Your Kindle Library](https://www.amazon.co.uk/gp/help/customer/display.html?nodeId=GX9XLEVV8G4DB28H))

## Known Issues
Due to the composition of emails, occasionally a block of style tags may be printed onto the end document. 
