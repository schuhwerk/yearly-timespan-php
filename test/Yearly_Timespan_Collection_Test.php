<?php
namespace Yearly_Timespan;

use \DateTime;
use \Timespan\Timespan;
use PHPUnit\Framework\TestCase;

class Yearly_Date_Test extends TestCase {

	public function test_constructor_exception() {
		$this->expectException( \InvalidArgumentException::class );
		$collection = new Yearly_Timespan_Collection( [ new Timespan( new DateTime(), new DateTime() ) ] );
	}

	public function test_filter() {
		$collection = $this->make_collection();
		$collection->filter( 'semester' );
		$this->assertCount( 2, $collection );
	}

	public function test_cast_to_year() {
		$collection = $this->make_collection();
		$result     = $collection->filter( 'Semester' )->select( new DateTime( '2015-02-02' ) )->get();
		$this->assertEquals( 'Wintersemester', $result->name );
		$this->assertEquals( '2014-07-15', $result->start->format( 'Y-m-d' ) );
	}


	public function test_get_from_timespan() {
		$collection = $this->make_collection();
		$timespan   = new Timespan( new DateTime( '1990-02-02' ), new DateTime( '2015-02-02' ) );
		$results    = $collection->filter( 'Semester' )->get_from_timespan( $timespan );
		$this->assertEquals( 51, count( $results ) );
	}

	public function test_group_timespans_by_collection() {
		$yearly  = $this->make_collection()->filter( 'Semester' );
		$dates   = new \Timespan\Collection();
		$dates[] = new Timespan( new DateTime( '2015-02-02' ), new DateTime( '2015-07-06' ) );
		$dates[] = new Timespan( new DateTime( '2016-06-02' ), new DateTime( '2017-04-06' ) );
		$dates[] = new Timespan( new DateTime( '2016-06-02' ), new DateTime( '2017-04-06' ) );

		$navigation = $yearly->group_timespans_by_collection( $dates );

		$this->assertEquals( $navigation[0]->data, [ 0 ] );
		$this->assertEquals( $navigation[5]->data, [ 1, 2 ] );

	}


	/**
	 * Don't change things here (test dependencies).
	 *
	 * @return Yearly_Timespan_Collection
	 */
	public function make_collection() {
		$timespans = [
			new Yearly_Timespan(
				Yearly_Date::createFromFormat( 'd.m', '15.07' ), // start
				Yearly_Date::createFromFormat( 'd.m', '04.02' ), // end
				'Wintersemester',
				'Semester'
			),
			new Yearly_Timespan(
				Yearly_Date::createFromFormat( 'd.m', '02.07' ), // start
				Yearly_Date::createFromFormat( 'd.m', '01.02' ), // end
				'Wintersemester',
				'Offset-Semester'
			),
			new Yearly_Timespan(
				Yearly_Date::createFromFormat( 'd.m', '5.2' ), // start
				Yearly_Date::createFromFormat( 'd.m', '14.7' ), // end
				'Sommersemester',
				'Semester'
			),
			new Yearly_Timespan(
				Yearly_Date::createFromFormat( 'd.m', '2.2' ), // start
				Yearly_Date::createFromFormat( 'd.m', '1.7' ), // end
				'Sommersemester',
				'Offset-Semester'
			),
		];
		return new Yearly_Timespan_Collection( $timespans );

	}
}
