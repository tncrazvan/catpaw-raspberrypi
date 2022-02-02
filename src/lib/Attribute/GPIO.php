<?php
namespace CatPaw\RaspberryPI\Attribute;

use Amp\File\File;
use Amp\LazyPromise;
use Amp\Promise;
use CatPaw\Attributes\Entry;
use CatPaw\Attributes\Interfaces\AttributeInterface;
use CatPaw\Attributes\Traits\CoreAttributeDefinition;
use CatPaw\Http\HttpContext;
use Psr\Log\LoggerInterface;
use ReflectionParameter;

use function Amp\File\openFile;

#[\Attribute]
class GPIO implements AttributeInterface{
    use CoreAttributeDefinition;
    public const READ = 0;
    public const WRITE = 1;
    public const HEADER7 = 4;
    public const HEADER11 = 17;
    public const HEADER12 = 18;
    public const HEADER13rv1 = 21;
    public const HEADER13 = 27;
    public const HEADER15 = 22;
    public const HEADER16 = 23;
    public const HEADER18 = 25;
    public const HEADER22 = 25;

    public function __construct(
        private int $pin,
        private int $direction,
    ) {}

    private LoggerInterface $logger;

    #[Entry]
    private function main(
        LoggerInterface $logger
    ){
        $this->logger = $logger;
    }


    private function export():void{
        @shell_exec('echo "'.$this->pin.'" > /sys/class/gpio/export');
        @shell_exec('echo "'.($this->direction>0?'out':'in').'" > /sys/class/gpio/gpio'.$this->pin.'/direction');
    }


    /**
     * @inheritDoc
     */
    public function onParameter(ReflectionParameter $reflection, mixed &$value, false|HttpContext $http): Promise{
        return new LazyPromise(function() use(&$value){
            
            # Exports pin to userspace and sets pin as an output
            $this->export();

            /** @var File $gpio */
            $gpio = yield openFile('/sys/class/gpio/gpio'.$this->pin.'/value',$this->direction>0?'a':'r');


            if($this->direction>0){
                $value = function(bool $state) use($gpio):Promise{
                    return new LazyPromise(function() use($state,$gpio){   
                        if($state)
                        # Sets pin to high
                        //'echo "1" > /sys/class/gpio/gpio'.$this->pin.'/value';
                        yield $gpio->write('1');
                        else
                        # Sets pin to low
                        //'echo "0" > /sys/class/gpio/gpio'.$this->pin.'/value';
                        yield $gpio->write('0');
                    });
                };
            }else{
                $value = function() use($gpio):Promise{
                    return new LazyPromise(function() use($gpio){
                        return yield $gpio->read();
                    });
                };
            }
        });
    }
}