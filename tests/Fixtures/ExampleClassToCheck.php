<?php

namespace Fixtures;

class ExampleClassToCheck
{
    public function function1()
    {
        ds('this is a function to check!');
    }

    public function function2()
    {
        return ' @ds("this is a directive to check!")';
    }

    public function function3()
    {
        dd('this is a function to check!');
    }

    public function function4()
    {
        return ' @dd("this is a directive to check!")';
    }

    public function function5()
    {
        return collect()->ds("this is a collect function to check!");
    }

    public function function6()
    {
        dump('this is a function to check!');
    }

    public function function7()
    {
        dd('this is a function to check!');
    }

    public function function8()
    {
        //dd('this is a function to check!');
    }
}
