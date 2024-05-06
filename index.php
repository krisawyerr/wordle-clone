<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wordle</title>
    <link rel="icon" type="image/png" href="./logo.png">
    <style>
        body {
            background-color: #121213;
            color: #F8F8F8;
            font-family: monospace;
        }
        .mainGrid {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .guess, .keyboard {
            display: flex;
            justify-content: center;
        }
        .guess div {
            height: 60px;
            width: 60px;
            margin: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
        }
        .green {
            background-color: #538D4E;
            border: 2px solid #538D4E;
        }
        .yellow {
            background-color: #B59F3B;
            border: 2px solid #B59F3B;
        }
        .gray {
            background-color: #3A3A3C;
            border: 2px solid #3A3A3C;
        }
        .white {
            background-color: transparent;
            border: 2px solid #3A3A3C;
        }
        #word-input {
            width: 345px;
            height: 50px;
            margin-bottom: 10px;
            padding: 5px;
            font-size: 40px;
            text-align: center;
            letter-spacing: 15px;
        }
        button {
            padding: 13px;
            margin: 5px;
            border: 2px solid #818384;
            background-color: #818384;
            color: #F8F8F8;
            font-weight: 900;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            border-radius: 5px;
        }
        @media screen and (max-width: 563px) {
            button {
                padding: 7px;
                margin: 2px;
            }
        }
    </style>
</head>
<body class="mainGrid">
    <h1>Wordle</h1>
    <form action='index.php' method='post'>
        <input type='text' id="word-input" name='word' readonly>
        <input type='submit' name='submit' value='submit' id="submit" style="display:none;">      
    </form>

    <script>
        let wordInput = document.getElementById('word-input');
        let submitButton = document.getElementById('submit');

        function addToWord(letter) {
            wordInput.value += letter;
        }

        function deleteLastLetter() {
            wordInput.value = wordInput.value.slice(0, -1);
        }

        function reset() {
            wordInput.value = '';
        }

        function enterWord() {
            submitButton.click();
        }
    </script>
</body>
</html>

<?php 
    session_start();

    if (!isset($_SESSION['row']) || isset($_POST['reset'])) {
        $_SESSION['row'] = 0;
        $_SESSION['randomWord'] = strtoupper(file_get_contents('https://random-word-api.herokuapp.com/word?length=5'));
        $_SESSION['randomWord'] = substr($_SESSION['randomWord'], 2, 5);
        $_SESSION['guesses'] = array_fill(0, 6, array_fill(0, 5, ''));
        $_SESSION['color'] = array_fill(0, 6, array_fill(0, 5, 'white'));
    }

    if(isset($_POST['submit'])) {
        $word = trim($_POST['word']);

        if (strlen($word) != 5 || !ctype_alpha($word)) {
            echo "Make sure word is only 5 letters and contains only alphabets.";
        } else {
            $word = str_split(strtoupper($word));

            for ($i = 0; $i < 5; $i++) {
                if ($_SESSION['randomWord'][$i] == $word[$i]) {
                    $_SESSION['color'][$_SESSION['row']][$i] = 'green';
                    $_SESSION['guesses'][$_SESSION['row']][$i] = $word[$i];
                } else if (in_array($word[$i], str_split($_SESSION['randomWord']))) {
                    $_SESSION['color'][$_SESSION['row']][$i] = 'yellow';
                    $_SESSION['guesses'][$_SESSION['row']][$i] = $word[$i]; 
                } else {
                    $_SESSION['color'][$_SESSION['row']][$i] = 'gray';
                    $_SESSION['guesses'][$_SESSION['row']][$i] = $word[$i];
                }
            }

            $_SESSION['row']++;
        }
    }

    echo "<div>";
    for ($i = 0; $i < 6; $i++) {
        echo "
        <div class='guess'>
            <div class='{$_SESSION['color'][$i][0]}'>{$_SESSION['guesses'][$i][0]}</div>
            <div class='{$_SESSION['color'][$i][1]}'>{$_SESSION['guesses'][$i][1]}</div>
            <div class='{$_SESSION['color'][$i][2]}'>{$_SESSION['guesses'][$i][2]}</div>
            <div class='{$_SESSION['color'][$i][3]}'>{$_SESSION['guesses'][$i][3]}</div>
            <div class='{$_SESSION['color'][$i][4]}'>{$_SESSION['guesses'][$i][4]}</div>
        </div>
        ";
    }
    echo "</div><br><br>";

    if(isset($_POST['submit']))  {
        if ($_POST['word'] == $_SESSION['randomWord']) {
            echo "<h1>You Won! The word was {$_SESSION['randomWord']}</h1>";
        }

        if ($_SESSION['row'] >= 6) {
            echo "<h1>You Lost! The word was {$_SESSION['randomWord']}</h1>";
        }        
    }

    $keyboard = array(
        array('Q','W','E','R','T','Y','U','I','O','P'), 
        array('A','S','D','F','G','H','J','K','L'), 
        array('Z','X','C','V','B','N','M'),
        array('enter','delete','reset'),
    );

    if (isset($_POST['submit']) && ($_POST['word'] == $_SESSION['randomWord'] || $_SESSION['row'] == 6)) {
        echo "<form action='index.php' method='post'>";
        foreach ($keyboard as $row) {
            echo "<div class='keyboard'>";
            foreach ($row as $key) {
                if ($key == "enter") {
                    echo "<button type='button' onclick='enterWord()' disabled>Enter</button>";
                } elseif ($key == "delete") {
                    echo "<button type='button' onclick='deleteLastLetter()' disabled>Delete</button>";
                } elseif ($key == "reset") {
                    echo "<button type='submit' name='reset'>Reset</button>";
                } else {
                    echo "<button type='button' onclick='addToWord(\"$key\")' disabled>$key</button>";
                }
            }
            echo "</div>";
        }
        echo "</form>";       
    } else {
        echo "<form action='index.php' method='post'>";
        foreach ($keyboard as $row) {
            echo "<div class='keyboard'>";
            foreach ($row as $key) {
                if ($key == "enter") {
                    echo "<button type='button' onclick='enterWord()'>Enter</button>";
                } elseif ($key == "delete") {
                    echo "<button type='button' onclick='deleteLastLetter()'>Delete</button>";
                } elseif ($key == "reset") {
                    echo "<button type='submit' name='reset'>Reset</button>";
                } else {
                    echo "<button type='button' onclick='addToWord(\"$key\")'>$key</button>";
                }
            }
            echo "</div>";
        }
        echo "</form>";
    }
?>
