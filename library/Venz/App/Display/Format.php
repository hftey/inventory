<?php

    
    class Venz_App_Display_Format
    {
	   function check_date($date)
		{
			if (preg_match("/0000-00-00/", $date)  || is_null($date) )
				return false;
			else
				return true;
			
		}
		
		function ordinal($num)
		{
			// Special case "teenth"
			if ( ($num / 10) % 10 != 1 )
			{
				// Handle 1st, 2nd, 3rd
				switch( $num % 10 )
				{
					case 1: return $num . 'st';
					case 2: return $num . 'nd';
					case 3: return $num . 'rd';  
				}
			}
			// Everything else is "nth"
			return $num . 'th';
		}
		
		function format_age($dob)
		{
			if (preg_match("/0000-00-00/", $dob)  || is_null($dob) )
				return "";
			else{
				list($year,$month,$day) = explode("-",$dob);    
				$year_diff  = date("Y") - $year;    
				$month_diff = date("m") - $month;    
				$day_diff   = date("d") - $day;    
				if ($month_diff < 0) 
					$year_diff--;    
				elseif (($month_diff==0) && ($day_diff < 0)) 
					$year_diff--;    
					
				return $year_diff;	        
			}
		}
		
		function format_date ($date, $newline = ', ') {
			if (preg_match("/0000-00-00/", $date)  || is_null($date) )
				return "";
			else
				return strftime("%d %b".$newline." %Y", strtotime($date));
		}
		
		function format_time ($date) {
			if (preg_match("/0000-00-00/", $date) || is_null($date) )
				return "";
			else
				return strftime("%H:%M", strtotime($date));
		}
		
		function format_datetime ($date, $newline=", ") {
			if (preg_match("/0000-00-00/", $date) || is_null($date) )
				return "";
			else
				return strftime("%d %b".$newline."%Y %H:%M", strtotime($date));
		}
		
		function format_datetime_sec ($date) {
			if (preg_match("/0000-00-00/", $date)  || is_null($date) )
				return "";
			else
				return strftime("%d %b, %Y %H:%M:%S", strtotime($date));
		} 
		   
		 function format_datetime_simple ($date, $newline=" ") {
			
			if (preg_match("/0000-00-00/", $date)  || is_null($date) )
				return "";
			else
				return strftime("%d/%m/%Y".$newline."%H:%M", strtotime($date));
		}   
		function format_date_simple ($date) {
			
			if (preg_match("/0000-00-00/", $date)  || is_null($date) )
				return "";
			else
				return strftime("%d/%m/%Y", strtotime($date));
		}
		
		function format_date_simple_to_db ($date) {
			$date_pattern = "(([0-9]{1,2})-([0-9]{1,2}-[0-9]{4}))";
			if (preg_match($date_pattern, $date) && !preg_match("/0000-00-00/", $date))
			{
				$DateData = explode("-", $date);
				return $DateData[2]."-".$DateData[1]."-".$DateData[0];
			}
			else
				return "";
			   
		}
		
		function format_date_db_to_simple ($date) {
			
			$date_pattern = "(([0-9]{4})-([0-9]{1,2}-[0-9]{1,2}))";
			if (preg_match($date_pattern, $date) && !preg_match("/0000-00-00/", $date))
			{
				$DateData = explode("-", $date);
				return $DateData[2]."-".$DateData[1]."-".$DateData[0];
			}
			else
				return "";
			   
		}	
		
		function checkDateFormat($date)
		{
		  //match the format of the date
		  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
		  {
			//check weather the date is valid of not
			if(checkdate($parts[2],$parts[3],$parts[1]))
			  return true;
			else
			 return false;
		  }
		  else
			return false;
		}
		
		function checkTimeFormat($time)
		{
		  //match the format of the date
		  if (preg_match ("/^([0-9]{2}):([0-9]{2})$/", $time, $parts))
		  {
			  $arrTime = split(":", $time);
			  if (intval($arrTime[0]) >= 0 AND intval($arrTime[0]) < 24 AND intval($arrTime[1]) >= 0 AND intval($arrTime[1]) < 60) 
				return true;
			  else
				return false;
			  
		  }
		  else
			return false;
		}
		
		function format_timestamp_to_datetime ($timestamp) {
			if (!is_numeric($timestamp) || is_null($timestamp) )
				return "";
			else
				return strftime("%d %b, %Y %H:%M", $timestamp);
		}
		
		function format_timestamp_to_date ($timestamp) {
			if (!is_numeric($timestamp) || is_null($timestamp) )
				return "";
			else
				return strftime("%d %b, %Y", $timestamp);
		}
		
		function format_time_simple($time, $format12hrs=false)
		{
			if (preg_match ("/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $time, $parts))
			{
				$arrTime = split(":", $time);
				if($format12hrs){
					return date('h:i A', mktime($arrTime[0], $arrTime[1]));
				}
				else{
					return $arrTime[0].":".$arrTime[1];
				}
			}
			else
				return "00:00";
		}
		
		function format_date_from_slash_to_db_defualt ($date) { 
			$date_pattern = "(([0-9]{1,2})/([0-9]{1,2}/[0-9]{4}))";
			if (preg_match($date_pattern, $date))
			{
				$DateData = split("/", $date);
				return $DateData[2]."-".$DateData[1]."-".$DateData[0];
			}
			else
				return "0000-00-00"; 
		}
		
		
  		function format_currency ($amount) {
		
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];
			
			if ($amount)
				return $currencyType." " . number_format($amount, 2, ".", ",");
			else
				return "";
		}      
    }
    