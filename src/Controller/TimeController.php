<?php

namespace App\Controller;

class TimeController
{
    public function minutesToHoursMinutes($minutes): string
    {
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
            $result = $hours . ' heure' . ($hours > 1 ? 's' : '');

            if ($minutes > 0) {
                $result .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }

            return $result . '.';
        }
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '.';
    }
}
