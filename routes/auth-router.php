<?php
  
  use OakBase\Database;
  use OakBase\PrimitiveParam;
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  require_once __DIR__ . "/../lib/susmail/susmail.php";
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
    
      $user = User::by_email(param($email))
        ->forwardFailure($response)
        ->getSuccess();
      
      if ($user === false) {
        User::insert(param($email));
        $user = User::by_email(param($email))
          ->forwardFailure($response)
          ->getSuccess();
      }
      
      $arg = InvitationLink::arg_gen()
        ->forwardFailure($response)
        ->getSuccess();
      
      InvitationLink::insert(param($arg), param($user->id));
      
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
      $mail->Body = "";
      $mail->AltBody = "Hello,\n\nTo log in to Flash Cards go to:\n$url\n\nIf you are not interested, just ignore or delete this email.\n\nDo NOT share this email to anyone. It may put your account in danger.\n\nLink in this email, will expire in 5 minutes.";

//      $mail->send();

//      MailTemplate::login($user, $url)->send();
      
      $response->json([
        "message" => "Link has been sent.",
        "url" => $url // todo remove or security leak :)
      ]);
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
    $link = InvitationLink::by_arg(
      param($request->query->get("arg"))
    )
      ->forwardFailure($response)
      ->getSuccess();
    
    if ($link === false) {
      $response->render("error", ["message" => "Link has been used or it has expired. (Expiration time: 5 minutes)"]);
    }
    
    $user = User::by_id(param($link->users_id))
      ->forwardFailure($response)
      ->getSuccess();
    
    if ($user === false) {
      $response->render("error", ["message" => "Lost link... No account found for this link."]);
    }
    
    $request->session->set("user", $user);
    
    InvitationLink::delete_for_user(param($user->id));
    
    $response->redirect("/");
  }]);
  
  
  
  $auth_router->get("/stay-logged-in", [function (Request $request, Response $response) {
    setcookie("PHPSESSID", $request->cookies->get("PHPSESSID"), time() + 86_400 * 30, "/");
    $response->json(["message" => "Done"]);
  }]);
  
  
  
  return $auth_router;