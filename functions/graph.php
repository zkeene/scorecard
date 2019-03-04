<?php

function scaleValue ($value, $barheight, $minv, $maxv) {
    $offset = $maxv * .10;
    $bottom = (($minv - $offset) > 0) ? ($minv - $offset) : 0;
    $top = (($maxv + $offset) < 100) ? ($maxv + $offset) : 100;
    return $barheight * (($value - $bottom)/($top-$bottom));
}

function createGraph($p, $d, $c)
{

    $thresharr = array();
    foreach ($c as $threshold => $color){
        if (($d==0 && $threshold!=0) || ($d==1 && $threshold!=100)) {
            $thresharr[] = array($threshold,$color);
        }
    }

    $width = 280;
    $height = 200;
    $fontsize = 10;
    $valarray = array_merge($p,array_map('array_shift',$thresharr));
    $maxv = max($valarray);
    $minv = min($valarray);
    echo '<svg width="'.$width.'" height="'.$height.'">';
    $barheight = $height-15;
    $barpercent = .7;
    $spacepercent = (1-$barpercent)/3;
    $barwidth = $width*($barpercent/4);
    $spacewidth = $width*$spacepercent;
    echo '<line x1="0" x2="'.$width.'" y1="'.($height-14).'" y2="'.($height-14).'" style="stroke:black;stroke-width:2"/>';
    $i = 0;
    foreach ($p as $val) {
        $perf_color = getCorrectThresholdValue($c, $val, $d);
        $xcoord = ($i*$barwidth)+($i*$spacewidth)+($spacewidth/2);
        $scaledvalue = scaleValue($val,$barheight,$minv,$maxv);
        echo '<rect width="'.$barwidth.'" height="'.$scaledvalue.'" x="'.$xcoord.'" y="'.($barheight-$scaledvalue).'" style="fill:'.$perf_color.'"/>';
        echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-20).'" font-size="10" font-weight="bold">'.$val.'</text>';
        echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-4).'" font-size="10" font-weight="bold">Q'.($i+1).'</text>';
        $i++;
    }

    foreach ($thresharr as $thresh) {
        $ypos = $barheight-scaleValue($thresh[0],$barheight,$minv,$maxv);
        echo '<line x1="0" x2="'.$width.'" y1="'.$ypos.'" y2="'.$ypos.'" style="stroke:'.$thresh[1].';stroke-width:2"/>';
        echo '<text x="'.($width-15).'" y="'.($ypos + 10).'" font-size="10" font-weight="bold" fill="'.$thresh[1].'">'.$thresh[0].'</text>';
    }

    echo '</svg>';
}