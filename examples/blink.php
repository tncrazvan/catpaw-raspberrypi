<?php

namespace {
    use CatPaw\RaspberryPI\Attribute\GPIO;
    use function Amp\delay;

    function main(
        #[GPIO(GPIO::HEADER12,GPIO::WRITE)] $set18
    ){
        $led = false;
        while(true){
            yield delay(1000);
            $led = !$led;
            yield $set18($led);
        }
    }
}