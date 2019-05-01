<?php
session_start(); // Open session.
include "inc/database.php"; // Include database connection.

// Function that will reset all variables for a soft game reset.
function MethodResetVars() {
    $_SESSION['time'] = 0;
    $_SESSION['diff'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['hasWon'] = 0;
    $_SESSION['content'] = 0;
    $_SESSION['numRand'] = 0;
    $_SESSION['numGuess'] = 0;
    $_SESSION['isPlaying'] = 0;
    $_SESSION['guessCount'] = 0;
    $_SESSION['timePassed'] = 0;
    $_SESSION['guessClose'] = "";
    $_SESSION['guessColor'] = "";
    $_SESSION['guessClose1'] = "";
    $_SESSION['guessClose2'] = "";
    $_SESSION['guessClose3'] = "";
    $_SESSION['guessClose4'] = "";
    $_SESSION['guessClose5'] = "";
    $_SESSION['guessColor1'] = "";
    $_SESSION['guessColor2'] = "";
    $_SESSION['guessColor3'] = "";
    $_SESSION['guessColor4'] = "";
    $_SESSION['guessColor5'] = "";
}

// This will perform a hard game (re)set, (re)setting all variables to default ones, also the variables that are constant throughout. This will be ran on page load and when user pressed hard reset button.
if (!isset($_SESSION['content']) || isset($_POST['btnHardReset'])) {
    MethodResetVars();
    $_SESSION['debug'] = 0;
    $_SESSION['showNum'] = 0;
    $_SESSION['numMax'] = 100;
    $_SESSION['maxScore'] = 0;
    $_SESSION['winCount'] = 0;
    $_SESSION['userName'] = "Anonymous";
}

// Logic for soft resetting game if user pressed stop game button or has won button.
if (isset($_POST['btnHome']) || isset($_POST['btnWon'])) {
    MethodResetVars();
}

// Logic to handle user guess.
if (isset($_POST['btnGuess']) && $_SESSION['hasWon'] == 0) {
    $_SESSION['numGuess'] = $_POST['numGuess']; // Get guessed number.
    $_SESSION['timePassed'] = time() - $_SESSION['time']; // Get difference in time between start of game and now.

    // Validate numGuess if it conforms to expected boundaries.
    if ($_SESSION['numGuess'] > 0 && $_SESSION['numGuess'] <= $_SESSION['numMax']) {
        $_SESSION['guessClose5'] = $_SESSION['guessClose4']; // Write previous guessed number to this number, too preserve previous guesses.
        $_SESSION['guessClose4'] = $_SESSION['guessClose3'];
        $_SESSION['guessClose3'] = $_SESSION['guessClose2'];
        $_SESSION['guessClose2'] = $_SESSION['guessClose1'];
        $_SESSION['guessClose1'] = $_SESSION['guessClose'];
        $_SESSION['guessColor5'] = $_SESSION['guessColor4']; // Do same for color.
        $_SESSION['guessColor4'] = $_SESSION['guessColor3'];
        $_SESSION['guessColor3'] = $_SESSION['guessColor2'];
        $_SESSION['guessColor2'] = $_SESSION['guessColor1'];
        $_SESSION['guessColor1'] = $_SESSION['guessColor'];
        $_SESSION['guessCount']++; // Add one to guess count.
        $_SESSION['diff'] = $_SESSION['numRand'] - $_SESSION['numGuess']; // Get the difference between guess and correct number.
        $tooHighOrLow = "<strong>too low.</strong>"; // Local variable that will be added to session guess var, to inform user whether guess was too high or low.

        // Check whether guess is negative and make it positive + update toohighorlow.
        if ($_SESSION['diff'] < 0) {
            $tooHighOrLow = "<strong>too high.</strong>";
            $_SESSION['diff'] = $_SESSION['diff'] * -1; // Turn diff into positive num.
        }

        // Huge else if that will check how close guess is to correct number and sets guessClose accordingly, If guess is correct, set corresponding values.
        if ($_SESSION['diff'] == 0) {
            $_SESSION['isPlaying'] = 0;
            $_SESSION['hasWon'] = 1;
            $_SESSION['winCount']++;
            $_SESSION['score'] = round($_SESSION['numMax'] * 1000 / $_SESSION['guessCount'] / $_SESSION['timePassed']); // Calculate score.
            $_SESSION['guessClose'] = "correct.";
            $_SESSION['guessColor'] = "text-5";

            $sql = "SELECT * FROM highscores LIMIT 5"; // Select all high scores(only are 5).
            $result = $conn->query($sql); // $result is data from database $conn, containing query specified in $sql variable above.
            
            // If there are more then 0 rows given back from database query above(always are, just safety precaution).
            if ($result->num_rows > 0) {
                $num1 = 1; // Counter.

                // While there are rows left in the database and $num is below 7(will be set to 999 if loop needs to stop), run through loop.
                while($row1 = $result->fetch_assoc()) {
                    // If the score is higher then current high score from database, perform logic to slot other scores downward and insert this score + name as new highscore for this slot.
                    if($_SESSION['score'] > $row1['scoreNum'] && $row1['scoreNum'] != "") {
                        $sql = "SELECT * FROM highscores LIMIT 5"; // Select all high scores(only are 5).
                        $result = $conn->query($sql); // $result is data from database $conn, containing query specified in $sql variable above.
                        $score = 0; // Placeholder.
                        $name = "nam"; // Placeholder.
                        $num2 = 1; // Start back form top score again.

                        // While loop that will go through every item in database and check whether it should be moved a slot down or not depending on new high score.
                        while ($row2 = $result->fetch_assoc()) {
                            // If this highscore is below new high score, update this highscore with high scores that were a slot above this one.
                            if ($num2 > $num1) {
                                $stmt = $conn->prepare("UPDATE highscores SET scoreName = ?, scoreNum = ? WHERE scoreId = $num2"); // Prepare sql statement, with sql protection, that will update current score.
                                $stmt->bind_Param('ss', $name, $score); // Bind the parameters to the command.
                                $stmt->execute(); // Execute command.
                            }

                            $score = $row2['scoreNum']; // Get score and name of current highscore for next cycle in while loop.
                            $name = $row2['scoreName'];
                            $num2++; // Count upwards.
                        }

                        $stmt2 = $conn->prepare("UPDATE highscores SET scoreName = ?, scoreNum = ? WHERE scoreId = $num1"); // Prepare sql statement, with sql protection, that will update current score.
                        $stmt2->bind_Param('ss', strip_tags($_SESSION['userName']), $_SESSION['score']); // Bind the parameters to the command.
                        $stmt2->execute(); // Execute command.
                        $num1 = 999; // Stop overarching while statement.
                    }

                    $num1++; // Count upwards.
                }
            }
        }
        // Diff < 5.
        else if ($_SESSION['diff'] < 5) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 5, " . $tooHighOrLow; // Contains guess(bold), the difference and too high or low(bold).
            $_SESSION['guessColor'] = "text-5"; // Bright green.
        }
        // Diff < 20.
        else if ($_SESSION['diff'] < 20) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 20, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-20"; // Green.
        }
        // Diff < 50.
        else if ($_SESSION['diff'] < 50) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 50, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-50"; // Light green.
        }
        // Diff < 100.
        else if ($_SESSION['diff'] < 100) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 100, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-100"; // Light orange.
        }
        // Diff < 250.
        else if ($_SESSION['diff'] < 250) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 250, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-250"; // Orange.
        }
        // Diff < 500.
        else if ($_SESSION['diff'] < 500) {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, < 500, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-500"; // Light red.
        }
        // Diff > 500.
        else {
            $_SESSION['guessClose'] = "<strong>" . $_SESSION['numGuess'] . "</strong>, > 500, " . $tooHighOrLow;
            $_SESSION['guessColor'] = "text-501"; // Bright red.
        }

        header("location: index.php"); // Reload page to clear post values.
        die(); // Kill page so logic stops here.
    }
}

// If numMax has been set and user has pressed start game button, perform game startup logic.
if (isset($_POST['btnStart']) && isset($_POST['numMax'])) {
    $_SESSION['numMax'] = $_POST['numMax']; // Get numMax into session variable.

    // Validate maxNum if it conforms to expected boundaries, then set all session variables to commence game.
    if ($_SESSION['numMax'] > 9 && $_SESSION['numMax'] < 1000) {
        $_SESSION['showNum'] = 0; // Reset cheat value.
        if (isset($_POST['showNum'])) { $_SESSION['showNum'] = 1; } // Get cheat value.
        $_SESSION['debug'] = 0; // Reset debug value.
        if (isset($_POST['debug'])) { $_SESSION['debug'] = 1; } // Get debug value.
        if (isset($_POST['username']) && $_POST['username'] != "") { $_SESSION['userName'] = $_POST['username']; } else { $_SESSION['userName'] = "Anonymous"; } // Get username, if it isn't set fill it with default anonymous.
        $_SESSION['numRand'] = rand(1, $_SESSION['numMax']); // Generate number between 0 and max.
        $_SESSION['hasWon'] = 0; // Keeps track if user has won game or not.
        $_SESSION['content'] = 1; // Shows game screen.
        $_SESSION['guessCount'] = 0; // Keeps track of number of guesses.
        $_SESSION['time'] = time(); // Get time as of game start.
        header("location: index.php"); // Reload page to clear post values.
        die(); // Kill page so logic stops here.
    }
    // Number doesn't conform to expected bounds, so notify user.
    else {
        echo "Looks like max number is outside of expected bounds!";
    }
} ?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"> <!-- Latest compiled and minified CSS Bootstrap -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> <!-- Add icon library -->
		<meta name="description" content="Guess The Number">
		<link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-2 mt-5">
                    <?php
                    // Checks to see if debug values should be displayed and echo's all session vars if it is.
                    if (isset($_SESSION['debug']) && $_SESSION['debug'] == 1) {
                        echo "<h3 class=\"text-primary text-right pt-5\">Debug values:</h3>";
                        echo "<p class=\"text-dark text-right font-stats\">Time: ", $_SESSION['time'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">Diff: ", $_SESSION['diff'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">Score: ", $_SESSION['score'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">HasWon: ", $_SESSION['hasWon'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">NumMax: ", $_SESSION['numMax'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">ShowNum: ", $_SESSION['showNum'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">Content: ", $_SESSION['content'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">NumRand: ", $_SESSION['numRand'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">winCount: ", $_SESSION['winCount'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">NumGuess: ", $_SESSION['numGuess'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">MaxScore: ", $_SESSION['maxScore'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">UserName: ", $_SESSION['userName'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">IsPlaying: ", $_SESSION['isPlaying'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessCount: ", $_SESSION['guessCount'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose: ", $_SESSION['guessClose'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">Time passed: ", $_SESSION['timePassed'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose1: ", $_SESSION['guessClose1'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose2: ", $_SESSION['guessClose2'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose3: ", $_SESSION['guessClose3'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose4: ", $_SESSION['guessClose4'], "</p>";
                        echo "<p class=\"text-dark text-right font-stats\">guessClose5: ", $_SESSION['guessClose5'], "</p>";
                    } ?>
                </div>

                <div class="col-md-6">
                    <?php if ($_SESSION['content'] == 0) { ?> <!-- Loads pre-game screen configurations. -->

                    <h1 class="text-dark text-center pb-5">Welcome to <strong>Guess The Number!</strong></h1>
                    <p class="text-dark">This is a simple game in which you will be guessing what the random number is.</p>
                    <p class="text-dark">But before we begin, you will have to enter some settings. Set the maximum value anywhere between 10 and 999, and enter your username, in case you achieve a new highscore.</p>

                    <form method="post">
                        <div class="form-group">
                            <div class="checkbox">
                                <label><input type="checkbox" name="showNum" <?php if ($_SESSION['showNum'] == 1) { echo "checked"; } ?> />Show random number(cheat)</label>
                            </div>

                            <div class="checkbox">
                                <label><input type="checkbox" name="debug" <?php if ($_SESSION['debug'] == 1) { echo "checked"; } ?> />Show debug info</label>
                            </div>

                            <p class="text-dark text-center float-left">Maximum number:</p>
                            <input type="text" class="ml-2 mr-2 float-left" id="inputNum" maxlength="3" value="<?php echo $_SESSION['numMax']; ?>" onchange="maxNumVerify(10);" onpaste="return false" onkeypress="return isNumberKey(event);" />
                            <input type="range" min="10" max="999" value="<?php echo $_SESSION['numMax']; ?>" class="slider float-left" id="maxNumSli" name="numMax" onload="addSliderEvent()" />
                            <div class="clearDiv"></div>

                            <p class="text-dark text-center float-left">Your username:</p>
                            <input type="text" class="float-left ml-2" name="username" maxlength="99" value="<?php echo $_SESSION['userName']; ?>" />
                            <div class="clearDiv"></div>

                            <input type="submit" class="btn btn-block btn-primary mx-auto d-block mt-3 mb-3 pt-2 pb-2" class="btnFont" id="btnStart" name="btnStart" value="Start game!" />

                            <input type="submit" class="btn btn-block btn-primary mx-auto d-block mt-3 mb-3 pt-2 pb-2" class="btnFont" id="btnHardReset" name="btnHardReset" value="Reset all" />
                        </div>
                    </form>

                    <?php
                        $sql = "SELECT * FROM highscores LIMIT 5"; // Select all high scores(only are 5).
                        $result = $conn->query($sql); // $result is data from database $conn, containing query specified in $sql variable above.

                        // If there are more then 0 rows given back from database query above.
                        if ($result->num_rows > 0) {
                            $num = 1; // Counter.
                            echo "<h4 class=\"text-primary\">Highscores:</h4>"; // Echo text to inform user what following scores indicate.

                            // While there are rows left in the database, run through loop.
                            while($row = $result->fetch_assoc()) {
                                echo "<h5 class=\"text-dark\">", $num,": <strong>", $row['scoreNum'], "</strong>, ", $row['scoreName'], "</h5>";
                                $num++; // Count upwards.
                            }
                        }
                    }
                    else if ($_SESSION['content'] == 1) { ?> <!-- Loads gamescreen itself. -->

                    <h1 class="text-dark text-center pb-5"><strong>Guess The Number!</strong></h1>

                    <form method="post">
                        <?php
                        // If user has won, echo elements that notify user of his win.
                        if ($_SESSION['hasWon'] == 1) {
                            echo "<h2 class=\"text-primary text-center\" id=\"hasWon\">You have won!</h2>"; // Notify user of win.

                            // If score is higher then maximum score, notify user of new high score.
                            if ($_SESSION['score'] > $_SESSION['maxScore']) {
                                echo "<h3 class=\"text-dark\">New high score! Your best score this session is: <strong>", $_SESSION['score'], "</strong></h3>"; // Notify user of new high score.
                                $_SESSION['maxScore'] = $_SESSION['score']; // Update score.
                            }

                            echo "<h6 class=\"text-dark\">Your score is: <strong>", $_SESSION['score'], "</strong>, calculated with max number <strong>", $_SESSION['numMax'], "</strong> in <strong> ", $_SESSION['timePassed'], " second(s) and ", $_SESSION['guessCount'], " guess(es).</strong></h6>"; // Notify user of this games score.
                            echo "<input type=\"submit\" class=\"btn btn-block btn-success mx-auto d-block mt-3 mb-3 pt-2 pb-2\" class=\"btnFont\" name=\"btnWon\" value=\"Yay!\" />"; // Button that returns to home.
                        } ?>

                        <?php if ($_SESSION['showNum'] == 1) { ?><p class="<?php if($_SESSION['hasWon'] == 1) { echo "text-muted"; } else { echo "text-dark"; }?>">The number is: <?php echo $_SESSION['numRand']; } ?>
                        <p class="<?php if($_SESSION['hasWon'] == 1) { echo "text-muted"; } else { echo "text-dark"; }?>"> The number is anywhere between 0 and <?php echo $_SESSION['numMax'] ?> </p>
                        <h5 class="<?php if($_SESSION['hasWon'] == 1) { echo "text-muted"; } else { echo "text-dark"; }?> text-center float-left">Guess the number:</h5>
                        <input type="text" class=" ml-2 mr-2 float-left" id="inputNum" name="numGuess" maxlength="3" value="<?php echo $_SESSION['numGuess'] ?>" onchange="numVerify(1);" onpaste="return false" onkeypress="return isNumberKey(event);" <?php if($_SESSION['hasWon'] == 1) { echo "disabled"; }?> />
                        <div class="clearDiv"></div>

                        <input type="submit" class="btn btn-block btn-primary mx-auto d-block mt-3 mb-3 pt-2 pb-2" class="btnFont" name="btnGuess" value="Guess!" <?php if($_SESSION['hasWon'] == 1) { echo "disabled"; }?> />

                        <input type="submit" class="btn btn-block btn-primary mx-auto d-block mt-3 mb-3 pt-2 pb-2" class="btnFont" name="btnHome" value="Stop game" <?php if($_SESSION['hasWon'] == 1) { echo "disabled"; }?> />
                    </form>
                    
                    <?php } ?>
                </div>

                <div class="col-md-2 mt-5">
                    <h3 class="text-primary pt-5">Statistics:</h3>
                    <p class="text-dark">Your username: <?php echo $_SESSION['userName']; ?></p>
                    <p class="text-dark">Total wins: <?php echo $_SESSION['winCount']; ?></p>
                    <p class="text-dark">High score: <?php echo $_SESSION['maxScore']; ?></p>
                    <?php
                    if ($_SESSION['content'] == 1) { echo "<p class=\"text-dark\" id=\"timePassed\">Time passed: ", $_SESSION['timePassed'], " s.</p>"; } // Echo time element if game has started.
                    if ($_SESSION['guessCount'] != 0) { echo "<p class=\"text-dark\">Total times guessed: ", $_SESSION['guessCount'], "</p>"; } // Echo guesscount if it is larger then 0.
                    if ($_SESSION['numGuess'] != 0) { echo "<p class=\"text-dark\">Your guess: ", $_SESSION['numGuess'], "</p>"; } // Echo guessed number if it isn't default value.
                    if ($_SESSION['guessClose'] != "") { echo "<h5 class=\"text-dark\">Your guess was:</h5><h5 class=\"", $_SESSION['guessColor'], " mb-3\">", $_SESSION['guessClose'], "</h5>"; } // Echo guessClose.
                    if ($_SESSION['diff'] != 0) { echo "<input type=\"range\" class=\"mb-5\" min=\"0\" max=\"", $_SESSION['numMax'], "\" value=\"", $_SESSION['numMax'] - $_SESSION['diff'], "\" disabled>"; } // Diff.
                    if ($_SESSION['guessClose1'] != "") { echo "<p class=\"", $_SESSION['guessColor1'], " font-stats\">2nd: ", $_SESSION['guessClose1'], "</p>"; } // Echo previous guesses if they are set.
                    if ($_SESSION['guessClose2'] != "") { echo "<p class=\"", $_SESSION['guessColor2'], " font-stats\">3rd: ", $_SESSION['guessClose2'], "</p>"; }
                    if ($_SESSION['guessClose3'] != "") { echo "<p class=\"", $_SESSION['guessColor3'], " font-stats\">4th: ", $_SESSION['guessClose3'], "</p>"; }
                    if ($_SESSION['guessClose4'] != "") { echo "<p class=\"", $_SESSION['guessColor4'], " font-stats\">5th: ", $_SESSION['guessClose4'], "</p>"; }
                    if ($_SESSION['guessClose5'] != "") { echo "<p class=\"", $_SESSION['guessColor5'], " font-stats\">6th: ", $_SESSION['guessClose5'], "</p>"; }

                    $sql = "SELECT * FROM highscores LIMIT 5"; // Select all high scores(only are 5).
                    $result = $conn->query($sql); // $result is data from database $conn, containing query specified in $sql variable above.

                    // If there are more then 0 rows given back from database query above.
                    if ($result->num_rows > 0) {
                        $num = 1; // Counter.
                        echo "<h5 class=\"text-primary\">Highscores:</h5>"; // Echo text to inform user what following scores indicate.

                        // While there are rows left in the database, run through loop.
                        while($row = $result->fetch_assoc()) {
                            echo "<h6 class=\"text-dark\">", $num,": <strong>", $row['scoreNum'], "</strong>, ", $row['scoreName'], "</h6>";
                            $num++; // Count upwards.
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php include "inc/js-import.php" ?> <!-- Loads javascript related to bootstrap. -->
        <script type="text/javascript" src="js/main.js"></script> <!-- Javascript. -->
    </body>
</html>