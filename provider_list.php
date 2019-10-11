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
$sql = 'SELECT provider_name, service_line, NPI, SER, badge_num, provider_type, total_incentive_amount, if(default_expire_date > current_date(),"Defaulted", "") as defaulted 
FROM providers
inner join service_lines
on providers.service_line_id = service_lines.id
inner join provider_types
on providers.provider_type_id = provider_types.id
left join contracts
on providers.id = contracts.provider_id
WHERE provider_status=1
order by provider_name ASC';
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