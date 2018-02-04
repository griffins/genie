<?php

namespace Sequence;

class Factory
{
    const CHARACTER_DIGIT = 'd';
    const CHARACTER_ALPHA_NUMERIC = 'c';

    protected $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function create($alphabet = null)
    {
        $instance = new static();
        if ($alphabet) {
            $instance->alphabet = $alphabet;
        }

        return $instance;
    }

    public function next($pattern, \Closure $dontUse = null, $startAt = null, $smartSearch = true)
    {
        //check if start at matches pattern if not don't use it
        if ($startAt != null) {
            if (strlen($pattern) != strlen($startAt)) {
                $startAt = null;
            } else {
                $regex = "/^$pattern$/";
                $regex = str_replace('<yyyy>', date('Y'), $regex);
                $regex = str_replace('<mm>', date('m'), $regex);
                $regex = str_replace('<dd>', date('d'), $regex);
                $regex = str_replace('#', '\d', $regex);
                $regex = str_replace('?', '\w', $regex);
                preg_match($regex, $startAt, $mms);
                if (count($mms) == 0) {
                    $startAt = null;
                }
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
        if (!$startAt) {
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
            $raw = $this->incrementWord($raw, $pattern_);
        } else {
            $raw = $startAt;
        }
        $backlog = null;
        $status = true;
        $step = 1;
        while (true) {
            $no = $this->replaceStuff($raw);
            if ($dontUse) {
                if (!$smartSearch && !$dontUse($no)) {
                    return $no;
                } else {
                    //if we can use this sequence
                    if (!$dontUse($no)) {
                        if ($step == 1) {
                            return $no;
                        } else {
                            if ($step > 8) {
                                $step = max(1, ($step - $step / 2));
                            } else {
                                $step -= 1;
                            }
                            if ($status === false) {
                                $raw = $backlog;
                            } elseif ($step == 1) {
                                $minus = $this->replaceStuff($this->decrementWord($raw, $pattern_));
                                if ($dontUse($minus)) {
                                    return $no;
                                }
                            }

                            $status = false;
                        }
                    } else {
                        //if we cant use this number and previous number was valid
                        // means we decreased so much
                        if ($status === false && $step == 2) {
                            $minus = $this->replaceStuff($this->decrementWord($raw, $pattern_));
                            if ($step == 2 && !$dontUse($minus)) {
                                return $minus;
                            }
                        }
                        $step *= 2;
                        $backlog = $raw;
                        $status = true;
                        $plus = $this->replaceStuff($this->incrementWord($raw, $pattern_));
                        if ($step == 2 && !$dontUse($plus)) {
                            return $plus;
                        }
                    }
                }
            } else {
                return $no;
            }
            foreach (range(1, $step) as $x) {
                $raw = $this->incrementWord($raw, $pattern_);
            }
        }
    }

    public function incrementWord($value, $pattern = null)
    {
        $chars = [];
        for ($i = 0; $i < strlen($value); $i++) {
            $chars[] = $value[$i];
        }
        $i = max(count($chars), strlen($pattern));
        $x = $i;
        while (true) {
            if ($i < 1) {
                throw new \Exception("Overflow incrementing $value");
            }
            $i--;
            $charType = $pattern[$i];

            if ($charType == static::CHARACTER_DIGIT || $charType == static::CHARACTER_ALPHA_NUMERIC || $pattern == null) {
                if (isset($pattern[$i + 1]) || $pattern == null) {
                    if (is_numeric($pattern[$i + 1]) || $pattern == null) {
                        if ($pattern == null) {
                            $y = $i;
                        } else {
                            $y = count($chars) - (strlen($pattern) - $x);
                        }
                        if ($this->incrementCharacter($chars[$y], $pattern[$i])) {
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

    public function decrementWord($value, $pattern = null)
    {
        $chars = [];
        for ($i = 0; $i < strlen($value); $i++) {
            $chars[] = $value[$i];
        }
        $i = max(count($chars), strlen($pattern));
        $x = $i;
        while (true) {
            if ($i < 1) {
                throw new \Exception("Underflow decrementing $value");
            }
            $i--;
            $charType = $pattern[$i];

            if ($charType == static::CHARACTER_DIGIT || $charType == static::CHARACTER_ALPHA_NUMERIC || $pattern == null) {
                if (isset($pattern[$i + 1]) || $pattern == null) {
                    if (is_numeric($pattern[$i + 1]) || $pattern == null) {
                        if ($pattern == null) {
                            $y = $i;
                        } else {
                            $y = count($chars) - (strlen($pattern) - $x);
                        }
                        if ($this->decrementCharacter($chars[$y], $pattern[$i])) {
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

    /**
     *  increments a value by a single character or returns true is we need to carry.
     *
     * @param $value
     * @param $type
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function incrementCharacter(&$value, $type)
    {
        switch ($type) {
            case static::CHARACTER_DIGIT:
                if ($value == 9) {
                    $value = 0;

                    return true;
                } else {
                    $value++;

                    return false;
                }
                break;
            case static::CHARACTER_ALPHA_NUMERIC:
                $alpha = $this->alphabet;
                $count = strlen($alpha);
                $position = strpos($alpha, strtoupper($value));
                if ($position === false) {
                    throw new \Exception('Invalid character');
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

    /**
     *  decrements a value by a single character or returns true is we need to borrow.
     *
     * @param $value
     * @param $type
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function decrementCharacter(&$value, $type)
    {
        switch ($type) {
            case 'd':
                if ($value == 0) {
                    $value = 9;

                    return true;
                } else {
                    $value--;

                    return false;
                }
                break;
            case 'c':
                $alpha = strrev($this->alphabet);
                $count = strlen($alpha);
                $position = strpos($alpha, strtoupper($value));
                if ($position === false) {
                    throw  new \Exception('Invalid character found!');
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

    protected function replaceStuff($no)
    {
        $no = str_replace('<yyyy>', date('Y'), $no);
        $no = str_replace('<mm>', date('m'), $no);
        $no = str_replace('<dd>', date('d'), $no);
        $no = str_replace('<yy>', date('y'), $no);

        return $no;
    }
}
