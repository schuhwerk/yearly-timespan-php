<?php
namespace Yearly_Timespan;

Use \Timespan\Timespan;
Use DateTimeInterface;
Use DateTime;

class Named_Timespan extends Timespan {

	public $name;
	public $type;

	/**
	 * Optional extra metadata for custom/legacy callers.
	 * Known fields are declared as first-class properties below.
	 *
	 * @var array|null
	 */
	public $attrs;
	/** @var bool|null */
	public $current;
	/** @var bool|null */
	public $selected;
	/** @var array */
	public $data = array();
	/** @var string|null */
	public $short;
	/** @var string|null */
	public $long;
	/** @var bool|null */
	public $is_current;

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
	public function toArray(): array {
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
		$start = $this->start->format( 'Y-m-d' );
		$end   = $this->end->format( 'Y-m-d' );
		return "$start_column <= '$end' AND $end_column >= '$start'";
	}
}

?>
