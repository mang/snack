<?php

$config_email        = "snack@rymdkoloni.se";
$config_maildir      = "/var/snack/";
$config_write_dir    = "/var/www/snack/trees/";
$config_redirect_url = "index.php";

class Comment {
  public $reply_to;
  public $references;
  public $message_id;
  public $signature;
  public $email;
  public $body;

  public function validate() {
    if (!(($this->reply_to && 
	   $this->references && 
	   $this->signature && 
	   $this->email && 
	   $this->body))) {
      die("one or more fields left empty");
    }
    return True;
  }

  public function send($address) {
    $subject = "reply";
    $headers .= "In-Reply-To: " . $this->reply_to . "\r\n" .
      "References: " . $this->references;

    mail($address, $subject, $this->body, $headers);
  }
}