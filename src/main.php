<?php

namespace {

    use CatPaw\Attributes\Http\PathParam;
    use CatPaw\Attributes\StartWebServer;
    use CatPaw\RaspberryPI\Attribute\GPIO;
    use CatPaw\Tools\Helpers\Route;

	#[StartWebServer()]
	function main() {
		Route::get("/red/{state}",fn(
			#[GPIO(GPIO::HEADER12,GPIO::WRITE)] $set18,
			#[PathParam] bool $state
		)=>$set18($state));
		echo Route::describe();
	}
}