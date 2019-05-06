<?php
include "database.php"; // Establish database connection.
$sql = "SELECT * FROM highscores LIMIT 5"; // Select all high scores(only are 5).
$result = $conn->query($sql); // $result is data from database $conn, containing query specified in $sql variable above.
$arrayScores = [];
$num = 1;

// If there are more then 0 rows given back from database query above.
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $arrayScores[$num] = [];
        $arrayScores[$num]["scoreNum"] = $row['scoreNum'];
        $arrayScores[$num]["scoreName"] =  $row['scoreName'];
        $num++;
    }
}

echo json_encode($arrayScores);
?>