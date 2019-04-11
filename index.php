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

        $qtr_incentive_per_metric = ($incentive_metric_count > 0 ? ($qtr_incentive/$incentive_metric_count):0);

        $quarter_status = getContractStatusArray($contract['effective'],$contract['default_expire'],$year_sel);

        $performances = getPerformancesByProvider($provider['id'], $year_sel);

        $no_data_metrics = getNoDataMetrics($specificmetrics, $performances, $quarter_sel);
        
        $gateway_status = getGatewayStatus($specificmetrics, $performances);

        foreach($quarter_status as $qtr => $qtr_status) {
            if ($gateway_status[$qtr]==0) {
                $quarter_status[$qtr] = 'ineligible';
            }
        }

        $total_incentive = 0;

        $page = 0;
        $i = 0;
        echo '<div class="provider">';
        while ($page < ($metriccount/(2*$metrics_per_row))) {
            echo '
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
            echo "</div><div id='incentive'></div></div>\n";
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
                    $metric_perf = array_fill(1,4,array('numerator'=>null,'denominator'=>null,'performance'=>null));
                    foreach ($perfkeys as $key) {
                        $metric_perf[$performances[$key]['quarter']]['numerator'] = $performances[$key]['numerator'];
                        $metric_perf[$performances[$key]['quarter']]['denominator'] = $performances[$key]['denominator'];
                        if ($specificmetrics[$i]['is_calculated_metric']) {
                            $metric_perf[$performances[$key]['quarter']]['performance'] = round($performances[$key]['numerator'], 1);
                        } else {
                            $metric_perf[$performances[$key]['quarter']]['performance'] = round($performances[$key]['numerator']/$performances[$key]['denominator']*100, 1);
                        }
                    }
                    
                    //color array
                    if (array_key_exists('thresholds', $specificmetrics[$i])) {
                        $colors = array_column($specificmetrics[$i]['thresholds'], 'color_hex', 'threshold');
                    } else {
                        if (!$specificmetrics[$i]['threshold_direction']) {
                            $colors = array(0=>'#8c8c8c');
                        } else {
                            $colors = array(100=>'#8c8c8c');
                        }
                    }
                    //perf array to pass to create graph
                    $perfarr = array();
                    foreach ($metric_perf as $key => $quarter) {
                            $perfarr[$key] = $quarter['performance'];
                    }
                    createGraph($perfarr, $specificmetrics[$i]['threshold_direction'], $colors, $quarter_sel);
                
                    echo "</div>\n";
                
                    //comp info array population
                    $inc_array = array_fill(1, 4, null);
                    $percent_incentive = array_fill(1, 4, null);
                    if (array_key_exists('thresholds', $specificmetrics[$i])) {
                        $thresh_percent_arr = array_column($specificmetrics[$i]['thresholds'], 'threshold_incentive_percent', 'threshold');
                    } else {
                        if (!$specificmetrics[$i]['threshold_direction']) {
                            $thresh_percent_arr = array(0=>100);
                        } else {
                            $thresh_percent_arr = array(100=>100);
                        }
                    }

                    for ($m=1; $m <= $quarter_sel; $m++) {
                        if ($specificmetrics[$i]['is_beta_metric']){
                            $percent_incentive[$m] = 0;
                            $inc_array[$m] = 0;
                        } else {
                            if (in_array($specificmetrics[$i]['metric_id'], $no_data_metrics[$m], true)) {
                                if ($quarter_status[$m]=='eligible') {
                                    if ($m<count($metric_perf)+1) {
                                        $percent_incentive[$m] = 100;
                                        $inc_array[$m] = $qtr_incentive_per_metric;
                                    }
                                } elseif ($quarter_status[$m]=='default') {
                                    $percent_incentive[$m] = 100;
                                    $inc_array[$m] = $qtr_incentive_per_metric;
                                } elseif ($quarter_status[$m]=='ineligible') {
                                    $percent_incentive[$m]=0;
                                } elseif ($quarter_status[$m]=='partial') {
                                    $partial_qtr_percent = getPartialQuarterPercent($m, $contract['effective'], $contract['default_expire'], $year_sel);
                                    $percent_incentive[$m] = $partial_qtr_percent['default'] + $partial_qtr_percent['eligible'];
                                    $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric;
                                }
                            } else {
                                if ($quarter_status[$m]=='eligible') {
                                    if ($m<count($metric_perf)+1) {
                                        $percent_incentive[$m] = getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m], $specificmetrics[$i]['threshold_direction']);
                                        $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric;
                                    }
                                } elseif ($quarter_status[$m]=='default') {
                                    $percent_incentive[$m] = 100;
                                    $inc_array[$m] = $qtr_incentive_per_metric;
                                } elseif ($quarter_status[$m]=='ineligible') {
                                    $percent_incentive[$m]=0;
                                } elseif ($quarter_status[$m]=='partial') {
                                    $partial_qtr_percent = getPartialQuarterPercent($m, $contract['effective'], $contract['default_expire'], $year_sel);
                                    $percent_incentive[$m] = $partial_qtr_percent['default'] + ($partial_qtr_percent['eligible']/100*getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m], $specificmetrics[$i]['threshold_direction']));
                                    $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric;
                                }
                            }
                        }
                    }

                    $total_incentive = $total_incentive + $inc_array[$quarter_sel];

                    include('constructors/metric_table.php');

                    echo '<div class="metric_message">';
                    if (array_key_exists('thresholds', $specificmetrics[$i])) {
                        $messages = array_column($specificmetrics[$i]['thresholds'], 'message', 'threshold');
                    } else {
                        if (!$specificmetrics[$i]['threshold_direction']) {
                            $messages = array(0=>null);
                        } else {
                            $messages = array(100=>null);
                        }
                    }
                    if (!in_array($specificmetrics[$i]['metric_id'], $no_data_metrics[$quarter_sel], true)) {
                        echo getCorrectThresholdValue($messages, $metric_perf[$quarter_sel]['performance'], $specificmetrics[$i]['threshold_direction']);
                    }
                    echo "</div>\n";

                    echo '<div class="metric_def">';
                    echo $specificmetrics[$i]['metric_def'];
                    echo "</div>\n";

                    echo "</div>\n";
                    $i++;
                    $k++;
                }
                echo "</div>\n";
                $row++;
            } ?>
        <?php        
        if (!($page+1 < ($metriccount/(2*$metrics_per_row)))){
            echo '<div class="incentive">Total Quality Incentive: '.curr_format($total_incentive).'</div>';
        } 
        ?>
        <div class="disclaimer">
        CONFIDENTIAL PEER REVIEW DOCUMENT<br>
        This document contains privileged and confidential information for exclusive use in the peer review and quality control functions of the Kettering Health Network and Kettering Physician Network. This information is legally protected by Ohio Revised Code Sections 2305.25, 2305.251 and 2305.252. Further review, dissemination, distribution or copying of this information is strictly prohibited.
        </div>
        <?php
        $page++;
        echo '</div>';
        }
        echo '</div>';
    }
    ?>
    </div>
</body>
</html>