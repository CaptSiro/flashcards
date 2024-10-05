<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <title>Flash Cards</title>
</head>
<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="display-none">
        <defs>
            <g id="icon-options">
                <circle fill="currentColor" cx="250" cy="85.9" r="55.1"/>
                <circle fill="currentColor" cx="250" cy="245.6" r="55.1"/>
                <circle fill="currentColor" cx="250" cy="405.2" r="55.1"/>
            </g>
        </defs>
    </svg>


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


        <div class="window form" id="create-stack">
            <div class="wrapper">
                <label for="stack-name">Stack name:</label>
                <input type="text" name="stack-name" id="stack-name">
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


        <div class="window form" id="create-card">
            <div id="question">
                <div class="wrapper">
                    <label for="card-question">Question:</label>
                    <textarea name="card-question" id="card-question" cols="30" rows="5"></textarea>
                </div>

                <div class="wrapper">
                    <label for="card-question-images">Or upload question images:</label>
                    <input type="file" name="card-question-images" id="card-question-images" accept="image/*" multiple>
                </div>

                <div class="divider"></div>

                <div class="wrapper sideways-end">
                    <button class="cancel-modal">Cancel</button>
                    <button class="next">Next</button>
                </div>
            </div>

            <div id="answer" class="display-none">
                <div class="wrapper">
                    <label for="card-answer">Answer:</label>
                    <textarea name="card-answer" id="card-answer" cols="30" rows="5"></textarea>
                </div>

                <div class="wrapper">
                    <label for="card-answer-images">Or upload answer images:</label>
                    <input type="file" name="card-answer-images" id="card-answer-images" accept="image/*" multiple>
                </div>

                <div class="divider"></div>

                <div class="wrapper sideways-end">
                    <button class="previous">Previous</button>
                    <button class="submit" type="submit">Create</button>
                </div>
            </div>

            <div class="wrapper">
                <p class="blockquote error error-modal"></p>
            </div>
        </div>


        <div class="window form" id="manage-privileges">
            <div class="wrapper">
                <p>Your team for deck: <span class="important" id="team-deck-name"></span></p>
            </div>

            <div id="team"></div>

            <div class="wrapper sideways-end">
                <button class="cancel-modal">Ok</button>
            </div>

            <div class="wrapper">
                <p class="blockquote error error-modal"></p>
            </div>
        </div>


        <div class="window form" id="share">
            <div class="wrapper">
                <label for="share-username">Enter username:</label>
                <input type="text" name="share-username" id="share-username">
            </div>

            <div class="wrapper">
                <label for="share-privilege">Select their privileges:</label>
                <select name="share-privilege" id="share-privilege">
                    <option value="1" selected>Editor</option>
                    <option value="2">Guest</option>
                </select>
            </div>

            <div class="divider"></div>

            <div class="wrapper sideways-end">
                <button class="cancel-modal">Cancel</button>
                <button class="submit" type="submit">Share</button>
            </div>

            <div class="wrapper">
                <p class="blockquote error error-modal"></p>
            </div>
        </div>
    </div>

    <nav>
        <button class="button-like back">
            <span class="mono">&lt;</span>
        </button>

        <div class="state-label">
            <span>Deck:&nbsp;<i><b id="current-deck"></b></i></span>
        </div>

        <button class="logout button-like">Logout</button>
    </nav>

    <!-- Root element for dynamically loaded content -->
    <main></main>
</body>
</html>