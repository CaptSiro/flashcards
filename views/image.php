<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="<?= $GLOBALS["__HOME__"] ?>/public/css/main.css">

    <script src="<?= $GLOBALS["__HOME__"] ?>/public/js/tungsten.js"></script>
    <script>
        AJAX.DOMAIN_HOME = "<?= $GLOBALS["__HOME__"] ?>";
    </script>
</head>
<body>
    <div>
        <input type="file" name="question_images[]" accept="image/*">
        <input type="file" name="question_images[]">
        <button id="s">Sub</button>
    </div>

    <script>
        $("#s").addEventListener("click", evt => {
            const fd = new FormData();
            for (const input of $$("[name='question_images[]']")) {
                for (const file of input.files) {
                    fd.append("question_images[]", file);
                }
            }

            AJAX.post("/card", JSONHandlerSync(console.log), { body: fd });
        });
    </script>
</body>
</html>