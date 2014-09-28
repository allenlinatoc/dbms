<?php

/**
 * This file is for debugging and app development purposes only.
 * Should not be used for front-end features.
 * Possible purposes are:
 * -- you want to test the functionality of the methods you created from another registered class.
 * -- or you may want to test the effective of such algorithms.
 * 
 */

# Begin coding below

// [1] Get second highest
/*
$numbers = [10, 32, 6, 3, 17];     
$max = 0;
$haha = 0;
foreach($numbers as $number) {
    if ($number > $max) {
        $haha= $max;
        $max= $number;
    } else if ($number > $haha) {
        $haha = $number;
    }
}
echo 'SECOND HIGHEST: ' . $haha;
*/

// [2] Get Combination from INT Array
/*
function getCombiFromIntArray(array $arr1, array $arr2) {
    $result = array();
    foreach($arr1 as $x) {
        foreach($arr2 as $y) {
            if ($x!=$y) {
                array_push($result, '[' . $x . ',' . $y . ']');
                echo $result[count($result)-1] . '<br>';
            }
        }
    }
    return $result;
}
echo 'Combination count: ' . count(getCombiFromIntArray([1,2,3], [2,3,4]));
*/


// [3] Return the highest average among both half-ends of an INT array
/*
function getHighestAverageFromHalfset(array $set) {
    $ave1 = 0;$ave2 = 0;$count = count($set);
    
    for($x=0; $x<count($set); $x++) {
        if ($x < ($count/2)) {
            $ave1 += $set[$x];
        } else {
            $ave2 += $set[$x];
        }
    }
    $ave1 = $ave1 / ($count/2);
    $ave2 = $ave2 / ($count/2) + ($count%2==0 ? 0 : 1);
    return intval($ave1 > $ave2 ? $ave1 : $ave2);
}
echo getHighestAverageFromHalfset([3,4,5,6,1,2,3,4]);
 */


// [4] Return if the last digit of both integers supplied are same
/*
function isSameLastDigit($x, $y) {
    return strval($x)[strlen(strval($x))-1]==strval($y)[strlen(strval($y))-1] ? 'true' : 'false';
}
echo isSameLastDigit(-32324, 4324124);
*/


// [5] Count the odd and even
/*
function countEvenOdd(array $intset) {
    $odd = 0;
    $even = 0;
    foreach($intset as $num) {
        if ($num%2==0) {
            $even++;
        } else {
            $odd++;
        }
    }
    return 'Number of odd numbers is ' . $odd . '. Number of even numbers is ' . $even . '.';
}
*/


// [6] Get factorial
/*
function getFactorial($intval) {
    $result = 1;
    while ($intval > 0) {
        $result *= $intval;
        $intval--;
    }
    return $result;
}
echo getFactorial(9);
*/

// [7] Returns boolean value if the array is splitable and sums of numbers from half to both ends are equal
/*
function getEqualSumBothEnds(array $set) {
    $count = count($set);
    $sum1 = 0;
    $sum2 = 0;
    for($x=0; $x<$count; $x++) {
        if ($x < intval($count/2)) {
            $sum1 += $set[$x];
        } else {
            $sum2 += $set[$x];
        }
    }
    return $sum1==$sum2 ? 'true' : 'false';
}
echo getEqualSumBothEnds(array(2,3,4,1,2));
*/

// [8] Returns the number of group of same adjacent integers in an integral array
/*
function countBundles(array $set) {
    $result = 0;
    $has_same = false;
    $oldval = $set[0];
    for($x=1; $x<count($set); $x++) {
        if (!$has_same && $set[$x]==$oldval) {
            $has_same = true;
            $result++;
        } else if ($set[$x]!=$oldval) {
            $has_same = false;
            $oldval = $set[$x];
        }
    }
    return $result;
}

echo countBundles([0,0,2,2,1,1,1,2,1,1,2,2]);
*/

// [9] Returns the number of small bars needed to achieve certain WEIGHT GOAL (in Kilos)
//      assuming SMALLBAR=(1 kilo), BIGBAR=(5 kilos)
/*
function getPackChocolate($smallbars, $bigbars, $goalkilo) {
    while ($bigbars > 0 && $goalkilo-5 >= 0) {
        $goalkilo-=5;
        $bigbars--;
    }
    if ($bigbars==0 && $goalkilo-$smallbars > 0) {
        return -1;
    } else {
        return $goalkilo;
    }
}
echo getPackChocolate(6, 2, 10);
*/



?>