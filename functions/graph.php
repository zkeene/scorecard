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
//$c: array(threshold=>hex_color)
//$q: run graph to this quarter
//$x: array(period=>performance)
//echos SVG image of graph
function createGraph($p, $d, $c, $q, $x=[])
{

    $width = 280; //width of svg element
    $height = 200; //height of svg element
    $fontsize = 10; //font size to use in svg element
    $barpercent = .7; //percent of SVG width for bars to cover
    $topmargin = 15; //margin at top of SVG
    $num_bars = 4;

    //change $num_bars to 6 if we have anything in period array
    if(count($x)>0){
        $num_bars=6;
    }

    //filter the original threshold/color array to remove 0 and 100 thresholds based on direction
    $thresharr = array();
    foreach ($c as $threshold => $color){
        if (($d==0 && $threshold!=0) || ($d==1 && $threshold!=100)) {
            $thresharr[] = array($threshold,$color);
        }
    }

    //find the min and max values
    $valarray = array_merge(array_filter($p),array_map('array_shift',$thresharr));
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
            $perf_color = getCorrectThresholdValue($c, $val, $d);
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

    foreach ($thresharr as $thresh) {
        $ypos = $barheight-scaleValue($thresh[0],$barheight,$minv,$maxv);
        echo '<line x1="0" x2="'.$width.'" y1="'.$ypos.'" y2="'.$ypos.'" style="stroke:'.$thresh[1].';stroke-width:2"/>';
        echo '<rect width="20" height="'.($fontsize+2).'" x="'.($width-20).'" y="'.($ypos).'" style="fill:'.$thresh[1].';"/>';
        echo '<text x="'.($width-20).'" y="'.($ypos + 10).'" font-size="'.$fontsize.'" font-weight="bold" fill="#000000">'.($thresh[0]+0).'</text>';
    }

    echo '</svg>';
}