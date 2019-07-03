<?php
include('functions/db.php');
global $conn;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Active Provider Listing</title>
</head>
<body>
<?php
$sql = 'SELECT provider_name, service_line, NPI, SER, badge_num, provider_type 
from providers, service_lines, provider_types
where providers.service_line_id=service_lines.id AND providers.provider_type_id=provider_types.id AND
provider_status=1
order by provider_name asc';
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