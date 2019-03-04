<div class="metric_table">
    <table class="metric_table"><tr><td>Q1</td><td>Q2</td><td>Q3</td><td>Q4</td></tr>
        <tr>
        <?php
            for ($j=1; $j <5; $j++) {
                if ($j<count($metric_perf)+1) {
                        echo '<td>'.$metric_perf[$j]['performance'].'% ('.$metric_perf[$j]['numerator'].'/'.$metric_perf[$j]['denominator'].')</td>';
                } else {
                        echo '<td></td>';
                }
            }
            echo '</tr><tr>';
            for ($j=1; $j < 5; $j++) {
                if ($j<count($metric_perf)+1) {
                        echo '<td>'.curr_format($inc_array[$j]).' ('.number_format($percent_incentive[$j],0).'%)</td>';
                } else {
                        echo '<td></td>';
                }
            }
        ?>
        </tr>
    </table>
</div>
