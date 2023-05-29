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
        return ' @ds("this is a function to check!")';
    }
}
