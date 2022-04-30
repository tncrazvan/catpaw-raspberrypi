<?php

namespace {
    use CatPaw\RaspberryPI\Attribute\GPIO;
    use function Amp\delay;

    function main(
        #[GPIO(GPIO_HEADER12,GPIO_WRITE)] $set12
    ){
        $led = false;
        while(true){
            yield delay(1000);
            $led = !$led;
            yield $set12($led);
        }
    }
}