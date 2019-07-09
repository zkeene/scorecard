<?php
include('functions/db.php');
global $conn;

if ($_POST) {
    if ($_POST['confirm']) {
        $metric_id = $_POST['metric_id'];
        $year = $_POST['year'];
        $quarter = $_POST['quarter'];

        $allowed_sql = "select * from period_locks where year=$year and quarter=$quarter";

        if ($conn->query($allowed_sql)->num_rows==0) {
            $sql = "delete from performances where metric_id=$metric_id and year=$year and quarter=$quarter";

            if ($conn->query($sql)) {
                echo 'Successfully deleted performance data';
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo 'Period Locked: Unable to delete performance';
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
    Quarter: <select name="quarter">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select><br/>
    Are you sure you want to delete all performance data for this metric in the selected period?:
    <select name="confirm">
        <option value="0" default>No</option>
        <option value="1">Yes</option>
    </select><br/>
    <input type="submit" value="Delete Performance" />
</form>
</body>
</html>