<?php
namespace Yearly_Timespan;

use \DateTime;
use \DateTimeZone;

/**
 * This is a DateTime Class with the time (and the year most of the time) set to 0000.
 */
class Yearly_Date extends DateTime {

	public function __construct( $time = 'now', $timezone = 'UTC' ) {
		parent::__construct( $time, new DateTimeZone( $timezone ) );
		$this->modify( self::get_clean_timestring( $this ) );
	}
	public static function get_clean_timestring( DateTime $date, $year = 0 ) {
		$month_day = $date->format( 'm-d' );
		$year = self::format_year($year);
		return "$year-$month_day 00:00:00";
	}
	public function set_month( $m ) {
		$this->modify( $this->format( "Y-$m-d 00:00:00" ) );
	}
	public function set_day( $d ) {
		$this->modify( $this->format( "Y-m-$d 00:00:00" ) );
	}
	public function get_year() {
		return intval( $this->format( 'Y' ) );
	}
	public function add_years( $years = 1 ) {
		$this->modify( "+$years years" );
		return $this;
	}

	public static function format_year( $year ){
		return sprintf( '%04d', $year );
	}

	public function to_date_time_object(){
		return new DateTime( $this->format('Y-m-d 00:00:00') );
	}

	/**
	 * Overwrites parent function.
	 * @todo: using UTC is not really clean here...
	 *
	 * @param [type] $format
	 * @param [type] $time
	 * @param string $timezone
	 * @return void
	 */
	public static function createFromFormat( $format, $time, $timezone = 'UTC' ) {
		$instance = parent::createFromFormat( $format, $time, new DateTimeZone( $timezone ) );
		return new Yearly_Date( self::get_clean_timestring( $instance ) );
	}
	public static function createFromDate( DateTime $date ) {
		return new Yearly_Date( self::get_clean_timestring( $date ) );
	}
	
	/**
	 * Undocumented function
	 *
	 * @param int|string $year
	 * @return void
	 * @todo rename!
	 * @see http://php.net/manual/en/datetime.createfromformat.php
	 */
	public function to_year( $year ) {
		return $this->format( "$year-m-d 00:00:00" );
	}

	/* public function __toString(){
		return "yearly ".$this->format("Y-m-d");
	} */
}



