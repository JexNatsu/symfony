<?php

namespace SG\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SGUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
