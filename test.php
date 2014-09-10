<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="auto">
    <head class='noprint'>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <?php
        include_once 'JalaliDate.php';
        include_once 'Timely.php';

        $testCases = array(
            'امروز',
            'دیروز',
            'فردا',
            '2 ماه',
            '1 سال',
            '-7 روز',
            '-1 ماه',
                )
        ?>
        <?php
        foreach ($testCases as $case) {
            $res = Timely::StrToTime_Filter($case, time());
            ?>
            <div style="border-bottom: 1px gray solid;">
                <h3>StrToTime_Filter('<?php echo $case; ?>') :</h3><?php echo $res; ?>
            </div>
            <?php
        }
        ?>
    </body>
</html>