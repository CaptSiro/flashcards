<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  
  <style>
    * {
      margin: 0;
      padding: 0;
      font-family: Helvetica, serif;
      box-sizing: border-box;
      color: #e5e5e5;
    }
    body {
      width: 100vw;
      height: 100vh;
      background: #370657;
    }
    a {
      color: #e5e5e5;
      text-decoration: unset;
    }
    a:hover {
      text-decoration: underline;
    }
    span {
      margin-left: 4px;
    }
    h3 {
      margin-bottom: 8px;
    }
    .navigation {
      width: 100%;
      padding: 32px;
      background: #0b0221;
    }
    .column {
      width: 100%;
      padding: 32px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .column a {
      text-indent: 16px;
    }
    .directories {
      background: #1e043d;
    }
  </style>
</head>
<body>
  <div class="navigation">
    <?php
      $index = count($GLOBALS["path"]) - 1;
      $current = ".";
    ?>
    <a href="./<?= str_repeat("../", $index == -1 ? 0 : $index) ?>">home</a><span>/</span>
    <?php foreach ($GLOBALS["path"] as $part) { ?>
      <a href="./<?= str_repeat("../", $index--) . $part ?>"><?= $part ?></a><span>/</span>
      <?php $current = $part; ?>
    <?php } ?>
  </div>
  
  <div class="column directories">
    <h3>Directories:</h3>
    <?php foreach ($GLOBALS["directories"] as $directory) { ?>
      <a href="<?= "$current/$directory" ?>"><?= $directory ?></a>
    <?php } ?>
  </div>
  
  <div class="column files">
    <h3>Files:</h3>
    <?php foreach ($GLOBALS["files"] as $file) { ?>
      <a href="<?= "$current/$file" ?>"><?= $file ?></a>
    <?php } ?>
  </div>
</body>
</html>