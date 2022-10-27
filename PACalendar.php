<?php
/**
 * Description of PACalendar
 *
 * @author WingedGeek
 */
class PACalendar {
    public const YEAR = 1000;
    public const MONTH = 1001;
    public const DAY = 1002;
    public const HOUR = 1003;
    public const MINUTE = 1004;
    public const SECOND = 1005;
    public const DAYOFWEEK = 1006;
    public const WEEK = 1007;
	
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    public const JANUARY = 1;
    public const FEBRUARY = 2;
    public const MARCH = 3;
    public const APRIL = 4;
    public const MAY = 5;
    public const JUNE = 6;
    public const JULY = 7;
    public const AUGUST = 8;
    public const SEPTEMBER = 9;
    public const OCTOBER = 10;
    public const NOVEMBER = 11;
    public const DECEMBER = 12;

    private $_weekdays = [ "SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY" ];
	
    private $_firstDayOfWeek = SELF::SUNDAY;

    private $DTO = null;
    
    public function __construct($date = null) {
        if($date === null) {
            $this->DTO = new DateTime("now");
        } else {
            $this->DTO = new DateTime($date);
        }
    }
    
    protected function _getDTO():object {
        return $this->DTO;
    }
    
    private function _setDateTime( DateTime $dto ):void {
        if($dto instanceof DateTime && $dto != null) {
            $this->DTO = $dto;
        }
    }
    
    private function _setDateTimeFromArray( array $YmdHis ):void {
        $new_dt_string = $YmdHis['Y'] . "-" . $YmdHis['m'] . "-" . $YmdHis['d'] 
                . " " 
                . $YmdHis['H'] . ":" . $YmdHis['i'] . ":" . $YmdHis['s'];
        $this->DTO = DateTime::createFromFormat("Y-m-d H:i:s", $new_dt_string);
    }
    
    /**
     * Adds or subtracts the specified amount of time to the given calendar field
     */
    public function add( int $field, int $amount ):void {
    	// Special case for if this day of the month is > the last day of the next month;
    	// last day of the month; 1/31/2022 + 1 month comes back with 3/3/2022
    	// using the interval add. 
    	//	If today = lastDayOfMonth, add one day (roll into next month) and then
    	//		set DAY to lastDayOfMonth for the new month.

		//	Start 1/31/2022.
		//		Roll 7 days forward, determine new month.
		//		while $i < $number_of_months
		//			use interval to add another MONTH
		//		then, if $this->day > lastDayOfMonth, set to lastDayOfMonth
    	if(($field == SELF::MONTH) && ($this->get( SELF::DAY) > 27)) {
			$t = $this->clone();
			$starting_day = $this->get(SELF::DAY);
			
			$direction = ($amount > 0) ? 1 : -1;
			print $direction . "\n";
			
			$initial_offset = ($amount > 0) ? 7 : (($starting_day + 7) * -1);
			
			$this->add( SELF::DAY, $initial_offset );	// Takes us into the next month
			for($i = 0; $i < abs($amount) - 1; $i++) {
				$interval = $this->buildInterval( $field, 1 );
				if( $direction > 0) {
					$this->DTO->add( $interval );
				} else {
					$this->DTO->sub( $interval );
				}
			}
			// Here, we should be in the right month; now, massage the date if necessary
			if($starting_day > $this->getLastDayOfMonth() ) {
				$this->set( SELF::DAY, $this->getLastDayOfMonth() );
			} else {
				$this->set( SELF::DAY, $starting_day );
			}
			return;
    	}
    	// End special case
    	//print "Got here...\n";
    	
        $interval = $this->buildInterval( $field, abs($amount) );
		// print_r($interval);
		       
        if($amount < 0) {
            $this->DTO->sub( $interval );
        } else {
            $this->DTO->add( $interval );        
        }
    }

    //boolean 	after(Object when)
    //Returns whether this Calendar represents a time after the time represented by the specified Object.
    public function after( PACalendar $comparison ):bool {
        return $this->compareTo( $comparison ) > 0;
    }
 
    //boolean 	before(Object when)
    //Returns whether this Calendar represents a time before the time represented by the specified Object.
    public function before( PACalendar $comparison ):bool {
        return $this->compareTo( $comparison ) < 0;
    }
    
    
    private function buildInterval( $field, $amount ):object { 
        $duration = "";
        switch($field) {
            case SELF::YEAR:   
                $duration = "P" . $amount . "Y";
                break;
            case SELF::MONTH:   
                $duration = "P" . $amount . "M";
                break;
            case SELF::WEEK:   
                $duration = "P" . $amount . "W";
                break;
            case SELF::DAY:   
                $duration = "P" . $amount . "D";
                break;
            // Time intervals:
            case SELF::HOUR:   
                $duration = "T" . $amount . "H";
                break;
            case SELF::MINUTE:   
                $duration = "T" . $amount . "M";
                break;
            case SELF::SECOND:   
                $duration = "T" . $amount . "S";
                break;
        }
        return new DateInterval( $duration );
    }

    //void 	clear()
    //Sets all the calendar field values and the time value (millisecond offset from the Epoch) of this Calendar undefined.
    
    
    //void 	clear(int field)
    //Sets the given calendar field value and the time value (millisecond offset from the Epoch) of this Calendar undefined.
    // Deliberately not implemented.
    
    //Object 	clone()
    //Creates and returns a copy of this object.    
    public function clone():object {
        $retval = new PACalendar();
        
        $dt = new DateTime();
        $dt->setTimestamp( $this->DTO->getTimestamp() );
        $retval->_setDateTime( $dt );

        return $retval;        
    }

    //int 	compareTo(Calendar anotherCalendar)
    //Compares the time values (millisecond offsets from the Epoch) represented by two Calendar objects.
    public function compareTo( PACalendar $comparison ):int {
        // the value 0 if the time represented by the argument is equal to the 
        // time represented by this Calendar; a value less than 0 if the time of
        // this Calendar is before the time represented by the argument; and a 
        // value greater than 0 if the time of this Calendar is after the time 
        // represented by the argument.
        return $this->DTO->getTimestamp() - $comparison->DTO->getTimestamp();
    }
    
    //protected void 	complete()
    //Fills in any unset fields in the calendar fields.
    // Deliberately not implemented.
    
    //protected abstract void 	computeFields()
    //Converts the current millisecond time value time to calendar field values in fields[].
    // Deliberately not implemented.

    //protected abstract void 	computeTime()
    //Converts the current calendar field values in fields[] to the millisecond time value time.
    // Deliberately not implemented.
   
    //boolean 	equals(Object obj)
    //Compares this Calendar to the specified Object.
    public function equals( PACalendar $comparison ):bool {
        return $this->compareTo( $comparison) == 0;
    }
   
    //int 	get(int field)
    //Returns the value of the given calendar field.
    public function get( int $field ): int {
        $format = "";
        switch($field) {
            case SELF::YEAR:   
                $format = "Y";    // yyyy
                break;
            case SELF::MONTH:   
                $format = "n";    // 1-12
                break;
            case SELF::WEEK:   
                $format = "W";    // ISO-8601 week number of year (weeks starting on Monday)
                break;
            case SELF::DAY:   
                $format = "j";    //  1-31
                break;
            case SELF::DAYOFWEEK:
                $format = "w";    // 0 Sunday - 6 Saturday
                break;
            case SELF::HOUR:   
                $format = "G";    // 0 - 23
                break;
            case SELF::MINUTE:   
                $format = "i";       // 00 - 59
                break;
            case SELF::SECOND:   
                $format = "s";        // 00 - 59
                break;
        }
        return (int)$this->DTO->format($format);        
    }
    
    //int 	getActualMaximum(int field)
    //Returns the maximum value that the specified calendar field could have, given the time value of this Calendar.
    public function getActualMaximum( int $field ):int {
        if( $field == SELF::YEAR ) {
            $tempdt = new DateTime;
            $tempdt->setTimestamp( PHP_INT_MAX );   // 64-bit, 9,223,372,036,854,775,807, good through 292277026596
            //  $tempdt->setTimestamp(2147483647);    // 32-bit max, 2038
            return (int)$tempdt->format("Y");            
        }

        if( $field == SELF::MONTH ) {
            return 12;
        }
        
        if( $field == SELF::DAY ) {
            // This isn't the most efficient; after 'set' is implemented, revisit TODO
            $tempdt = $this->clone();
            $max = 0;
            $cur_month = $this->get( SELF::MONTH );
            while($tempdt->get( SELF::MONTH ) == $cur_month) {
                $max = $tempdt->get( SELF::DAY );
                $tempdt->add( SELF::DAY, 1 );
            }
            return $max;
        }
    }

    //int 	getActualMinimum(int field)
    //Returns the minimum value that the specified calendar field could have, given the time value of this Calendar.
    public function getActualMinimum( int $field ):int {
        if( $field == SELF::YEAR ) {
            $tempdt = new DateTime;
            $tempdt->setTimestamp( PHP_INT_MIN );   // 64-bit, 9,223,372,036,854,775,807, good through 292277026596
            return (int)$tempdt->format("Y");            
        }

        if( $field == SELF::MONTH ) {
            return 1;
        }
        
        if( $field == SELF::DAY ) {
            // This isn't the most efficient; after 'set' is implemented, revisit TODO
            return 1;            
        }
    }

    //static Locale[] 	getAvailableLocales()
    //Returns an array of all locales for which the getInstance methods of this class can return localized instances.
    //String 	getDisplayName(int field, int style, Locale locale)
    //Returns the string representation of the calendar field value in the given style and locale.
    //Map<String,Integer> 	getDisplayNames(int field, int style, Locale locale)
    //Returns a Map containing all names of the calendar field in the given style and locale and their corresponding field values.
    //int 	getFirstDayOfWeek()
    //Gets what the first day of the week is; e.g., SUNDAY in the U.S., MONDAY in France.
    //abstract int 	getGreatestMinimum(int field)
    //Returns the highest minimum value for the given calendar field of this Calendar instance.
    //static Calendar 	getInstance()
    //Gets a calendar using the default time zone and locale.
    //static Calendar 	getInstance(Locale aLocale)
    //Gets a calendar using the default time zone and specified locale.
    // ... Not yet implemented (TODO?)
    
    public static function getDifference( PACalendar $a):DateInterval {
        return $this->DTO->diff( $a->_getDTO(), true );
    
    }  

    
    public static function getDifferenceBetween( PACalendar $a, PACalendar $b):DateInterval {
        return $a->_getDTO()->diff( $b->_getDTO(), true );
    }
    
    //static Calendar 	getInstance(TimeZone zone)
    //Gets a calendar using the specified time zone and default locale.
    public static function getInstance( DateTimeZone $dtz ):object {
        $cal = new PACalendar();
        $cal->setTimezone( new DateTimeZone( $dtz ) );
        return $cal;
    }
    
    public function getLastDayOfMonth():int {
		return $this->getMaximum( SELF::DAY );
//     	if($month > -1) {
//     		$tpac = new PACalendar();
//     		$tpac->set( SELF::MONTH, $month );
//     		return $tpac->getMaximum( SELF::DAY );
//     	} else {
//     		return $this->getMaximum( SELF::DAY );
//     	}
    }
    
    //static Calendar 	getInstance(TimeZone zone, Locale aLocale)
    //Gets a calendar with the specified time zone and locale.
    //abstract int 	getLeastMaximum(int field)
    //Returns the lowest maximum value for the given calendar field of this Calendar instance.
    // ... Not yet implemented (TODO?)

    //abstract int 	getMaximum(int field)
    //Returns the maximum value for the given calendar field of this Calendar instance.
    public function getMaximum(int $field): int {
        return $this->getActualMaximum($field);
    }

    ////int 	getMinimalDaysInFirstWeek()
    //Gets what the minimal days required in the first week of the year are; e.g., if the first week is defined as one that contains the first day of the first month of a year, this method returns 1.
    // ... Not yet implemented (TODO?)

    //abstract int 	getMinimum(int field)
    //Returns the minimum value for the given calendar field of this Calendar instance.
    public function getMinimum(int $field): int {
        return $this->getActualMinimum($field);
    }
    
    //Date 	getTime()
    //Returns a Date object representing this Calendar's time value (millisecond offset from the Epoch").
    public function getTime():int {
        return $this->DTO->getTimestamp();
    }

    //long 	getTimeInMillis()
    //Returns this Calendar's time value in milliseconds.
    // ... N/A in PHP (?)

    //TimeZone 	getTimeZone()
    //Gets the time zone.
    public function getTimeZone():object {
        return $this->DTO->getTimezone();
    }

    //int 	getWeeksInWeekYear()
    //Returns the number of weeks in the week year represented by this Calendar.
    public function getWeeksInWeekYear():int {
        $tempdt = DateTime::createFromFormat("Y-m-d", $this->get( SELF::YEAR) . "-12-31");
        return (int)$tempdt->format('W');
    }
    
    //int 	getWeekYear()
    //Returns the week year represented by this Calendar.
    public function getWeekYear():int {
        return $this->get( SELF::WEEK );
    }
    
    //int 	hashCode()
    //Returns a hash code for this calendar.
    public function hashCode():int {
        return crc32( json_encode( $this->DTO ) );
    }

    
    /**
	 * Parses a date string.
	 *
	 * @param string $dateString Any string that can be parsed by PHP's \DateTime constructor is valid here (e.g., "9/27/2022" or "September 27, 2022").
	 *
	 * @return PACalendar
	 */
	public static function parse( $dateString ) {
            return new PACalendar( $dateString );		
	}
    
    //protected int 	internalGet(int field)
    //Returns the value of the given calendar field.
    //boolean 	isLenient()
    //Tells whether date/time interpretation is to be lenient.
    //boolean 	isSet(int field)
    //Determines if the given calendar field has a value set, including cases that the value has been set by internal fields calculations triggered by a get method call.
    //boolean 	isWeekDateSupported()
    //Returns whether this Calendar supports week dates.
    //abstract void 	roll(int field, boolean up)
    //Adds or subtracts (up/down) a single unit of time on the given time field without changing larger fields.
    //void 	roll(int field, int amount)
    //Adds the specified (signed) amount to the specified calendar field without changing larger fields.
    // ... Not yet implemented (TODO?)

    //void 	set(int field, int value)
    //Sets the given calendar field to the given value.
    public function set( int $field, int $value):void {
        $ndt = [
            'Y' => $this->DTO->format('Y'),
            'm' => $this->DTO->format('m'),
            'd' => $this->DTO->format('d'),
            'H' => $this->DTO->format('H'),
            'i' => $this->DTO->format('i'),
            's' => $this->DTO->format('s'),
        ];
       
        switch( $field ) {
            case SELF::YEAR:
                $ndt['Y'] = $value;
                break;
            case SELF::MONTH:
                $ndt['m'] = sprintf("%02d", $value);
                break;
            case SELF::DAY:
                $ndt['d'] = sprintf("%02d", $value);
                break;
            case SELF::HOUR:
                $ndt['H'] = sprintf("%02d", $value);
                break;
            case SELF::MINUTE:
                $ndt['i'] = sprintf("%02d", $value);
                break;
            case SELF::SECOND:
                $ndt['s'] = sprintf("%02d", $value);
                break;
        }
        $this->_setDateTimeFromArray( $ndt );
    }

    //void 	set(int year, int month, int date)
    //Sets the values for the calendar fields YEAR, MONTH, and DAY_OF_MONTH.
    //void 	set(int year, int month, int date, int hourOfDay, int minute)
    //Sets the values for the calendar fields YEAR, MONTH, DAY_OF_MONTH, HOUR_OF_DAY, and MINUTE.
    //void 	set(int year, int month, int date, int hourOfDay, int minute, int second)
    //Sets the values for the fields YEAR, MONTH, DAY_OF_MONTH, HOUR, MINUTE, and SECOND.
    public function setFields( int $year, int $month, int $date, int $hourOfDay = -1, int $minute = -1, int $second = -1):void {
        // TODO Use DateTime::setDate, ::setTime?
        $ndt = [
            'Y' => $year,
            'm' => sprintf("%02d", $month),
            'd' => sprintf("%02d", $date),
            'H' => $this->DTO->format('H'),
            'i' => $this->DTO->format('i'),
            's' => $this->DTO->format('s'),
        ];
        
        if($hourOfDay > -1) {
            $ndt['H'] = sprintf("%02d", $hourOfDay);
        }
        if($minute > -1) {
            $ndt['i'] = sprintf("%02d", $minute);
        }
        if($second > -1) {
            $ndt['s'] = sprintf("%02d", $second);
        }
        $this->_setDateTimeFromArray( $ndt );
    }

    //void 	setFirstDayOfWeek(int value)
    //Sets what the first day of the week is; e.g., SUNDAY in the U.S., MONDAY in France.
    //void 	setLenient(boolean lenient)
    //Specifies whether or not date/time interpretation is to be lenient.
    //void 	setMinimalDaysInFirstWeek(int value)
    //Sets what the minimal days required in the first week of the year are; For example, if the first week is defined as one that contains the first day of the first month of a year, call this method with value 1.
    // ... Not yet implemented (TODO?)

    //void 	setTime(Date date)
    //Sets this Calendar's time with the given Date.
    public function setTime(int $epoch):void {
        $this->DTO->setTimestamp($epoch);
    }

    //void 	setTimeInMillis(long millis)
    //Sets this Calendar's current time from the given long value.
    // ... N/A in PHP (?)

    //void 	setTimeZone(TimeZone value)
    //Sets the time zone with the given time zone value.
    // e.g. ->setTimeZone( new DateTimeZone('America/New_York') );
    public function setTimeZone( DateTimeZone $dtz ):void {
        $this->DTO->setTimezone( $dtz );
    }

    //void 	setWeekDate(int weekYear, int weekOfYear, int dayOfWeek)
    //Sets the date of this Calendar with the the given date specifiers - week year, week of year, and day of week.
    // ... Not yet implemented (TODO?)
   
    
    /**
     * Sets the weekday in a month, e.g., the 1st Monday or the last Friday.
     *
     * If the count is negative, search backwards from the end of the month.
     * Otherwise, start from the first of the month and find the $count instance of the specified weekday.
     * Examples: Calendar::THURSDAY, 4 - the 4th Thursday (e.g., Thanksgiving)
     *   Calendar::MONDAY, 1 - the first Monday.
     *   Calendar::MONDAY, -1 - the last Monday.
     *
     * @param int $dayofweek A Calendar constant (e.g., Calendar::MONDAY)
     * @param int $count The number of the weekday (e.g. 2 for the 2nd Monday; -1 for the last Monday, in a month)
     *
     * @return void
     */
    public function setWeekdayInMonth( int $dayofweek, int $count ) {
        $date_weekday = $dayofweek;	// 0 = Sunday etc. 'w'
        $tcal = $this->clone();
        $endcal = $this->clone();
        // Default: Count forward, starting on the 1st:
        $offset = 1;
        $days_in_month = $this->getMaximum( SELF::DAY );
        $tcal->set(SELF::DAY, 1);
        $endcal->set(SELF::DAY, $days_in_month );

        // Override if counting backwards:
        if ($count < 0) {
                $offset = -1;
                $tcal->set(SELF::DAY, $days_in_month );
                $endcal->set(SELF::DAY, 1);
        }

        $foundcount = 0;
        do {

                if ( $tcal->get(SELF::DAYOFWEEK) == $date_weekday) {
                        $foundcount++;
                        if ($foundcount == abs($count)) {
                                $this->set( SELF::DAY, $tcal->get(SELF::DAY));
                                return;
                        }
                }
                $tcal->add( SELF::DAY, $offset );

        } while( $tcal->compareTo( $endcal ) != 0);
        // If we've gotten here, no date matches the $count instance of $weekday
        throw new Exception("Invalid specification: No [" . $count . "] instance of PACalendar::" . $this->_weekdays[ $date_weekday] . " in " . $this->toString('F Y'));
    }
    
    
    /*  String 	toString()
     *  Return a string representation of this calendar.
     *
     */
    public function toString( $format = "c"):string {
        return $this->DTO->format($format);
    }
    
    
    // TODO Clean up the following and move the functions into alphabetical order, above.
    /**
	 * Compares two Calendar objects looking only at the date portion (ignoring hours, minutes, seconds)
	 * 
	 * @param Calendar $obj The Calendar instance to compare $this to
	 *
	 * @return int 0 if equal, < 0 if $this before $obj, > 0 if $this after $obj
	 */
	public function compareToDate( PACalendar $obj ):int {
		// Compare two Calendar objects looking only at Year/Month/Day
		$a = (int)$this->toString("Ymd");
		$b = (int)$obj->toString("Ymd");
		return $a - $b;
	}
	
	/**
	 * Compare two Calendar objects looking only at the date portion (ignoring hours, minutes, seconds)
	 *
	 * Equivalent to compareToDate == 0
	 *
	 * @param $obj The Calendar instance to compare $this to
	 *
	 * @return bool True if the same date
	 */
	public function equalsDate( PACalendar $obj ):bool {
		return ! $this->compareToDate( $obj );
	}
	
	/**
	 * Determine if $this is after $obj looking only at the date
	 *
	 * @param Calendar $obj Calendar instance to compare $this to
	 *
	 * @return bool True if $this is after $obj
	 */
	public function afterDate( PACalendar $obj ):bool {
		if($this->compareToDate( $obj ) > 0 ) {
			return true;
		}
		return false;
	}

	/**
	 * Determine if $this is before $obj looking only at the date
	 *
	 * @param Calendar $obj Calendar instance to compare $this to
	 *
	 * @return bool True if $this is before $obj
	 */
	public function beforeDate( PACalendar $obj ):bool {
		if($this->compareToDate( $obj ) < 0 ) {
			return true;
		}
		return false;
	}
    
        
        public static function ordinal( int $day ) {
            $cal = new PACalendar();
            $cal->set( SELF::DAY, $day );
            return $cal->toString( "S" );
	}
    
//static int 	ALL_STYLES
//A style specifier for getDisplayNames indicating names in all styles, such as "January" and "Jan".
//static int 	AM
//Value of the AM_PM field indicating the period of the day from midnight to just before noon.
//static int 	AM_PM
//Field number for get and set indicating whether the HOUR is before or after noon.
//static int 	APRIL
//Value of the MONTH field indicating the fourth month of the year in the Gregorian and Julian calendars.
//protected boolean 	areFieldsSet
//True if fields[] are in sync with the currently set time.
//static int 	AUGUST
//Value of the MONTH field indicating the eighth month of the year in the Gregorian and Julian calendars.
//static int 	DATE
//Field number for get and set indicating the day of the month.
//static int 	DAY_OF_MONTH
//Field number for get and set indicating the day of the month.
//static int 	DAY_OF_WEEK
//Field number for get and set indicating the day of the week.
//static int 	DAY_OF_WEEK_IN_MONTH
//Field number for get and set indicating the ordinal number of the day of the week within the current month.
//static int 	DAY_OF_YEAR
//Field number for get and set indicating the day number within the current year.
//static int 	DECEMBER
//Value of the MONTH field indicating the twelfth month of the year in the Gregorian and Julian calendars.
//static int 	DST_OFFSET
//Field number for get and set indicating the daylight saving offset in milliseconds.
//static int 	ERA
//Field number for get and set indicating the era, e.g., AD or BC in the Julian calendar.
//static int 	FEBRUARY
//Value of the MONTH field indicating the second month of the year in the Gregorian and Julian calendars.
//static int 	FIELD_COUNT
//The number of distinct fields recognized by get and set.
//protected int[] 	fields
//The calendar field values for the currently set time for this calendar.
//static int 	FRIDAY
//Value of the DAY_OF_WEEK field indicating Friday.
//static int 	HOUR
//Field number for get and set indicating the hour of the morning or afternoon.
//static int 	HOUR_OF_DAY
//Field number for get and set indicating the hour of the day.
//protected boolean[] 	isSet
//The flags which tell if a specified calendar field for the calendar is set.
//protected boolean 	isTimeSet
//True if then the value of time is valid.
//static int 	JANUARY
//Value of the MONTH field indicating the first month of the year in the Gregorian and Julian calendars.
//static int 	JULY
//Value of the MONTH field indicating the seventh month of the year in the Gregorian and Julian calendars.
//static int 	JUNE
//Value of the MONTH field indicating the sixth month of the year in the Gregorian and Julian calendars.
//static int 	LONG
//A style specifier for getDisplayName and getDisplayNames indicating a long name, such as "January".
//static int 	MARCH
//Value of the MONTH field indicating the third month of the year in the Gregorian and Julian calendars.
//static int 	MAY
//Value of the MONTH field indicating the fifth month of the year in the Gregorian and Julian calendars.
//static int 	MILLISECOND
//Field number for get and set indicating the millisecond within the second.
//static int 	MINUTE
//Field number for get and set indicating the minute within the hour.
//static int 	MONDAY
//Value of the DAY_OF_WEEK field indicating Monday.
//static int 	MONTH
//Field number for get and set indicating the month.
//static int 	NOVEMBER
//Value of the MONTH field indicating the eleventh month of the year in the Gregorian and Julian calendars.
//static int 	OCTOBER
//Value of the MONTH field indicating the tenth month of the year in the Gregorian and Julian calendars.
//static int 	PM
//Value of the AM_PM field indicating the period of the day from noon to just before midnight.
//static int 	SATURDAY
//Value of the DAY_OF_WEEK field indicating Saturday.
//static int 	SECOND
//Field number for get and set indicating the second within the minute.
//static int 	SEPTEMBER
//Value of the MONTH field indicating the ninth month of the year in the Gregorian and Julian calendars.
//static int 	SHORT
//A style specifier for getDisplayName and getDisplayNames indicating a short name, such as "Jan".
//static int 	SUNDAY
//Value of the DAY_OF_WEEK field indicating Sunday.
//static int 	THURSDAY
//Value of the DAY_OF_WEEK field indicating Thursday.
//protected long 	time
//The currently set time for this calendar, expressed in milliseconds after January 1, 1970, 0:00:00 GMT.
//static int 	TUESDAY
//Value of the DAY_OF_WEEK field indicating Tuesday.
//static int 	UNDECIMBER
//Value of the MONTH field indicating the thirteenth month of the year.
//static int 	WEDNESDAY
//Value of the DAY_OF_WEEK field indicating Wednesday.
//static int 	WEEK_OF_MONTH
//Field number for get and set indicating the week number within the current month.
//static int 	WEEK_OF_YEAR
//Field number for get and set indicating the week number within the current year.
//static int 	YEAR
//Field number for get and set indicating the year.
//static int 	ZONE_OFFSET
//Field number for get and set indicating the raw offset from GMT in milliseconds.
        
        



}
