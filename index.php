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

    $specificmetrics = getSpecificMetrics($service_line_id, $year_sel, TRUE);
    $metriccount = count($specificmetrics);
    $incentive_metric_count = count(array_filter($specificmetrics, function ($d) {return !$d['is_beta_metric'];}));

    $providers = getProvidersByServiceLine($service_line_id);

    foreach ($providers as $provider) {

        $contract = getContract($provider['id']);
        if ($contract['incentive']) {
            $qtr_incentive = $contract['incentive']/4;
        } else {
            $qtr_incentive = 0;
        }
        $qtr_incentive_per_metric = $qtr_incentive/$incentive_metric_count;
        $quarter_status = getContractStatusArray($contract['effective'],$contract['default_expire'],$year_sel);

        $performances = getPerformacesByProvider($provider['id'], $year_sel);
        
        $gateway_status = getGatewayStatus($specificmetrics, $performances);

        foreach($quarter_status as $qtr => $qtr_status) {
            if ($gateway_status[$qtr]==0) {
                $quarter_status[$qtr] = 'ineligible';
            }
        }

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
                
                    //comp info array population
                    $inc_array = array_fill(1, 4, null);
                    $percent_incentive = array_fill(1, 4, null);
                    $thresh_percent_arr = array_column($specificmetrics[$i]['thresholds'], 'threshold_incentive_percent', 'threshold');
                
                    for ($m=1; $m < 5; $m++) {
                        if ($quarter_status[$m]=='eligible') {
                            if ($m<count($metric_perf)+1) {
                                $percent_incentive[$m] = getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m-1], $specificmetrics[$i]['threshold_direction']);
                                $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric;
                            }
                        } elseif ($quarter_status[$m]=='default') {
                            $percent_incentive[$m] = 100;
                            $inc_array[$m] = $qtr_incentive_per_metric;
                        } elseif ($quarter_status[$m]=='ineligible') {
                            $percent_incentive[$m]=0;
                        } elseif ($quarter_status[$m]=='partial') {
                            $partial_qtr_percent = getPartialQuarterPercent($m,$contract['effective'],$contract['default_expire'],$year_sel);
                            $percent_incentive[$m] = $partial_qtr_percent['default'] + ($partial_qtr_percent['eligible']/100*getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m-1], $specificmetrics[$i]['threshold_direction']));
                            $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric;
                        }
                    }

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