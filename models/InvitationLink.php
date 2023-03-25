<?php
  
  use OakBase\Database;
  use OakBase\MixedIndexingException;
  use OakBase\PrimitiveParam;

  class InvitationLink {
    public $id, $arg, $users_id, $expires;
    
    
    
    static function insert (string $arg, User $user): Result {
      $arg = new PrimitiveParam($arg);
      $user_id = new PrimitiveParam($user->id);
      $expires = new PrimitiveParam(time() + 60 * 5);
  
      return success(Database::get()->statement(
        "INSERT INTO invitation_links (arg, users_id, expires)
          VALUE ($arg, $user_id, $expires)"
      ));
    }
    
    
    
    static function get_by_arg (string $arg): Result {
      self::purge_old();
      
      $arg = new PrimitiveParam($arg);
  
      return success(Database::get()->fetch(
        "SELECT id, arg, expires, users_id
          FROM invitation_links
          WHERE arg = $arg
            AND expires > UNIX_TIMESTAMP()"
      ));
    }
  
  
  
    static function delete_for_user (User $user): Result {
      self::purge_old();
    
      $user_id = new PrimitiveParam($user->id);
    
      return success(Database::get()->statement(
        "DELETE FROM invitation_links
          WHERE users_id = $user_id"
      ));
    }
    
    
    
    static function purge_old () {
      Database::get()->statement("DELETE FROM invitation_links WHERE expires < UNIX_TIMESTAMP()");
    }
    
    
    
    const ARG_GEN_CHARSET = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789_-";
    const ARG_GEN_MAX_TRIES = 4_096;
    
    static function arg_gen (): Result {
      $arg = "";
      $try = 0;
      
      do {
        $try++;
        
        for ($i = 0; $i < 64; $i++) {
          $arg .= self::ARG_GEN_CHARSET[random_int(0, 63)];
        }
        
        $existing_arg = self::get_by_arg($arg);
        if ($existing_arg->isSuccess() && $existing_arg->getSuccess() === false) {
          return success($arg);
        }
      } while ($try < self::ARG_GEN_MAX_TRIES);
      
      return fail(new Exc("Argument generation timeout."));
    }
  }