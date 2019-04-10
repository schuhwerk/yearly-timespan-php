<?php
namespace Yearly_Timespan;

Use \Timespan\Timespan;
Use DateTimeInterface;
Use DateTime;

class Named_Timespan extends Timespan {

	public $name;
	public $type;

	/**
	 * Undocumented variable
	 *
	 * @var array contains things like 'current' or 'selected'.
	 */
	public $attrs;

	public function set_name( $name ) {
		$this->name = $name;
		return $this;
	}

	public function set_type( $type ) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Overwrites function in parent class
	 *
	 * @return void
	 */
	public function toArray(){
		return array_merge(
			parent::toArray(),
			['name' => $this->name,
			'type' => $this->type]
		);
	}

	public static function createFromArray( array $arr ){
		$span = new self( 
			new DateTime( $arr['start']),
			new DateTime( $arr['end'] )
		);
		$span->set_name($arr['name']);
		$span->set_type($arr['type']);
		return $span;
	}

	/**
	 * Undocumented function
	 *
	 * @param [type] $start_column
	 * @param [type] $end_column
	 * @param DateTimeInterface $start default is the start date of the current instance.
	 * @param DateTimeInterface $end default is the end date of the current instance.
	 * @return void
	 */
	public function to_query( $start_column, $end_column, $start = '', $end = '' ) {
		$start = ( $start ) ? $start : $this->start;
		$end   = ( $end ) ? $end : $this->end;
		$start = $timespan->start->format( 'Y-m-d' );
		$end   = $timespan->end->format( 'Y-m-d' );
		return "$start_column <= '$end' AND $end_column >= '$start'";
	}
}

?>