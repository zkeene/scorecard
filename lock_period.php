<?php
include('functions/db.php');
include('functions/methods.php');
global $conn;

if ($_POST) {
    $lock_year = $_POST['year'];
    $lock_quarter = $_POST['quarter'];

    $sql = "insert into period_locks (
        year,
        quarter
    )
    VALUES (
        $lock_year,
        $lock_quarter
    )";

    if ($conn->query($sql)) {
        echo 'Success locked selected period';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Lock Performance Data</title>
</head>
<body>
<form enctype="multipart/form-data" action="lock_period.php" method="POST">
    Year: <input type="text" name="year" size="4" /><br/>
    Quarter: <select name="quarter">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select><br/>
    Are you sure you want to lock all performance data in the selected period?:
    <select name="confirm">
        <option value="0" default>No</option>
        <option value="1">Yes</option>
    </select><br/>
    <input type="submit" value="Lock Data" />
</form>
</body>
</html>