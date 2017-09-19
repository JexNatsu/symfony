<?php

namespace SG\PlatformBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MessagePostEvent extends Event{
	protected $message;
	protected $user;

	public function _construct($message, UserInterface $user){
		$this->message = $message;
		$this->user = $user;
	}

	public function getMessage(){
		return $this->message;
	}

	public function setMessage($message){
		return $this->message = $message;
	}

	public function getUser(){
		return $this->user;
	}
}
