<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/main.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/login-register.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/forms.css">
  
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/tungsten.js"></script>
  <script>
    AJAX.DOMAIN_HOME = "<?= $GLOBALS["__HOME__"] ?>";
  </script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/forms.js" defer></script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/auth.js" defer></script>
</head>
<body class="center">
  <div class="form login">
    <div class="wrapper">
      <label for="l-email">Email:</label>
      <input type="text" name="l-email" id="l-email">
    </div>
  
    <div class="wrapper">
      <p class="blockquote note">This site uses email only authentication. Submit your email and log in via invitation link that will be sent to your address. If you don't have an account, it will be created automatically.</p>
    </div>
  
    <div class="wrapper">
      <button class="submit" type="submit">Submit</button>
    </div>
    
    <div class="wrapper">
      <p class="blockquote error"></p>
    </div>
  </div>
  
  <div class="form confirmation hide">
    <div class="wrapper">
      <p class="blockquote note">Email with invitation link has been sent.</p>
    </div>
  </div>
</body>
</html>