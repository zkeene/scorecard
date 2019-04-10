<?php
include('functions/db.php');
global $conn;

if ($_POST) {
    if ($_POST['confirm']) {
        $metric_id = $_POST['metric_id'];
        $year = $_POST['year'];
        $sql = "delete from performances where metric_id=$metric_id and year=$year";

        if ($conn->query($sql)) {
            echo 'Successfully deleted performance data';
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Delete Performance Data</title>
</head>
<body>
<form enctype="multipart/form-data" action="delete_performance.php" method="POST">

    Metric: <select name="metric_id">
    <?php
       
        $sql_met = 'SELECT id, metric FROM metrics';
        $result = $conn->query($sql_met);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="'.$row['id'].'">'.$row['metric'].'</option>';
            }
        }
    
    ?>
    </select><br/>
    Year: <input type="text" name="year" size="4" /><br/>
    Are you sure you want to delete all performance data for this metric in this year?:
    <select name="confirm">
        <option value="0" default>No</option>
        <option value="1">Yes</option>
    </select><br/>
    <input type="submit" value="Delete Performance" />
</form>
</body>
</html>