<?php

namespace CatPaw\RaspberryPI\Attribute;

use Amp\File\File;
use Amp\LazyPromise;
use Amp\Promise;
use CatPaw\Attribute\Entry;
use CatPaw\Attribute\Interface\AttributeInterface;
use CatPaw\Attribute\Trait\CoreAttributeDefinition;
use CatPaw\RaspberryPI\Service\GPIOService;
use Psr\Log\LoggerInterface;
use ReflectionParameter;

use function Amp\File\openFile;

#[\Attribute]
class GPIO implements AttributeInterface {
	use CoreAttributeDefinition;

	public function __construct(
		private int $pin,
		private int $direction,
	) {
	}

	private GPIOService     $service;

	#[Entry]
	private function main(
		GPIOService     $service,
	) {
		$this->service = $service;
	}


	/**
	 * @inheritDoc
	 */
	public function onParameter(ReflectionParameter $reflection, mixed &$value, mixed $http): Promise {
		return new LazyPromise(function() use (&$value) {
			$value = yield $this->service->export($this->pin,$this->direction);
		});
	}
}