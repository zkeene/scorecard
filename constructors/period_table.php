<div class="metric_table">
    <table class="metric_table">
        <tr><td colspan="2">Period</td></tr>
        <tr><td>P1</td><td>P2</td></tr>
        <tr>
        <?php
        $period1 = $period_metric_perf[1]['performance'].'% ('.$period_metric_perf[1]['numerator'].'/'.$period_metric_perf[1]['denominator'].')';
        $period2 = $period_metric_perf[2]['performance'].'% ('.$period_metric_perf[2]['numerator'].'/'.$period_metric_perf[2]['denominator'].')';;
        echo '<td>'.$period1.'</td>';
        echo '<td>'.$period2.'</td>';
        ?>
        </tr>
    </table>
</div>
