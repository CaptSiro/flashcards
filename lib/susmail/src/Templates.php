<?php

  require_once __DIR__ . "/Mail.php";

  class MailTemplate {
    static function login (User $user, $url): MailTemplate {
      return new MailTemplate($user, "Flash Cards login", "Hello,<br><br>
        
        To log in to Flash Cards click <a href=\"$url\" target=\"_blank\">here.</a><br><br>
        
        If you are not interested, just ignore or delete this email.

        Do <b><i>NOT</i></b> share this email to anyone. It may put your account in danger.<br><br>
        
        Link in this email, will expire in 5 minutes.");
    }
    
    

    public string $content;
    public string $subject;
    public User $user;
  
    public function __construct (User $user, string $subject, string $content) {
      $this->user = $user;
      $this->subject = $subject;
      $this->content = $content;
    }

    public function send () {
      Mail::message($this->user->email, $this->subject, $this->content);
    }
  }