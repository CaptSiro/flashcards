<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Login - Flash Cards</title>
  
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
  <div class="form login" id="login">
    <div class="wrapper">
      <label for="l-username">Username:</label>
      <input type="text" name="l-username" id="l-username">
    </div>
    
    <div class="wrapper">
      <label for="l-password">Password:</label>
      <input type="password" name="l-password" id="l-password">
    </div>
    
    <div class="divider"></div>
    
    <div class="wrapper">
      <button class="submit" type="submit">Login</button>
    </div>
    
    <div class="wrapper">
      <p class="blockquote error"></p>
    </div>
    
    <div class="hline"></div>
    
    <div class="wrapper">
      <p>Don't have an account? <button class="link" link-to="register">Sign up.</button></p>
    </div>
  
    <div class="wrapper">
      <p class="blockquote error error-modal"></p>
    </div>
  </div>
  
  
  
  <div class="form register hide" id="register">
    <div class="wrapper">
      <label for="r-username">Username:</label>
      <input type="text" name="r-username" id="r-username">
    </div>
  
    <div class="wrapper">
      <label for="r-password">Password:</label>
      <input type="password" name="r-password" id="r-password">
    </div>
  
    <div class="wrapper">
      <label for="r-password-again">Password again:</label>
      <input type="password" name="r-password-again" id="r-password-again">
    </div>
    
    <div class="divider"></div>
    
    <div class="wrapper">
      <button class="submit" type="submit">Register</button>
      <p class="blockquote error"></p>
    </div>
    
    <div class="hline"></div>
    
    <div class="wrapper">
      <p>Already have an account? <button class="link" link-to="login">Login.</button></p>
    </div>
  
    <div class="wrapper">
      <p class="blockquote error error-modal"></p>
    </div>
  </div>
</body>
</html>