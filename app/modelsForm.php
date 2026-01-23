<?php
namespace App;

/**
 *
 */
class modelsForm
{
	private $models = null;
	private $request = null;

	__construct($models, $request = null)
	{

	}

	public function getFormAttribute()
	{
		if (property_exists($this->models, 'modelsForm')) {
			// code...
		}
	}
}
