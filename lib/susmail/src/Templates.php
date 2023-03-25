<?php

  require_once __DIR__ . "/Mail.php";

  class MailTemplate {
    static function login (User $user, $url): MailTemplate {
      return new MailTemplate($user, "", "");
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