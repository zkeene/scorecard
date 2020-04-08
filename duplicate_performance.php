<?php
include('functions/db.php');
global $conn;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Duplicate Performance Providers</title>
</head>
<body>
<?php
$sql = 'select distinct provider_name
from performances, providers
where performances.provider_id = providers.id and (year>2020 or (quarter>2 and year=2019))
group by provider_id, metric_id, quarter, year
HAVING count(provider_id) >1 AND count(metric_id) >1 AND count(quarter) >1 AND count(year) >1';
?>
<table border=1>
<?php
    if ($result = $conn->query($sql)) {
        $finfo = $result->fetch_fields();
        echo '<tr>';
        foreach($finfo as $field) {
            echo "<th>$field->name</th>";
        }
        echo '</tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            foreach ($row as  $cell) {
                echo "<td>$cell</td>";
            }
            echo '</tr>';
        }
        $result->free();
    }
?>
</table>
</body>
</html>