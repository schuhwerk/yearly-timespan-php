<?php

namespace Yearly_Timespan;
use PHPUnit\Framework\TestCase;

use \DateTime;
use \Timespan\Timespan;

class Yearly_Timespan_Test extends TestCase {
	/**
	 * Return a yearly timespan. Used in multiple test-cases.
	 *
	 * @return Yearly_Timespan
	 */
	public function make_yearly_timespan(){
		return new Yearly_Timespan(
			Yearly_Date::createFromFormat( 'd.m', '02.07' ), // start
			Yearly_Date::createFromFormat( 'd.m', '01.02' ), // end
			'Wintersemester',
			'Offset-Semester'
		);
	}

	public function test_timespan() {
		$this->assertEquals(1,1);
	}

	public function test_cast_to_year_int(){
		$yts = $this->make_yearly_timespan();
		$ts  = $yts->cast_to_year( 2018 );
		$this->assertEquals( '2019', $ts->end->format( 'Y' ) );
	}

	public function test_cast_to_year_date(){
		$yts = $this->make_yearly_timespan();
		$ts  = $yts->cast_to_year( new DateTime('2018-01-01') );
		$this->assertEquals( '2017', $ts->start->format( 'Y' ) );
	}

	/**
	 * Check if a Yearly timespan contains a date.
	 *
	 * @return void
	 */
	public function test_contains(){
		$yts = $this->make_yearly_timespan();
		$this->assertEquals( 
			2, 
			$yts->contains(\DateTime::createFromFormat( 'd.m.Y', '15.01.2019'))
		);
	}
	

	public function test_overlap(){
		
		$yts = $this->make_yearly_timespan();
		$ts = new Timespan( 
			\DateTime::createFromFormat( 'd.m.Y', '15.01.2019'),
			\DateTime::createFromFormat( 'd.m.Y', '15.02.2019')
		);

		$this->assertEquals(1, $yts->overlaps( $yts ));
		$this->assertEquals(2, $yts->overlaps( $ts ));
	}
}