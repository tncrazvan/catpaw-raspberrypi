<?php

namespace {
    use CatPaw\RaspberryPI\Attribute\GPIO;
    use function Amp\delay;

    function main(
        #[GPIO(GPIO::HEADER12,GPIO::WRITE)] $set12
    ){
        $led = false;
        while(true){
            yield delay(1000);
            $led = !$led;
            yield $set12($led);
        }
    }
}