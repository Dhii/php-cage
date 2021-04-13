<?php


namespace Me\OtherModule;


use Me\Module\ThingA;

class ThingB
{
    /**
     * @var ThingA
     */
    private ThingA $thingA;

    public function __construct(ThingA $thingA)
    {
        $this->thingA = $thingA;
    }
}