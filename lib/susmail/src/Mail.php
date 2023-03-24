<?php

  class Mail {
    const FROM = "noreply@nocoma.com";

    public static function message ($to, $subject, $msg) {
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      $headers .= 'From: <' . self::FROM . '>' . "\r\n";
      $headers .= 'Reply-To: <' . self::FROM . '>' . "\r\n";

      mail("<$to>", $subject, $msg, $headers);
    }
  }