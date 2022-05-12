<?php

namespace {
    use CatPaw\RaspberryPI\Attributes\GPIO;
    use function Amp\delay;

    function main(
        #[GPIO("12","write")] $set12
    ){
        $led = false;
        while(true){
            yield delay(1000);
            $led = !$led;
            yield $set12($led);
        }
    }
}