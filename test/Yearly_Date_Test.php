<?php
use PHPUnit\Framework\TestCase;

class Yearly_Date_Test extends TestCase {

	public function test_constructor(){

		$yearly_date = \Yearly_Timespan\Yearly_Date::createFromFormat( 'd.m', '15.07' ); // start
		
		$this->assertEquals(0, $yearly_date->get_year());
		$this->assertEquals('0000-07-15 00:00:00', $yearly_date->format('Y-m-d H:i:s'));

	}
}