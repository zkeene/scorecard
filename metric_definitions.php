<?php
include('functions/db.php');
global $conn;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Metric Definitions</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<?php
$sql = 'SELECT metric, metric_def, numerator_definition, denominator_definition, exclusion_definition 
from metrics
order by metric asc';
?>
<?php
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {?>
            <div class="page">
            <h2><?=$row['metric']?></h2>
            <h3>Definition</h3>
            <?=$row['metric_def']?>
            <h3>Numerator</h3>
            <?=$row['numerator_definition']?>
            <h3>Denominator</h3>
            <?=$row['denominator_definition']?>
            <h3>Exclusion/Exceptions</h3>
            <?=$row['denominator_definition']?>
            </div>
        <?php }
        $result->free();
    }
?>
</body>
</html>