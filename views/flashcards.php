<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/main.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/flashcards.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/modal.css">
  <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/forms.css">
  
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/tungsten.js"></script>
  <script>
    AJAX.DOMAIN_HOME = "<?= $GLOBALS["__HOME__"] ?>";
  </script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/modal.js" defer></script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/forms.js" defer></script>
  <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/flashcards.js" defer></script>
  
  <title>Flash decks</title>
</head>
<body>
  <div class="modals">
    <div class="window form" id="create-deck">
      <div class="wrapper">
        <label for="deck-name">Deck name:</label>
        <input type="text" name="deck-name" id="deck-name">
      </div>
    
      <div class="divider"></div>
  
      <div class="wrapper sideways-end">
        <button class="cancel-modal">Cancel</button>
        <button class="submit" type="submit">Create</button>
      </div>
      
      <div class="wrapper">
        <p class="blockquote error error-modal"></p>
      </div>
    </div>
  </div>
  
  <nav>
    <div class="breadcrumbs">
      <button class="button-like"><span class="mono">&lt;</span></button>
      <button class="button-like">Decks</button>
      <button class="button-like">Stacks</button>
      <button class="button-like">Cards</button>
    </div>
    <div>
      <span>Deck: <i><b id="current-deck"></b></i></span>
    </div>
    <button class="logout button-like">Logout</button>
  </nav>
  
  <main>
    <button class="add button-like"><span class="mono">+</span></button>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
    <div class="deck">
      <h3>Something</h3>
    </div>
  </main>
</body>
</html>