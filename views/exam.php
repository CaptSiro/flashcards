<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Test - Flash Cards</title>
  
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/main.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/exam.css">
  
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/tungsten.js"></script>
  <script>
    AJAX.DOMAIN_HOME = "<?= $GLOBALS["__HOME__"] ?>";
    const stack_id = <?= $GLOBALS["stack_id"] ?>;
    const cards = shuffle_mut(JSON.parse(`<?= str_replace("\\", "\\\\", json_encode($GLOBALS["cards"])) ?>`));
  </script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/exam.js" defer></script>
</head>
<body>
  <nav>
    <button class="button-like back"><span class="mono">&lt;</span></button>
    <div class="state-label">
      <span>Stack: <i><b id="current-deck"><?= $GLOBALS["stack_name"] ?></b></i></span>
    </div>
    <span class="void"></span>
  </nav>

  
  <div class="exam">
    <div class="progress-bar"></div>
    
    <div class="card"></div>
  
    <section class="controls">
      <div class="initial">
        <label for="answer-input" class="display-none"></label>
        <input type="text" name="answer-input" id="answer-input">
        <button class="button-like next">Next</button>
      </div>
    
      <div class="text-answer display-none">
        <button class="button-like next">Next</button>
      </div>
    
      <div class="thought-answer display-none row">
        <button class="button-like" id="answer-right">Right</button>
        <button class="button-like" id="answer-wrong">Wrong</button>
      </div>
    </section>
  </div>
  
  
  <div class="results display-none">
    <div class="stats">
      <span class="percentage">69%</span>
      <span class="fraction"><span class="right">420</span>/<span class="wrong">1337</span></span>
    </div>
    
    <canvas class="graph"></canvas>
  </div>
  
  
  <div class="no-cards display-none">
    <p class="blockquote note">There are no cards in this stack.</p>
  </div>
</body>
</html>