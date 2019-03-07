<?php
include('functions/db.php');
global $conn;
if ($_POST) {
    if ($_POST['confirm']) {
        $del_sql = 'delete from performances where import_error IS NOT NULL';
        $res1 = $conn->query($del_sql);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Upload Errors</title>
</head>
<body>
<?php
$sql = 'SELECT import_error, metric, numerator, denominator, quarter, year 
from performances, metrics 
where performances.metric_id = metrics.id 
AND import_error IS NOT NULL';
?>
<table border=1>
<tr><th>Error ID</th><th>Metric</th><th>Perf</th><th>Qtr</th><th>Year</th></tr>
<?php
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr><td>'.
            $row['import_error'].'</td><td>'.
            $row['metric'].'</td><td>'.
            $row['numerator'].'/'.
            $row['denominator'].'</td><td>'.
            $row['quarter'].'</td><td>'.
            $row['year'].'</td></tr>';
        }
    }
?>
</table><br/>
Clear Errors from Performance Table
<form action="upload_errors.php" method="POST">
Are You Sure?<select name="confirm">
<option value=0>No</option>
<option value=1>Yes</option>
<select><br/>
<input type="submit" value="Clear Errors" />
</body>
</html>