<!DOCTYPE html>
<html lang="en">

<head>
    <title>KPN Quality Scorecard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <?php
            include('functions/methods.php');
            include('functions/graph.php');

            include('constructors/selector.php');

    $metrics_per_row = 2;

    $specificmetrics = getSpecificMetrics($service_line_id, $year_sel);
    $metriccount = count($specificmetrics);

    $providers = getProvidersByServiceLine($service_line_id);

    foreach ($providers as $provider) {

        $performances = getPerformacesByProvider($provider['id'], $year_sel);
    
        $page = 0;
        $i = 0;
        while ($page < ($metriccount/(2*$metrics_per_row))) {
            echo '
            <div class="provider">
            <div class="page">
            <div class="row">
                <div class="logo">
                    <img src="logo.png" height="33.5">
                </div>
                <div class="scorecard_title">
                Quality Scorecard
                </div>';

            echo '<div class="identity">';
            echo getServiceLineName($service_line_id).' - ';
            echo $provider['provider_name'];
            echo "</div></div>\n";
            $row=0;
            while ($i < $metriccount && $row < 2) {
                echo '<div class="row">';
                $k=0;
                while ($i < $metriccount && $k < $metrics_per_row) {
                    echo '<div class="metric">
                        <div class="metric_title">';
                    echo $specificmetrics[$i]['metric'];
                    echo "</div>\n";
                    echo '<div class="metric_graph">';

                    //build metric performance array
                    $perfkeys = array_keys(array_column($performances, 'metric_id'), $specificmetrics[$i]['metric_id']);
                    $metric_perf = array();
                    foreach ($perfkeys as $key) {
                        $metric_perf[$performances[$key]['quarter']]['numerator'] = $performances[$key]['numerator'];
                        $metric_perf[$performances[$key]['quarter']]['denominator'] = $performances[$key]['denominator'];
                        $metric_perf[$performances[$key]['quarter']]['performance'] = round($performances[$key]['numerator']/$performances[$key]['denominator']*100, 1);
                    }

                    //color array
                    $colors = array_column($specificmetrics[$i]['thresholds'], 'color_hex', 'threshold');
                    //perf array to pass to create graph
                    $perfarr = array();
                    foreach ($metric_perf as $quarter) {
                        $perfarr[] = $quarter['performance'];
                    }
                    createGraph($perfarr, $specificmetrics[$i]['threshold_direction'], $colors);
                
                    echo "</div>\n";
                
                    include('constructors/metric_table.php');

                    echo '<div class="metric_def">';
                    echo $specificmetrics[$i]['metric_def'];
                    echo "</div>\n";

                    echo '<div class="metric_message">';
                    $messages = array_column($specificmetrics[$i]['thresholds'], 'message', 'threshold');
                    echo getCorrectThresholdValue($messages, $metric_perf[count($metric_perf)]['performance'], $specificmetrics[$i]['threshold_direction']);
                    echo "</div>";

                    echo "</div>\n";
                    $i++;
                    $k++;
                }
                echo "</div>\n";
                $row++;
            } ?>
        <div class="disclaimer">
        CONFIDENTIAL PEER REVIEW DOCUMENT<br>
        This document contains privileged and confidential information for exclusive use in the peer review and quality control functions of the Kettering Health Network and Kettering Physician Network. This information is legally protected by Ohio Revised Code Sections 2305.25, 2305.251 and 2305.252. Further review, dissemination, distribution or copying of this information is strictly prohibited.
        </div>
        </div>
        </div>
        <?php
         $page++;
        }
    }
    ?>
    </div>
</body>
</html>