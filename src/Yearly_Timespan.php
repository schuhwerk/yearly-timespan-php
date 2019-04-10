<?php
namespace Yearly_Timespan;

use \Timespan\Timespan;

use \DateTime;
use \DateTimeInterface;
use \DateInterval;
use \DatePeriod;
use \ArrayObject;
use \DateTimeZone;

class Yearly_Timespan extends Named_Timespan {

	/**
	 * Undocumented variable
	 *
	 * @var Yearly_Date
	 */
	public $start;

	/**
	 * Undocumented variable
	 *
	 * @var Yearly_Date
	 */
	public $end;

	/**
	 * Undocumented function
	 *
	 * @todo: add support for multiyear timespans.
	 *
	 * @param DateTimeInterface|Yearly_Date $start
	 * @param DateTimeInterface|Yearly_Date $end
	 * @param [type]               $name
	 * @param string               $type
	 */
	public function __construct( DateTimeInterface $start, DateTimeInterface $end, $name, $type = '' ) {

		if ( $this->count_years( $start, $end ) > 1 ) {
			die( 'This class currently only supports timespans of less than a year.' );
		}

		$this->start = Yearly_Date::createFromDate( $start );
		$this->end   = Yearly_Date::createFromDate( $end );
		$this->name  = $name;
		$this->type  = $type;
		$this->check_over_years();
	}

	public function count_years( DateTimeInterface $start, DateTimeInterface $end ) {
		$interval = $start->diff( $end );
		return \intval( $interval->format( 'y' ) );
	}

	/**
	 * Check if the current timespan overlaps the year-border (dec-jan).
	 * If that is the case, the second
	 *
	 * @return void
	 */
	private function check_over_years() {
		if ( $this->end < $this->start
			|| $this->end == $this->start ) {
			$this->end = $this->end->add_years( 1 );
		}
	}

	/**
	 * Check if the current timespan instance has a certain type.
	 * (case insensitive matching)
	 * Pass 'any' to match any type.
	 *
	 * @param string $type
	 * @return boolean
	 */
	public function has( string $thing, string $contains ) {
		if ( 'any' === $contains ) {
			return true;
		}
		if ( $thing == 'date' ) {
			return $this->contains( $contains );
		}
		if ( strcasecmp( $this->$thing, $contains ) == 0 ) {
			return true;
		}
		return false;
	}

	public function matches( $type = 'any', $name = 'any', $date = 'any' ) {
		return ( $this->has( 'type', $type ) && $this->has( 'name', $name ) && $this->has( 'date', $date ) );
	}

	public static function create_from_timespan( Timespan $timespan, $name = '', $type = '' ) {
		return new Yearly_Timespan(
			Yearly_Date::createFromDate( $timespan->start ),
			Yearly_Date::createFromDate( $timespan->end ),
			$name,
			$type
		);
	}

	public static function createFromArray( array $arr ) {
		return new self(
			new DateTime( $arr['start'] ),
			new DateTime( $arr['end'] ),
			$arr['name'],
			$arr['type']
		);
		// return $span;
	}

	/**
	 * Create a Yearly Timespan from an array.
	 *
	 * @todo quite low performance, lots of objects.
	 *
	 * @param array $arr
	 * @return void
	 */
	/*
	  public static function createFromArray( array $arr ){

		return new self::__construct();
		return self::create_from_timespan(
			parent::createFromArray($arr), $arr['name'], $arr['type']
		);
	} */

	/**
	 * Cast an "abstract" Yearly_Timespan to a new "specific" (not yearly) Timespan.
	 *
	 * @param DateTimeInterface|int $date a specific DateTime of just a year.
	 * @return Named_Timespan
	 */
	public function cast_to_year( $date ) {
		/** there is a special case if over_year is true */
		if ( $date instanceof DateTime ) {
			$year     = (int) $date->format( 'Y' );
			$contains = $this->contains( $date );
			if ( 2 === $contains ) {
				$year--;
			}
		} else {
			$year = $date;
		}

		$ts = new Named_Timespan(
			$this->start->to_date_time_object()->modify( "+$year years" ),
			$this->end->to_date_time_object()->modify( "+$year years" )
		);

		return $ts->set_name( $this->name )->set_type( $this->type );

	}

	private function add_years( int $years ) {
		$this->start->modify( "+$years years" );
		$this->end->modify( "+$years years" );
		return $this;
	}

	/**
	 * Returns (bigger 0) if the timespan contains $date otherwise (0).
	 * Returns 1: The event is in the first year.
	 * Returns 2: The event is in the second year.
	 *
	 * @param DateTimeInterface $date
	 * @return int
	 */
	public function contains( DateTimeInterface $date ) {
		if ( $date instanceof DateTimeInterface ) {
			$date = Yearly_Date::createFromDate( $date );
		} else {
			$date = clone ($date);
		}
		/**
		 * @var Yearly_Date $date
		 */
		if ( parent::contains( $date ) ) {
			return 1; // matches year 0.
		} elseif ( parent::contains( $date->add_years( 1 ) ) ) {
			return 2; // matches year 1. event from dec to jan machtes jan.
		} else {
			return 0; // doesn't match.
		}
	}

	public function overlaps( \Timespan\Timespan $span ) {
		if ( $span instanceof Yearly_Timespan ) {
			$compare = clone $span;
			if ( parent::overlaps( $compare ) ) {
				return 1; // matches year 0.
			} elseif ( parent::overlaps( $compare->add_years( 1 ) ) ) {
				return 2; // matches year 1.
			} else {
				return 0; // doesn't match.
			}
		} else {
			/**
			 * $span is a Timespan (not a Yearly_Timespan).
			 * Convert $span to Yearly_Timespan and call again.
			 */
			return $this->overlaps( self::create_from_timespan( $span ) );
		}

	}
}

