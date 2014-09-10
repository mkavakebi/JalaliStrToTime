<?php
/**
 * @author Morteza Kavakebi <m.kavakebi@gmail.com>
 * @version 1.0.0
 */
class Timely extends JalaliDate {

    private static function PersianTimerangeRaw($fromTime, $toTime, $mode = 'Monthly', $offset, $count, $exLang = 'gregorian') {
        list($jYear1, $jMonth1, $jDay1) = self::ExplodeToJalali($fromTime);
        list($jYear2, $jMonth2, $jDay2) = self::ExplodeToJalali($toTime);
        $ar = array();
        switch ($mode) {
            case 'Daily':
                $begin = new DateTime($fromTime);
                $end = new DateTime($toTime);
                $end = $end->modify('+1 day');
                $interval = new DateInterval('P1D');
                $priod = new DatePeriod($begin, $interval, $end);
                foreach ($priod as $p) {
                    $ar[] = $p->format('Y-m-d');
                }
                break;
            case 'Monthly':
                if ($jYear1 == $jYear2) {
                    for ($m = $jMonth1; $m <= $jMonth2; $m++) {
                        $ar[] = self::jalali_to_gregorian($jYear1, $m, 1);
                    }
                } else {
                    for ($m = $jMonth1; $m <= 12; $m++) {
                        $ar[] = self::jalali_to_gregorian($jYear1, $m, 1);
                    }
                    for ($y = $jYear1 + 1; $y <= $jYear2 - 1; $y++) {
                        for ($m = 1; $m <= 12; $m++) {
                            $ar[] = self::jalali_to_gregorian($y, $m, 1);
                        }
                    }
                    for ($m = 1; $m <= $jMonth2; $m++) {
                        $ar[] = self::jalali_to_gregorian($jYear2, $m, 1);
                    }
//                    var_dump($ar);
//                    var_dump($ar);
                }
                //to add ending date
                if ($jMonth2 == 12) {
                    $ar[] = self::jalali_to_gregorian($jYear2 + 1, 1, 1);
                } else {
                    $ar[] = self::jalali_to_gregorian($jYear2, $jMonth2 + 1, 1);
                }
                break;
            case 'Seasonly':
                if ($jYear1 == $jYear2) {
                    for ($f = self::mah2fasl($jMonth1); $f <= self::mah2fasl($jMonth2); $f++) {
                        $ar[] = self::jalali_to_gregorian($jYear1, self::fasl2mah($f), 1);
                    }
                } else {
                    for ($f = self::mah2fasl($jMonth1); $f <= 3; $f++) {
                        $ar[] = self::jalali_to_gregorian($jYear1, self::fasl2mah($f), 1);
                    }
                    for ($y = $jYear1 + 1; $y <= $jYear2 - 1; $y++) {
                        for ($f = 0; $f <= 3; $f++) {
                            $ar[] = self::jalali_to_gregorian($y, self::fasl2mah($f), 1);
                        }
                    }
                    for ($f = 0; $f <= self::mah2fasl($jMonth2); $f++) {
                        $ar[] = self::jalali_to_gregorian($jYear2, self::fasl2mah($f), 1);
                    }
                }
                //to add ending date
                if (self::mah2fasl($jMonth2) == 3) {
                    $ar[] = self::jalali_to_gregorian($jYear2 + 1, 1, 1);
                } else {
                    $ar[] = self::jalali_to_gregorian($jYear2, self::fasl2mah(self::mah2fasl($jMonth2) + 1), 1);
                }

                break;
            case 'Yearly':
                for ($y = $jYear1; $y <= $jYear2; $y++) {
                    $ar[] = self::jalali_to_gregorian($y, 1, 1);
                }
                $ar[] = self::jalali_to_gregorian($jYear2 + 1, 1, 1);
                break;
        }
        if ($mode == 'Daily') {
            $ar = array_slice($ar, $offset, $count + 1);
            $ar2 = $ar;
        } else {
            $ar = array_slice($ar, $offset, $count + 1);
            foreach ($ar as $x) {
                $t = mktime(0, 0, 0, $x[1], $x[2], $x[0]);
                $ar2[] = date('Y-m-d', $t);
            }
        }
        return $ar2;
    }

    /**
     * 
     * @param type $fromTime
     * @param type $toTime
     * @param type $mode like "Monthly" "Seasonly" "Yearly"
     * @param type $offset
     * @param type $count
     * @return type
     */
    public static function PersianTimerangeRipe($fromTime, $toTime, $mode, $offset, $count) {
        $ar = self::PersianTimerangeRaw($fromTime, $toTime, $mode, $offset, $count);
        if ($mode == 'Daily') {
            if ($fromTime == $toTime) {
                $ar[1] = $ar[0];
            }
            for ($i = 0; $i < count($ar); $i++) {
                $item = array();
                $item['date'] = $ar[$i];
                $item['label'] = self::GetTimePriodLabel($ar[$i], $mode);
                $items[] = $item;
            }
        } else {
            for ($i = 0; $i < count($ar) - 1; $i++) {
                $item = array();
                $item['from'] = $ar[$i];
                $item['to'] = $ar[$i + 1];
                $item['label'] = self::GetTimePriodLabel($item['from'], $mode);
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * 
     * @param type $date
     * @param type $mode
     * @return string
     * returns label of the time priod using its date
     * @example بهار90, مهر90 
     */
    private static function GetTimePriodLabel($date, $mode) {
        list($jY, $jM, $jD) = self::ExplodeToJalali($date);
        switch ($mode) {
            case 'Monthly':
                $label = self::$PersianMonthes[$jM - 1] . '' . $jY;
                break;
            case 'Yearly':
                $label = $jY;
                break;
            case 'Seasonly':
                $label = self::$PersianSeasons[self::mah2fasl($jM)] . '' . $jY;
                break;
            case 'Daily':
                $label = $jY . '/' . $jM . '/' . $jD;
                break;
            default :
                $label = '?';
                break;
        }
        return $label;
    }

    public static function StrToTime_Filter(&$str, $time) {
        switch ($str) {
            case 'امروز':
            case 'today':
                return Timely::jdate('Y/m/d', 'now', $time);
                break;
            case 'دیروز':
            case 'yesterday':
                return Timely::jdate('Y/m/d', '-1 day', $time);
                break;
            case 'فردا':
            case 'tomorrow':
                return Timely::jdate('Y/m/d', '1 day', $time);
                break;
        }
        $r = explode(' ', $str);
        if (count($r) == 1) {
            return $str;
        }
        $offset = $r[0];
        switch ($r[1]) {
            case 'روز':
            case 'day':
                $name = 'day';
                break;
            case 'ماه':
            case 'month':
                $name = 'month';
                break;
            case 'سال':
            case 'year':
                $name = 'year';
                break;
            default:
                throw new Exception('wrong Time input Format!');
                return;
                break;
        }
        return Timely::jdate('Y/m/d', "$offset $name", $time);
    }

    public static function AllCalendartToGregory($date, $sep = '/', $relativeTo = null) {
        if ($relativeTo)
            $relativeTo = time();
        $date2 = self::StrToTime_Filter($date, $relativeTo);
        list($Y, $M, $D) = self::ExplodeGeneralDate($date2);
        if ($Y < 1500) { //then it is Jalali Date
            $a = self::jalali_to_gregorian($Y, $M, $D);
            return implode($sep, $a);
        } else {
            return $date2;
        }
    }

    public static function AllCalendartToJalali(&$date, $sep = '/', $relativeTo = null) {
        if (!$relativeTo)
            $relativeTo = time();
        $date2 = self::StrToTime_Filter($date, $relativeTo);
        
        list($Y, $M, $D) = self::ExplodeGeneralDate($date2);
        if ($Y > 1500) { //then it is Jalali Date
            $a = self::gregorian_to_jalali($Y, $M, $D);
            return implode($sep, $a);
        } else {
            return $date2;
        }
    }

    /**
     * 
     * @param type $fromTime
     * @return type
     * * returns exploded date from string Date
     */
    private static function ExplodeGeneralDate($fromTime) {
        $fromTime = str_replace('-', '/', $fromTime);
        $jdatestr = explode(' ', $fromTime);
        $gFromParam = explode('/', $jdatestr[0]);
        if (count($gFromParam) != 3)
            throw new Exception('Date input is not valid!');
        $gY = $gFromParam[0];
        $gM = $gFromParam[1];
        $gD = $gFromParam[2];
        return array($gY, $gM, $gD);
    }

    /**
     * 
     * @param type $fromTime
     * @return array
     * returns exploded date from string gregorian input
     */
    private static function ExplodeToJalali($fromTime) {
        list($gY, $gM, $gD) = self::ExplodeGeneralDate($fromTime);
        return self::gregorian_to_jalali($gY, $gM, $gD);
    }

    private static function mah2fasl($m) {
        return round(($m - 1) / 3);
    }

    private static function fasl2mah($f) {
        return $f * 3 + 1;
    }

    public static $PersianSeasons = array('بهار',
        'تابستان',
        'پاییز',
        'زمستان');
    public static $PersianMonthes = array('فروردین',
        'اردیبهشت',
        'خرداد',
        'تیر',
        'مرداد',
        'شهریور',
        'مهر',
        'آبان',
        'آذر',
        'دی',
        'بهمن',
        'اسفند');

}
