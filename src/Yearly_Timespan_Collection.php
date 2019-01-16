<?php

namespace Yearly_Timespan;

use \DateTime;
use \Timespan\Collection;
use \Timespan\Timespan;

/**
 * Defined by a start and an end month and day.
 * 
 * Yearly Timespans (called abstract timespans here) don't have a year. So the Yearly_Timespan (day.month) 1.1 - 1.2 overlaps
 * the Date 10.1.2010 and the Date 10.1.2011, ...
 *
 * Things this class does:
 * - Cast an (anstract) Summerterm (5.2 - 14.7) to 2018 -> (5.2.2018 - 14.7.2018)
 * - Todays date is 6.2.2020. Which (abstract timespans) (of type semester) overlap this Date?
 * - What is the next (concrete) timespan of the timespan mentioned above?
 * - What concrete Timespans (derived from abstract Timespans) overlap the Timespan between 1.1.1950 and 2.2.2020?
 */
class Yearly_Timespan_Collection extends \Timespan\Collection {

	public $selected_span = 0;

	public $selected_year = 0;

	public function __construct( array $mixed = null ) {
		parent::__construct( $mixed );
		foreach ( $this as $key => $span ) {
			if ( ! $span instanceof Yearly_Timespan ) {
				throw new \InvalidArgumentException( 'Please pass an array of Yearly_Timespan Instances.' );
			}
		}
		$this->sort(); // sorts the Timespans by start-date.
	}

	/**
	 * Filtering removes elements from the array ($this)
	 * if not all parameters are true.
	 * todo: do this could probbaly via array_filter?
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $date
	 * @return Yearly_Timespan_Collection
	 */
	public function filter( $type = 'any', $name = 'any', $date = 'any' ) {
		$tmp = array();
		foreach ( $this as $key => $span ) {
			/**
			 * @var Yearly_Timespan $span
			 */
			if ( $span->matches( $type, $name, $date ) ) {
				$tmp[] = $span;
			}
		}
		$this->exchangeArray( $tmp );
		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * @param DateTime $date
	 * @return Yearly_Timespan_Collection
	 */
	public function select( DateTime $date ) {
		$this->selected_year = intval( $date->format( 'Y' ) );
		foreach ( $this as $key => $span ) {
			/**
			 * @var Yearly_Timespan $span
			 */
			$contains = $span->contains( $date );
			if ( $contains ) {
				$this->selected_span = $key;
				if ( 2 == $contains ) { // matched a timespan which starts in prev year.
					$this->selected_year--;
				}
				return $this;
			}
		}
		return $this;
	}

	/**
	 * Returns timespans that are selected with the select function.
	 *
	 * @return \Timespan\Timespan
	 */
	public function next( $i = 1 ) {
		return $this->move( $i );
	}

	/**
	 * Returns timespans that are selected with the select function.
	 *
	 * @return \Timespan\Timespan
	 */
	public function previous( $i = -1 ) {
		return $this->move( $i );
	}

	public function move( int $i ) {
		$this->selected_year += floor( ( $this->selected_span + $i ) / count( $this ) );
		$this->selected_span  = $this->truemod( ( $this->selected_span + $i ), count( $this ) );
		// echo $this->selected_year . $this->m[ $this->selected_span ] . "\n";
		return $this;
	}

	/**
	 * The PHP Modulo operator is odd for negative numbers.
	 * PHP: -10 % 30 = -10
	 * This: truemod( -10, 30 ) = 20
	 *
	 * @see https://stackoverflow.com/questions/4409281/how-is-13-64-13-in-php
	 *
	 * @return int
	 */
	private function truemod( int $x, int $n ) {
		$r = $x % $n;
		return ( $r < 0 ) ? ( $r + abs( $n ) ) : $r;
	}

	/**
	 * Returns timespans that are selected with the select function.
	 *
	 * @return \Timespan\Timespan
	 */
	public function get() {
		if ( 0 === $this->selected_year ){
			throw new Exception( 'Select a Year with the select() function first!' );
		}
		return $this[ $this->selected_span ]->cast_to_year( $this->selected_year );
	}

	/**
	 * Get all specific Timespans from the the current abstact Timespans (yearly)
	 * that overlap the given $search_span.
	 *
	 * @param Timespan $search_span
	 * @param DateTime $selected If set, the newly created spans that contain the given date will have ->selected set true.
	 * @return \Timespan\Collection
	 */
	public function get_from_timespan( Timespan $search_span, DateTime $selected = null ) {
		$output_spans = [];
		$more         = true;
		while ( $more ) {
			/**
			 * @var Yearly_Timespan $span
			 */
			foreach ( $this as $key => $span ) {
				$new_span = $span->cast_to_year( $search_span->start );
				if ( $new_span->overlaps( $search_span ) ) {
					if ( $selected && $new_span->contains( $selected ) ) {
						$new_span->selected = true;
					}
					$output_spans[] = $new_span;
				} else {
					$more = false;
				}
			}
			$search_span->start->modify( '+1 year' );
		}
		$coll = new Collection( $output_spans );
		return $coll->sort();
	}

	/**
	 * You have a set of concrete input timespans (that are not related to the yearly timspans).
	 * Returns all overlapping concrete yearly timespans with a "data"-key which contains 
	 * the indexes of the overlapping input timespans.
	 *
	 * @todo: remove empty?
	 *
	 * @return void
	 */
	public function group_timespans_by_collection( \Timespan\Collection $timespans ) {
		foreach ( $timespans as $timespan ) {
			$start = ( ! isset( $start ) || $timespan->start < $start ) ? $timespan->start : $start;
			$end   = ( ! isset( $end ) || $timespan->end > $end ) ? $timespan->end : $end;
		}
		$results = $this->get_from_timespan( new Timespan( clone $start, clone $end ) );

		$remove = array();
		/**
		 * @see https://stackoverflow.com/questions/1949259/how-do-you-remove-an-array-element-in-a-foreach-loop
		 */
		foreach ( $results as $result_key => &$result ) {
			/**
			 * @var \Timespan\Timespan $result
			 */
			foreach ( $timespans as $key => $myspan ) {
				if ( ! isset( $result->data ) ) {
					$result->data = [];
				}
				if ( $result->overlaps( $myspan ) ) {
					$result->data[] = $key;
				}
			}
		}
		return $results;
	}

}


