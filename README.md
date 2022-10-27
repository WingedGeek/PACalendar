# PACalendar
A loose re-implementation of java.util.Calendar (with some added functionality) in PHP, using DateTime etc. (I had a re-implementing the wheel version of this I was working on before, using seconds-since-epoch; this is a refactored version relying heavily on PHP's internal DateTime, DateInterval, etc. API.)

# Basic Usage Example

```
<?php
require "PACalendar.php";

$parse_string = "11/15/2022";
$today = new PACalendar();
$due = PACalendar::parse( $parse_string );
$twenty_one_days_from_today = $today->clone();
$twenty_one_days_from_today->add( PACalendar::DAY, 21);
print "Today is " . $today->toString() . "\n";
print "21 days from today is " . $twenty_one_days_from_today->toString() . "\n";
print "$parse_string is " . $due->toString() . "\n";
print "Due date is " . $due->toString() . "\n";

$text = ($today->beforeDate( $due )) ? "until due" : "past due";

$diff = PACalendar::getDifferenceBetween( $today, $due );
print $diff->d . " day(s) " . $text 
		. " (due on the " . $due->toString('jS') 
		. " of " . $due->toString('F, Y') . ")\n";

```
