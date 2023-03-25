<?php
  
  use OakBase\Database;
  use OakBase\PrimitiveParam;
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
//  require_once __DIR__ . "/../lib/susmail/susmail.php";
  require_once __DIR__ . "/../lib/phpmailer/phpmailer.php";
  require_once __DIR__ . "/../lib/regexes.php";
  
  require_once __DIR__ . "/../models/User.php";
  require_once __DIR__ . "/../models/InvitationLink.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  
  $auth_router = new Router();
  
  
  
  $auth_router->get("/", [
    Middleware::requireToBeLoggedOut(Middleware::RESPONSE_REDIRECT, Response::createRedirectURL("/")),
    function (Request $request, Response $response) {
      $response->render("auth");
    }
  ]);
  
  
  
  $auth_router->post("/", [
    Middleware::requireToBeLoggedOut(Middleware::RESPONSE_JSON, Response::createRedirectURL("/")),
    function (Request $request, Response $response) {
      $email = $request->body->get("email");
      
      if (preg_match(REGEX_EMAIL, $email) === false || $email === "") {
        $response->fail(new Exc("Not a valid email."));
      }
    
      $user = User::get_by_email($email)
        ->forwardFailure($response)
        ->getSuccess();
      
      if ($user === false) {
        User::insert($email);
        $user = User::get_by_email($email)
          ->forwardFailure($response)
          ->getSuccess();
      }
      
      $arg = InvitationLink::arg_gen()
        ->forwardFailure($response)
        ->getSuccess();
      
      InvitationLink::insert($arg, $user);
      
      $mail = new PHPMailer(true);
      
      $mail->IsSMTP();
      $mail->Host = "smtp.gmail.com";
      $mail->SMTPAuth = true;
      $mail->Username = 'captsiro@gmail.com';
      $mail->Password = 'wonsnuejsmdwjotb';
      $mail->SMTPSecure = 'ssl';
      $mail->Port = 465;
  
      $mail->setFrom('captsiro@gmail.com');
      $mail->addAddress($user->email);
      $mail->addReplyTo('info@example.com', 'Information');
  
      $mail->isHTML();
      $mail->Subject = 'Flash Cards login';
      $url = Response::createRedirectURL("/auth/login/?arg=$arg");
      $mail->Body = "Hello,<br><br>
        
        To log in to Flash Cards click <a href=\"$url\" target=\"_blank\">here.</a><br><br>
        
        If you are not interested, just ignore or delete this email.

        Do <b><i>NOT</i></b> share this email to anyone. It may put your account in danger.<br><br>
        
        Link in this email, will expire in 5 minutes.";
      $mail->AltBody = "Hello,\n\nTo log in to Flash Cards go to:\n$url\n\nIf you are not interested, just ignore or delete this email.\n\nDo NOT share this email to anyone. It may put your account in danger.\n\nLink in this email, will expire in 5 minutes.";
      
      $mail->send();
      
      
      
      $response->json(["message" => "Link has been sent."]);
    }
  ]);
  
  
  
  $auth_router->delete("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $request->session->unset("user");
      $response->json(["next" => Response::createRedirectURL("/auth")]);
    }
  ]);
  
  
  
  $auth_router->get("/login", [function (Request $request, Response $response) {
    $link = InvitationLink::get_by_arg(
      $request->query->get("arg")
    )
      ->forwardFailure($response)
      ->getSuccess();
    
    if ($link === false) {
      $response->render("error", ["message" => "Link has been used or it has expired. (Expiration time: 5 minutes)"]);
    }
    
    $user = User::get_by_id((int) $link->users_id)
      ->forwardFailure($response)
      ->getSuccess();
    
    if ($user === false) {
      $response->render("error", ["message" => "Lost link... No account found for this link."]);
    }
    
    $request->session->set("user", $user);
    
    InvitationLink::delete_for_user($user);
    
    $response->redirect("/");
  }]);
  
  
  
  $auth_router->get("/stay-logged-in", [function (Request $request, Response $response) {
    setcookie("PHPSESSID", $request->cookies->get("PHPSESSID"), time() + 86_400 * 30, "/");
    $response->json(["message" => "Done"]);
  }]);
  
  
  
  return $auth_router;