<?php

function generate_no($pattern, Closure $checkIfExists = null, $seed = null)
{
    if (strlen($pattern) != strlen($seed)) {
        $seed = null;
    } else {
        $regex = "/^$pattern$/";
        $regex = str_replace('yyyy', date('Y'), $regex);
        $regex = str_replace('mm', date('m'), $regex);
        $regex = str_replace('dd', date('d'), $regex);
        $regex = str_replace('#', '\d', $regex);
        $regex = str_replace('?', '\w', $regex);
        preg_match($regex, $seed, $mms);
        if (count($mms) == 0) {
            $seed = null;
        }
    }
    $pattern_ = '';
    $char_count = 0;
    $digit_count = 0;
    $variables = [];
    for ($i = 0; $i < strlen($pattern); $i++) {
        $char = $pattern[$i];
        switch ($char) {
            case '?':
                $char_count++;
                $pattern_ .= "c$char_count";
                $variables["c$char_count"] = "c$char_count";
                break;
            case '#':
                $digit_count++;
                $pattern_ .= "d$digit_count";
                $variables["d$digit_count"] = "d$digit_count";
                break;
            default:
                $digit_count = 0;
                $char_count = 0;
                $pattern_ .= $char;
                break;
        }
    }

    //reset values
    $raw = $pattern_;
    if (!$seed) {
        foreach ($variables as $type => $name) {
            switch ($type[0]) {
                case 'd':
                    $raw = str_replace($name, '0', $raw);
                    break;
                case 'c':
                    $raw = str_replace($name, '0', $raw);
                    break;
            }
        }
        $raw = increment($raw, $pattern_);
    } else {
        $raw = $seed;
    }
    $no = null;
    while (true) {

        $no = str_replace("yyyy", date('Y'), $raw);
        $no = str_replace("mm", date('m'), $no);
        $no = str_replace("dd", date('d'), $no);
        $no = str_replace("yy", date('y'), $no);
        if ($checkIfExists) {
            if (!$checkIfExists($no)) {
                return $no;
            }
        } else {
            return $no;
        }
        $raw = increment($raw, $pattern_);
    }
    return $no;
}

function increment($value, $pattern = null)
{
    $chars = [];
    for ($i = 0; $i < strlen($value); $i++) {
        $chars[] = $value[$i];
    }
    $i = max(count($chars), strlen($pattern));
    $x = $i;
    $y = $i;
    while (true) {
        if ($i < 1) {
            throw new Exception("Overflow incrementing $value");
        }
        $i--;
        $charType = $pattern[$i];

        if ($charType == 'd' || $charType == 'c' || $pattern == null) {
            if (isset($pattern[$i + 1]) || $pattern == null) {
                if (is_numeric($pattern[$i + 1]) || $pattern == null) {
                    if ($pattern == null) {
                        $y = $i;
                    } else {
                        $y = count($chars) - (strlen($pattern) - $x);
                    }
                    if (incrementChar($chars[$y], $pattern[$i])) {
                    } else {
                        break;
                    }
                } else {
                    $x--;
                }
            } else {
                $x--;
            }
        } else {
            $x--;
        }
    }
    return implode('', $chars);
}

function incrementChar(&$value, $pattern, $propergateInvalid = true)
{
    switch ($pattern) {
        case 'd':
            if ($value == 9) {
                $value = 0;
                return true;
            } else {
                $value++;
                return false;
            }
            break;
        case 'c':
            $alpha = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $count = strlen($alpha);
            $position = strpos($alpha, strtoupper($value));
            if ($position === false) {
                return $propergateInvalid;
            }
            $position++;
            if ($position == $count) {
                $value = $alpha[0];
                return true;
            } else {
                $value = $alpha[$position];
                return false;
            }
            break;
    }
}
