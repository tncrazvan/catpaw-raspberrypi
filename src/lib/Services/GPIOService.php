<?php

namespace CatPaw\RaspberryPI\Services;

use Amp\File\File;
use function Amp\File\openFile;
use Amp\LazyPromise;
use Amp\Promise;
use CatPaw\Attributes\Service;
use CatPaw\RaspberryPI\Exceptions\GPIOException;

#[Service]
class GPIOService {
    private const READ = 0;
    private const WRITE = 1;
    private const HEADER7 = 4;
    private const HEADER11 = 17;
    private const HEADER12 = 18;
    private const HEADER13rv1 = 21;
    private const HEADER13rv2 = 27;
    private const HEADER15 = 22;
    private const HEADER16 = 23;
    private const HEADER18 = 25;
    private const HEADER22 = 25;

    public function __construct() {
    }

    /**Builds a function that will interact with a pin.
     * @param string $pin pin (or header if you will) number.<br/>
     * The <b>GPIOService::HEADER*</b> constants exist for ease of use.
     * @param string $direction direction of the operation.<br/>
     * Use <b>GPIOService::READ</b> to read from the pin, or <b>GPIOService::WRITE</b> to write to the pin.
     * @return Promise<Closure> the resulting function that will interact with the given pin when called.<br/>
     * If the direction is <b>GPIOService::READ</b>, the closure will take no parameters and return a truthy value.<br/>
     * If the direction is <b>GPIOService::WRITE</b>, the closure will take 1 boolean parameter (the value to write), and return void.
     */
    public function export(string $pin, string $direction): Promise {
        return new LazyPromise(function() use ($pin, $direction) {
            $originalPin = $pin;
            $pin = match ($pin) {
                '7' => self::HEADER7,
                '11' => self::HEADER11,
                '12' => self::HEADER12,
                '13rv1' => self::HEADER13rv1,
                '13rv2', '13' => self::HEADER13rv2,
                '15' => self::HEADER15,
                '16' => self::HEADER16,
                '18' => self::HEADER18,
                '22' => self::HEADER22,
                default => -1,
            };

            if (-1 === $pin) {
                throw new GPIOException("Pin name must be one of the following: `7`,`11`,`12`,`13rv1`,`13`,`13rv2`,`15`,`16`,`18`,`22`. Received '$originalPin'.");
            }

            $originalDirection = $direction;

            $direction = match ($direction) {
                "read" => self::READ,
                "write" => self::WRITE,
                default => -1
            };

            if (-1 === $pin) {
                throw new GPIOException("Direction must be either 'read' or 'write', '$originalDirection' received.");
            }

            # Exports pin to userspace and sets pin as an output
            @shell_exec('echo "'.$pin.'" > /sys/class/gpio/export');
            @shell_exec('echo "'.($direction > 0 ? 'out' : 'in').'" > /sys/class/gpio/gpio'.$pin.'/direction');


            /** @var File $gpio */
            $gpio = yield openFile('/sys/class/gpio/gpio'.$pin.'/value', $direction > 0 ? 'a' : 'r');


            if ($direction > 0) {
                $value = function(bool $state) use ($gpio): Promise {
                    return new LazyPromise(function() use ($state, $gpio) {
                        if ($state) {
                            # Sets pin to high
                            //'echo "1" > /sys/class/gpio/gpio'.$this->pin.'/value';
                            yield $gpio->write('1');
                        } else {
                            # Sets pin to low
                            //'echo "0" > /sys/class/gpio/gpio'.$this->pin.'/value';
                            yield $gpio->write('0');
                        }
                    });
                };
            } else {
                $value = function() use ($gpio): Promise {
                    return new LazyPromise(function() use ($gpio) {
                        return yield $gpio->read();
                    });
                };
            }

            return $value;
        });
    }
}