// Function which handles keypress and checks if it should be handled or not.
function isNumberKey(e) {
    var key = getKeyEvent(e); // Get the keycode, with added compatibility for IE and other browsers.

    if (key < 32 || (key > 47 && key < 58)) {
        return true;
    } else {
        return false;
    } // If the key is a functional key(backspace, tab etc.), or is a number(48-57), handle keypress, otherwise, don't handle keypress.
}

// Event which ensures compatability between different browsers for keyevent.
function getKeyEvent(e) {
    var keyNum;
    if (window.event) {
        keyNum = e.keyCode
    } else if (e.which) {
        keyNum = e.which
    }
    return keyNum; // Checks to see which browser it is on and performs logic to return keyNum for that browser, for compat.
}

// Verifies input on maxNum textbox, makes sure value is above 10 and string length doesn't exceed 3 characters, and update slider only if it conforms to 10 - 999 range.
function maxNumVerify(minInputNum) {
    var inputNum = document.getElementById("inputNum");
    var num = inputNum.value; // Define element and get string to variable num..

    if (num == "") {
        inputNum.value = minInputNum;
    } // If field is empty, fill it with minimum expected input.
    while (num.length > 3) {
        num = maxNum.substring(0, num.length - 1);
        inputNum.value = num;
    } // while maxNum exceeds 3 characters, remove last digit from string.

    var intNum = parseInt(inputNum.value);
    if (intNum < minInputNum) {
        inputNum.value = minInputNum;
    } else if (intNum > 999) {
        inputNum.value = 999;
    } // If num is exceeds boundary, fill it with boundary maximums.
    var intNum = parseInt(inputNum.value);
    if (intNum > 9 && intNum < 1000 && minInputNum == 10) {
        document.getElementById("maxNumSli").value = intNum;
    } // Update slider if is present and num is conforms to boundary.
}

$(document).ready(function () {
    // If slider is present on page, add an eventlistener that will update the value in maxNum text input to slider value every time value changes.
    if (document.getElementById("maxNumSli") != null) {
        document.getElementById("maxNumSli").addEventListener("input", function () {
            document.getElementById("inputNum").value = document.getElementById("maxNumSli").value; // Update value in textbox to slider value.
        });
    }

    // If the timePassed element is on page AND haswon element is NOT on page, so if game is in progress and user hasn't won, perform logic to read time and start function that will count upwards.
    if (document.getElementById("timePassed") != null && document.getElementById("hasWon") == null) {
        var timePassed = document.getElementById("timePassed").innerHTML; // Get current string to local var.
        var timePassed = timePassed.split(" "); // Split string at space.
        var timePassed = timePassed[2]; // Take the second part of array, which contains the time in seconds.
        setTimeout(function () {
            updateTime();
        }, 1000); // Run function that count time upwards every second.
    }

    // This function will update the time on game page, purely cosmetic as php will calculate the time game has lasted on its own.
    function updateTime() {
        timePassed++; // Count upwards.
        document.getElementById("timePassed").innerHTML = "Time passed: " + timePassed + " s."; // Update label with new time.
        setTimeout(function () {
            updateTime();
        }, 1000); // Run function that updates time every second.
        showHint(); // Update scores.
    }

    function showHint() {
        $.get('inc/getHighScores.php', function (scores) {
            console.log(scores);
            $("#highscore1").html("1: <strong>" + scores["1"].scoreNum + "</strong>, " + scores["1"].scoreName);
            document.getElementById("highscore2").innerHTML = "2: <strong>" + scores["2"].scoreNum + "</strong>, " + scores["2"].scoreName;
            document.getElementById("highscore3").innerHTML = "3: <strong>" + scores["3"].scoreNum + "</strong>, " + scores["3"].scoreName;
            document.getElementById("highscore4").innerHTML = "4: <strong>" + scores["4"].scoreNum + "</strong>, " + scores["4"].scoreName;
            document.getElementById("highscore5").innerHTML = "5: <strong>" + scores["5"].scoreNum + "</strong>, " + scores["5"].scoreName;
        }, 'json');
    }
});