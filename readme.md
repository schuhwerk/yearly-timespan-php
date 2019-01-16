Collection of PHP classes to work with yearly repeating timespans.

Heavily using [smhg]s(https://github.com/smhg/) [date-timespan](date-timespan).

## Installation
composer require tivus/yearly-timespan


## Classes

The examples shown here are additive (you need the previous ones for the later ones to work).

### Yearly Date

Extends the DateTime object. The year is set to 0.

```php
    use Timespan\Timespan;
    use Yearly_Timespan\Yearly_Date;
    use Yearly_Timespan\Yearly_Timespan;
    
    $july = Yearly_Date::createFromFormat( 'd.m', '15.07' );
    $feb  = Yearly_Date::createFromFormat( 'd.m', '04.02' );
```

### Yearly Timespan

Extends the [smgh/timespan Class](https://github.com/smhg/date-timespan-php#timespan)

```php
    $winter_term = new Yearly_Timespan(
        $july, // start, Yearly_Timespan object.
        $feb, // end. Yearly_Timespan object.
        'Winterterm', // name.
        'Term' // type.
    );
    $summer_term = new Yearly_Timespan(
        $feb,
        $july,
        'Summerterm',
        'Term'
    );

    // cast to a Timespan (with a name and type)
    $current_winter_term = $winter_term->cast_to_year( new \DateTime() );

    //check if a (regular) Date is contained by a Yearly_Timespan.
    if ( $winter_term->contains( \DateTime::createFromFormat( 'd.m.Y', '04.02.2019' ))) {
        echo '04.02.2019 is in the Winterterm. ';
    }

    // check if a (regular) Timespan is overlapped by a Yearly_Timespan.
    $project_week = new Timespan(
        \DateTime::createFromFormat( 'd.m.Y', '14.01.2019' ),
        \DateTime::createFromFormat( 'd.m.Y', '18.01.2019' )
    );

    if ( $winter_term->overlaps( $project_week ) ){
        echo 'The Projekt Week overlaps the Winterterm. ';
    }
```

### Yearly Timespan Collection

```php
    // Create a collection of Yearly_Timespans.
    $yearly_collection = new Yearly_Timespan_Collection();

    // Add Yearly_Timespans to the Collection.
    $yearly_collection[] = $winter_term;
    $yearly_collection[] = $summer_term;

    // Select a Yearly_Timespan (Winterterm in this case) and cast it to a year.
    $current_term = $yearly_collection->select( new DateTime('2020-01-01') )->get();

    // Get the timespan (not yearly) for the Summerterm 2020.
    $next_term = $yearly_collection->next()->get();

    // Create a collection of timespans (not yearly).
    $timespan_collection   = new \Timespan\Collection([
        new Timespan( new DateTime( '2015-02-02' ), new DateTime( '2015-07-06' ) ),
        new Timespan( new DateTime( '2016-06-02' ), new DateTime( '2017-04-06' ) )
    ]);

    /**
     * Create an array of specific timespans (form the yearly ones) that overlap our new $timespan_collection.
     * The new \Timespan\Collection Object ($navigation) has a data attribute that references all Timespans (from the $timespan_collection)
     * (via an array index), that overlap the newliy create specific timespans ( $navigation ).
     */
	$navigation = $yearly_collection->group_timespans_by_collection( $timespan_collection );

    // Get an array of summerterms (specific timespans, not yearly ones) that overlap the given timespan.
    $many_terms = $yearly_collection->filter('any', 'Summerterm')->get_from_timespan(
        new Timespan(
            new DateTime('1990-01-01'),
            new DateTime('2010-01-01')
        )
    );

```