<?php


class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testWordGeneration()
    {
        $factory = Sequence\Factory::create();
        $format = 'CIT-221/###';
        $value = $factory->next($format);
        self::assertEquals($value, 'CIT-221/001', 'Word sequence generation failed');
    }

    public function testWordSkipping()
    {
        $factory = Sequence\Factory::create();
        $format = 'CIT-221/###';
        $set = false;
        $value = $factory->next($format, function () use (&$set) {
            if (!$set) {
                $set = true;

                return true;
            }

            return false;
        });
        self::assertEquals($value, 'CIT-221/002', 'Word sequence skipping failed');
    }

    public function testIncrementCharacter()
    {
        $factory = Sequence\Factory::create();
        $value = '9';
        $factory->incrementCharacter($value, \Sequence\Factory::CHARACTER_DIGIT);
        self::assertEquals($value, '0', 'Digit increment failed');

        $value = 'Z';
        $factory->incrementCharacter($value, \Sequence\Factory::CHARACTER_ALPHA_NUMERIC);
        self::assertEquals($value, '0', 'Alphanumeric increment failed');
    }

    public function testDecrementCharacter()
    {
        $factory = Sequence\Factory::create();
        $value = '9';
        $factory->decrementCharacter($value, \Sequence\Factory::CHARACTER_DIGIT);
        self::assertEquals($value, '8', 'Digit decrement failed');
        $value = 'Z';
        $factory->decrementCharacter($value, \Sequence\Factory::CHARACTER_ALPHA_NUMERIC);
        self::assertEquals($value, 'Y', 'Alphanumeric decrement failed');
    }
}
