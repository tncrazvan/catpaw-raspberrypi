<?php

namespace CatPaw\RaspberryPI\Attributes;

use Amp\LazyPromise;
use Amp\Promise;
use Attribute;
use CatPaw\Attributes\Entry;
use CatPaw\Attributes\Interfaces\AttributeInterface;
use CatPaw\Attributes\Traits\CoreAttributeDefinition;
use CatPaw\RaspberryPI\Services\GPIOService;
use ReflectionParameter;

#[Attribute]
class GPIO implements AttributeInterface {
    use CoreAttributeDefinition;

    /**
     * Mark a variable as a GPIO operation.
     * @param string $pin       the pin to interact with, value must be one of the following: `7`,`11`,`12`,`13rv1`,`13`,`13rv2`,`15`,`16`,`18`,`22`.
     * @param string $direction value must be either '<b>read</b>' or '<b>write</b>'.<br/>
     *                          This will define the direction in which the signal travels.<br/>
     *                          <ul>
     *                          <li>
     *                          '<b>read</b>: the marked variable will become a function that returns the value of the pin.<br/>
     *                          The function accepts no parameters.
     *                          </li>
     *                          <li>
     *                          '<b>write</b>: the marked variable will become a function that will set the value of the pin.<br/>
     *                          The function requires 1 boolean parameter, that is to say: the value to send to the pin.
     *                          </li>
     *                          </ul>
     */
    public function __construct(
		private string $pin,
		private string $direction,
	) {
    }

    private GPIOService $service;

    #[Entry]
	private function main(
		GPIOService $service,
	) {
	    $this->service = $service;
	}


    /**
     * @inheritDoc
     */
    public function onParameter(ReflectionParameter $reflection, mixed &$value, mixed $http): Promise {
        return new LazyPromise(function() use (&$value) {
            $value = yield $this->service->export($this->pin, $this->direction);
        });
    }
}