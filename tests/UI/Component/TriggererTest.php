<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\TriggeredSignal;

class Triggerermock {
	use \ILIAS\UI\Implementation\Component\Triggerer;

	public function _appendTriggeredSignal(Component\Signal $signal, $event) {
		return $this->appendTriggeredSignal($signal, $event);
	}

	public function _withTriggeredSignal(Component\Signal $signal, $event) {
		return $this->withTriggeredSignal($signal, $event);
	}

	public function _setTriggeredSignal(Component\Signal $signal, $event) {
		return $this->setTriggeredSignal($signal, $event);
	}
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class ILIAS_UI_Component_TriggererTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->mock = new TriggererMock();
	}

	protected $signal_mock_counter = 0;
	protected function getSignalMock() {
		$this->signal_mock_counter++;
		return $this
			->getMockBuilder(Component\Signal::class)
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMockClassName("Signal_{$this->signal_mock_counter}")
			->getMock();
	}

	public function testStartEmpty() {
		$this->assertEquals([], $this->mock->getTriggeredSignals());
	}

	public function testAppendTriggeredSignalIsImmutable() {
		$signal = $this->getSignalMock();

<<<<<<< HEAD
		$mock = $this->mock->_appendTriggeredSignal($signal, "some_event");
=======
		$mock = $this->mock->_appendTriggeredSignal($signal, "click");
>>>>>>> e3b0948b48... UI: added tests for Triggerer
		$this->assertNotSame($mock, $this->mock);
	}

	public function testAppendTriggeredSignal() {
		$signal1 = $this->getSignalMock();
		$signal2 = $this->getSignalMock();
		$signal3 = $this->getSignalMock();

<<<<<<< HEAD
		$mock = $this->mock->_appendTriggeredSignal($signal1, "some_event");
		$mock2 = $this->mock
			->_appendTriggeredSignal($signal2, "some_event")
			->_appendTriggeredSignal($signal3, "some_event");

		$this->assertEquals([], $this->mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal1, "some_event")], $mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal2, "some_event"), new TriggeredSignal($signal3, "some_event")], $mock2->getTriggeredSignals());
=======
		$mock = $this->mock->_appendTriggeredSignal($signal1, "click");
		$mock2 = $this->mock
			->_appendTriggeredSignal($signal2, "click")
			->_appendTriggeredSignal($signal3, "click");

		$this->assertEquals([], $this->mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal1, "click")], $mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal2, "click"), new TriggeredSignal($signal3, "click")], $mock2->getTriggeredSignals());
>>>>>>> e3b0948b48... UI: added tests for Triggerer
	}

	public function testWithTriggeredSignalIsImmutable() {
		$signal = $this->getSignalMock();

<<<<<<< HEAD
		$mock = $this->mock->_withTriggeredSignal($signal, "some_event");
=======
		$mock = $this->mock->_withTriggeredSignal($signal, "click");
>>>>>>> e3b0948b48... UI: added tests for Triggerer

		$this->assertNotSame($mock, $this->mock);
	}

	public function testWithTriggeredSignal() {
		$signal1 = $this->getSignalMock();
		$signal2 = $this->getSignalMock();

<<<<<<< HEAD
		$mock = $this->mock->_withTriggeredSignal($signal1, "some_event");
		$mock2 = $mock->_withTriggeredSignal($signal2, "some_event");

		$this->assertEquals([new TriggeredSignal($signal1, "some_event")], $mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal2, "some_event")], $mock2->getTriggeredSignals());
=======
		$mock = $this->mock->_withTriggeredSignal($signal1, "click");
		$mock2 = $mock->_withTriggeredSignal($signal2, "click");

		$this->assertEquals([new TriggeredSignal($signal1, "click")], $mock->getTriggeredSignals());
		$this->assertEquals([new TriggeredSignal($signal2, "click")], $mock2->getTriggeredSignals());
>>>>>>> e3b0948b48... UI: added tests for Triggerer
	}

	public function testSetTriggeredSignal() {
		$signal1 = $this->getSignalMock();
		$signal2 = $this->getSignalMock();

<<<<<<< HEAD
		$this->mock->_setTriggeredSignal($signal1, "some_event");
		$this->mock->_setTriggeredSignal($signal2, "some_event");

		$this->assertEquals([new TriggeredSignal($signal2, "some_event")], $this->mock->getTriggeredSignals());
=======
		$this->mock->_setTriggeredSignal($signal1, "click");
		$this->mock->_setTriggeredSignal($signal2, "click");

		$this->assertEquals([new TriggeredSignal($signal2, "click")], $this->mock->getTriggeredSignals());
>>>>>>> e3b0948b48... UI: added tests for Triggerer
	}

	public function testWithResetTriggeredSignalIsImmutable() {
		$signal = $this->getSignalMock();

		$mock = $this->mock->withResetTriggeredSignals();

		$this->assertNotSame($mock, $this->mock);
	}

	public function testWithResetTriggeredSignal() {
		$signal1 = $this->getSignalMock();
		$signal2 = $this->getSignalMock();

		$mock = $this->mock
<<<<<<< HEAD
			->_appendTriggeredSignal($signal1, "some_event")
			->_appendTriggeredSignal($signal2, "some_event")
=======
			->_appendTriggeredSignal($signal1, "click")
			->_appendTriggeredSignal($signal2, "click")
>>>>>>> e3b0948b48... UI: added tests for Triggerer
			->withResetTriggeredSignals();

		$this->assertEquals([], $mock->getTriggeredSignals());
	}
<<<<<<< HEAD

	public function testGetTriggeredSignalsForNonRegisteredSignal() {
		$signals = $this->mock->getTriggeredSignalsFor("some_event");
		$this->assertEquals([], $signals);
	}

	public function testGetTriggeredSignals() {
		$signal1 = $this->getSignalMock();
		$signal2 = $this->getSignalMock();

		$mock = $this->mock
			->_appendTriggeredSignal($signal1, "some_event")
			->_appendTriggeredSignal($signal2, "some_event");

		$signals = $mock->getTriggeredSignalsFor("some_event");

		$this->assertEquals([$signal1, $signal2], $signals);
	}
=======
>>>>>>> e3b0948b48... UI: added tests for Triggerer
}
