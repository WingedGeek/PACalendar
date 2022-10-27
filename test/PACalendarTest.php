<?php
/*  Usage:
    Download phar from https://phar.phpunit.de/
    chmod +x phpunit-???.phar
    ln -s phpunit-???.phar phpunit
    phpunit PACalendarTest.php
*/


require __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "PACalendar.php";

use PHPUnit\Framework\TestCase;

class PACalendarTest extends TestCase {
	public function testDayOfWeek() {
		$c = PACalendar::parse("2019-10-01");
		$c->setWeekdayInMonth( PACalendar::THURSDAY, 3);
		$this->assertEquals('2019-10-17', $c->toString('Y-m-d'));

		$c = PACalendar::parse("2019-09-01");
		$c->setWeekdayInMonth( PACalendar::MONDAY, 1);
		$this->assertEquals('2019-09-02', $c->toString('Y-m-d'));

		$c = PACalendar::parse("2019-05-01");
		$c->setWeekdayInMonth( PACalendar::MONDAY, -1);
		$this->assertEquals('2019-05-27', $c->toString('Y-m-d'));

		$c = PACalendar::parse("2021-11-01");
		$c->setWeekdayInMonth( PACalendar::THURSDAY, 4);
		$this->assertEquals('2021-11-25', $c->toString('Y-m-d'));

// 		try { 
// 			$c = PACalendar::parse("2019-02-01");
// 			print "This should throw an error: ";
// 			$c->setWeekdayInMonth( PACalendar::MONDAY, 6);
// 			print "Is last Monday of May 2019 the 27th? " . $c->toString() . "\n";
// 		} catch( Exception $e ) {
// 			print "Caught exception $e" . "\n";
// 		}
	}
	
	
	public function testArithmetic() {		
		$c = PACalendar::parse("1/31/2022");
		$c->add( PACalendar::DAY, 1 );
		$this->assertEquals( "2022-02-01", $c->toString('Y-m-d'));
		$c->add( PACalendar::MONTH, 2 );
		$this->assertEquals( "2022-04-01", $c->toString('Y-m-d'));
		
		$c2 = PACalendar::parse("1/31/2022");
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-02-28", $c2->toString('Y-m-d'));	// Per https://www.timeanddate.com/date/dateadded.html?m1=1&d1=31&y1=2022&type=add&ay=&am=1&aw=&ad=&rec=
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-03-28", $c2->toString('Y-m-d'));	// Per https://www.timeanddate.com/date/dateadded.html?m1=2&d1=28&y1=2022&type=add&ay=&am=1&aw=&ad=&rec=
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-04-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-05-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-06-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-07-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-08-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-09-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-10-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-11-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2022-12-28", $c2->toString('Y-m-d'));
		$c2->add( PACalendar::MONTH, 1 );
		$this->assertEquals( "2023-01-28", $c2->toString('Y-m-d'));
		
		$c3 = PACalendar::parse("1/31/2022");
		$c3->add( PACalendar::MONTH, 3 );		
		$this->assertEquals( "2022-04-30", $c3->toString('Y-m-d'));	// Per https://www.timeanddate.com/date/dateadded.html?m1=1&d1=31&y1=2022&type=add&ay=&am=3&aw=&ad=&rec=

		$c4 = PACalendar::parse("2/28/2022");
		$c4->add( PACalendar::MONTH, 3 );		
		$this->assertEquals( "2022-05-28", $c4->toString('Y-m-d'));	// 2-28-2022 +3 months = 5-28-2022 per https://www.timeanddate.com/date/dateadded.html
		
		$c5 = PACalendar::parse("12/31/2022");
		$c5->add( PACalendar::MONTH, 7 );		
		$this->assertEquals( "2023-07-31", $c5->toString('Y-m-d'));	// 2-28-2022 +3 months = 5-28-2022 per https://www.timeanddate.com/date/dateadded.html

		// 12/31/2022 -7 months = May 31, 2022
		$c6 = PACalendar::parse("12/31/2022");
		$c6->add( PACalendar::MONTH, -7 );		
		$this->assertEquals( "2022-05-31", $c6->toString('Y-m-d'));		

		// 12/31/2022 -7 months = May 31, 2022
		$c7 = PACalendar::parse("12/31/2022");
		$c7->add( PACalendar::MONTH, -12 );		
		$this->assertEquals( "2021-12-31", $c7->toString('Y-m-d'));		
	}
		
	public function testComparisons() {		
		// Test 'after()'
		$c = PACalendar::parse("2021-11-01");
		$d = PACalendar::parse("2000-09-01");
		$this->assertTrue( $c->after( $d ));
		$this->assertFalse( $d->after( $c ));
		
		// Test 'before()'
		$this->assertTrue( $d->before( $c ));
		$this->assertFalse( $c->before( $d ));
		
		// Test 'compareTo()'
		$this->assertLessThan( 0, $d->compareTo( $c ));
		$this->assertGreaterThan( 0, $c->compareTo( $d ));

		// Test 'equals()' 'compareTo()' and 'clone()'
		$eq1 = new PACalendar();
		$eq2 = new PACalendar();
		$eq3 = $eq1->clone();
		$this->assertTrue( $eq1->equals( $eq2 ) );
		$this->assertTrue( $eq1->equals( $eq3 ) );
		$this->assertTrue( $eq2->equals( $eq3 ) );
		$this->assertEquals( 0, $eq1->compareTo( $eq2 ) );	
		$this->assertEquals( 0, $eq1->compareTo( $eq3 ) );	
		$this->assertEquals( 0, $eq2->compareTo( $eq3 ) );
		
		// Test date comparisons
		$c = PACalendar::parse("1/5/2022");
		$c->set( PACalendar::HOUR, 23 );
		$d = PACalendar::parse("1/5/2022");
		$d->set( PACalendar::HOUR, 14 );
		$this->assertTrue( $c->equalsDate( $d ) );
		$this->assertEquals( 0, $c->compareToDate( $d ) );

		$c = PACalendar::parse("11/1/2020");
		$d = PACalendar::parse("11/3/2020");
		$this->assertTrue( $c->beforeDate( $d ) );
		$this->assertFalse( $d->beforeDate( $c ) );
		$this->assertFalse( $c->afterDate( $d ) );
		$this->assertTrue( $d->afterDate( $c ) );
		$this->assertFalse( $c->equalsDate( $d ) );
	}
	
	public function testDateCalculator() {
		
		// Should be 44 years, 11 months, 20 days between these dates:
		$c = PACalendar::parse("1/31/1977");
		$d = PACalendar::parse("1/20/2022");
		//$cd = PACalendar::calculateDuration($c, $d, PACalendar::YEAR, PACalendar::DAY);
		$interval = PACalendar::getDifferenceBetween( $c, $d );
		
		$this->assertEquals(44, $interval->y );
 		$this->assertEquals(11, $interval->m );
 		$this->assertEquals(20, $interval->d );
// 		//	 Or 16,425 days:
		$this->assertEquals("16425", $interval->format("%a"));

		// 	1/20/2022	to	12/25/2022	11 months, 5 days (339 days)
 		$c = PACalendar::parse("1/20/2022");
 		$d = PACalendar::parse("12/25/2022");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
 		$this->assertEquals(0, $interval->y );
 		$this->assertEquals(11, $interval->m );
 		$this->assertEquals(5, $interval->d );
		$this->assertEquals("339", $interval->format("%a"));

		//	1/20/2022	to	1/31/2027	5 years, 11 days	60 months, 11 days	1837 days
		$c = PACalendar::parse("1/20/2022");
		$d = PACalendar::parse("1/31/2027");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
		$this->assertEquals(5, $interval->y );
		$this->assertEquals(0, $interval->m );
		$this->assertEquals(11, $interval->d );
 		$this->assertEquals("1837", $interval->format("%a"));

		// 2/1/2020 -> 3/1/2020 = 29 days (leap year)
		$c = PACalendar::parse("2/1/2020");
		$d = PACalendar::parse("3/1/2020");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
 		$this->assertEquals(1, $interval->m );
 		$this->assertEquals(0, $interval->d );
		$this->assertEquals("29", $interval->format("%a"));

		// 2/1/2021 -> 3/1/2021 = 28 days (non-leap year)
		$c = PACalendar::parse("2/1/2021");
		$d = PACalendar::parse("3/1/2021");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
		$this->assertEquals("28", $interval->format("%a") );

		// 2/1/2000 -> 3/1/2000 = 29 days (leap year)
		$c = PACalendar::parse("2/1/2000");
		$d = PACalendar::parse("3/1/2000");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
		$this->assertEquals("29", $interval->format("%a"));

		// 2/1/1900 -> 3/1/1900 = 28 days (non-leap year)
		$c = PACalendar::parse("2/1/1900");
		$d = PACalendar::parse("3/1/1900");
		$interval = PACalendar::getDifferenceBetween( $c, $d );
		$this->assertEquals("28", $interval->format("%a") );
	}
}
?>