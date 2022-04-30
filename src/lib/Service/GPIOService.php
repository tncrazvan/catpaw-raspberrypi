<?php

namespace CatPaw\RaspberryPI\Service;

use Amp\File\File;
use Amp\LazyPromise;
use Amp\Promise;
use CatPaw\Attribute\Service;
use function Amp\File\openFile;

#[Service]
class GPIOService {
	public function __construct() { }

	/**Builds a function that will interact with a pin.
	 * @param int $pin pin (or header if you will) number.<br/>
	 * The <b>GPIOService::HEADER*</b> constants exist for ease of use.
	 * @param int $direction direction of the operation.<br/>
	 * Use <b>GPIOService::READ</b> to read from the pin, or <b>GPIOService::WRITE</b> to write to the pin.
	 * @return Promise<Closure> the resulting function that will interact with the given pin when called.<br/>
	 * If the direction is <b>GPIOService::READ</b>, the closure will take no parameters and return a truthy value.<br/>
	 * If the direction is <b>GPIOService::WRITE</b>, the closure will take 1 boolean parameter (the value to write), and return void.
	 */
	public function export(int $pin, int $direction): Promise {
		return new LazyPromise(function() use ($pin, $direction) {

			# Exports pin to userspace and sets pin as an output
			@shell_exec('echo "'.$pin.'" > /sys/class/gpio/export');
			@shell_exec('echo "'.($direction > 0 ? 'out' : 'in').'" > /sys/class/gpio/gpio'.$pin.'/direction');


			/** @var File $gpio */
			$gpio = yield openFile('/sys/class/gpio/gpio'.$pin.'/value', $direction > 0 ? 'a' : 'r');


			if($direction > 0) {
				$value = function(bool $state) use ($gpio): Promise {
					return new LazyPromise(function() use ($state, $gpio) {
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