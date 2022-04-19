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

## Debug

As the process of sending an email to a 3rd party (Amazon) is "fire and forget", without any confirmation or failure, there are two optional parameters, in the event of needing to troubleshoot.

Add one or both of these prior to calling `checkMail()`.

```php
$newsletter2kindle->debug               = true; // false by default or when not specified
$newsletter2kindle->delete_mail_after   = false; // true by default or when not specified

```

### Parameters

| Parameter 	| Explanation 	|
|---	|---	|
| kindle_email 	| Used to send the PDF email to, so that it goes to your Amazon account 	|
| imap settings  	| Used to both check an email directory, such as your inbox, for an email to convert, as well as to send the PDF to Amazon as the FROM  address 	|
| imap_dir  	| This can be your inbox or a subfolder, eg. "Inbox", "[Gmail]/All Mail", "my-newsletters", etc.  	|
| debug 	| Prints out progress or any issues as it goes; for debugging only (defaults to false) 	|
| delete_mail_after 	| Keeps the mail in the mailbox after processing (defaults to true) - note that subsequent requests will pick up the same email again 	|


## Included Packages
With thanks to the following for these dependancies;
- https://github.com/mpdf/mpdf
- https://github.com/zbateson/mail-mime-parser
- https://github.com/swiftmailer/swiftmailer


## Requirements
- php's IMAP library (eg. extension=imap.so) needs to be enabled in your php.ini file


## Troubleshooting
* Ensure that your "kindle_email" address is correct as it appears on your Amazon account for receiving documents (see [Edit Your Send to Kindle Email Address](https://www.amazon.co.uk/gp/help/customer/display.html?ref_=hp_left_v4_sib&nodeId=G7V489F2ZZU9JJGE))
* Ensure that your sending email address is permitted to send documents to your Amazon Kindle (see [Add an Email Address to Receive Documents in Your Kindle Library](https://www.amazon.co.uk/gp/help/customer/display.html?nodeId=GX9XLEVV8G4DB28H))

## Known Issues
- Due to the composition of emails, occasionally a block of style tags may be printed onto the end document. This is caused by external sources, such as includes / images, being pulled in after the email is parsed.
- At present the Kindle document appears in Amazon without an Author being populated, even though it's on the PDF. This seems to be caused on Amazon's side, when they convert the document.
