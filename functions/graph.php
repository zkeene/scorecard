<?php

function scaleValue ($value, $barheight, $min_value, $max_value) {
    $buffer = $max_value * .10;
    $bottom = (($min_value - $buffer) > 0) ? ($min_value - $buffer) : 0;
    $top = (($max_value + $buffer) < 100) ? ($max_value + $buffer) : 100;
    $scaled_value = $barheight * (($value - $bottom)/($top-$bottom));
    return $scaled_value;
}

//$p: array(quarter=>performance)
//$d: direction(0=>up,1=>down)
//$c: array(threshold=>hex_color) what is the newly expected data structure??
//$q: run graph to this quarter
//$x: array(period=>performance)
//$f: floating goal boolean
//echos SVG image of graph
function createGraph(array $p, int $d, array $c, int $q, array $x=[], int $f=0)
{

    $width = 280; //width of svg element
    $height = 200; //height of svg element
    $fontsize = 10; //font size to use in svg element
    $barpercent = .7; //percent of SVG width for bars to cover
    $topmargin = 15; //margin at top of SVG
    $num_bars = 4;

    //shrink bars for floating goals
    if ($f==1) {
        $barpercent = $barpercent * 0.8;
    }

    //change $num_bars to 6 if we have anything in period array
    if(count($x)>0){
        $num_bars=6;
    }

    //filter the original threshold/color array to remove 0 and 100 thresholds based on direction
    $thresharr = array();
    if ($f==1) {
        foreach ($c as $qtr => $qtr_threshold) { //This fails if not all quarters have thresholds, should eventually rewrite to use last thresholds if missing
            foreach ($qtr_threshold as $threshold => $color) {
                if (($d==0 && $threshold!=0) || ($d==1 && $threshold!=100)) {
                    $thresharr[$qtr][] = array($threshold,$color);
                }
            }
        }
    } else {
        foreach ($c as $threshold => $color) {
            if (($d==0 && $threshold!=0) || ($d==1 && $threshold!=100)) {
                $thresharr[] = array($threshold,$color);
            }
        }
    }

    //find the min and max values
    if($f==1){
        foreach ($thresharr as $qtr => $qtr_threshold) {
            foreach ($qtr_threshold as $threshold) {
                $valarray[] = $threshold[0];
            }
        }
        foreach ($p as $p_item) {
            $valarray[] = $p_item;
        }
    } else {
        $valarray = array_merge(array_filter($p), array_map('array_shift', $thresharr));
    }

    $maxv = max($valarray);
    $minv = min($valarray);

    $barheight = $height-$topmargin;
    $spacepercent = (1-$barpercent)/$num_bars;
    //$width -15 to give space for the threshold box
    $barwidth = ($width-15)*($barpercent/$num_bars);
    $spacewidth = ($width-15)*$spacepercent;

    echo '<svg width="'.$width.'" height="'.$height.'">';
    echo '<line x1="0" x2="'.$width.'" y1="'.($barheight+1).'" y2="'.($barheight+1).'" style="stroke:black;stroke-width:2"/>';
    
    $i = 1;
    $qtr_num = 1;
    foreach ($p as $key => $val) {
        $xcoord = (($i-1)*$barwidth)+(($i-1)*$spacewidth)+($spacewidth/3);
        if (($key <= $q) && (!is_null($val))) {
            if($f==1){
                $perf_color = getCorrectThresholdValue($c[$key], $val, $d);
            } else {
                $perf_color = getCorrectThresholdValue($c, $val, $d);
            }
            $scaledvalue = scaleValue($val, $barheight, $minv, $maxv);
            echo '<rect width="'.$barwidth.'" height="'.$scaledvalue.'" x="'.$xcoord.'" y="'.($barheight-$scaledvalue).'" style="fill:'.$perf_color.'"/>';
            echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-20).'" font-size="'.$fontsize.'" font-weight="bold">'.$val.'</text>';
        }
        echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-4).'" font-size="'.$fontsize.'" font-weight="bold">Q'.$qtr_num.'</text>';
        if ($i==2 && count($x)>0){
            $i++;
        }
        $i++;
        $qtr_num++;
    }
    //period performance
    if (count($x)>0) {
        $i = 1;
        $per_num = 1;
        foreach ($x as $key => $val) {
            $xcoord = ((($key*3)-1)*$barwidth)+((($key*3)-1)*$spacewidth)+($spacewidth/3);
            if (($key <= $q) && (!is_null($val))) {
                $perf_color = getCorrectThresholdValue($c, $val, $d);
                $scaledvalue = scaleValue($val, $barheight, $minv, $maxv);
                echo '<rect width="'.$barwidth.'" height="'.$scaledvalue.'" x="'.$xcoord.'" y="'.($barheight-$scaledvalue).'" style="fill:'.$perf_color.'"/>';
                echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-20).'" font-size="'.$fontsize.'" font-weight="bold">'.$val.'</text>';
            }
            echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-4).'" font-size="'.$fontsize.'" font-weight="bold">P'.$per_num.'</text>';
            $i++;
            $per_num++;
        }
    }
    //thresholds
    if ($f==1) {
        foreach ($thresharr as $qtr => $thresh) {
            foreach ($thresh as $thresh_item) {
                $thresh_bar_width = ($qtr*$barwidth)+(($qtr-1)*$spacewidth)+($spacewidth/3) + 23;
                $thresh_box_pos = ($qtr*$barwidth)+(($qtr-1)*$spacewidth)+($spacewidth/3) + 3;
                $thresh_bar_start = (($qtr-1)*$barwidth)+(($qtr-1)*$spacewidth)+($spacewidth/3) -3;
                $ypos = $barheight-scaleValue($thresh_item[0], $barheight, $minv, $maxv);
                echo '<line x1="'.$thresh_bar_start.'" x2="'.$thresh_bar_width.'" y1="'.$ypos.'" y2="'.$ypos.'" style="stroke:'.$thresh_item[1].';stroke-width:2"/>';
                echo '<rect width="20" height="'.($fontsize+2).'" x="'.$thresh_box_pos.'" y="'.$ypos.'" style="fill:'.$thresh_item[1].';"/>';
                echo '<text x="'.$thresh_box_pos.'" y="'.($ypos + 10).'" font-size="'.$fontsize.'" font-weight="bold" fill="#000000">'.($thresh_item[0]+0).'</text>';
            }
        }
    } else {
        foreach ($thresharr as $thresh) {
            $ypos = $barheight-scaleValue($thresh[0], $barheight, $minv, $maxv);
            echo '<line x1="0" x2="'.$width.'" y1="'.$ypos.'" y2="'.$ypos.'" style="stroke:'.$thresh[1].';stroke-width:2"/>';
            echo '<rect width="20" height="'.($fontsize+2).'" x="'.($width-20).'" y="'.$ypos.'" style="fill:'.$thresh[1].';"/>';
            echo '<text x="'.($width-20).'" y="'.($ypos + 10).'" font-size="'.$fontsize.'" font-weight="bold" fill="#000000">'.($thresh[0]+0).'</text>';
        }
    }

    echo '</svg>';
}
?>