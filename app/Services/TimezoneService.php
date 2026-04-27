<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TimezoneService
{
    private array $supportedTimezones = [
        // Major timezones
        'UTC' => ['name' => 'Coordinated Universal Time', 'offset' => '+00:00', 'region' => 'Global'],
        'EST' => ['name' => 'Eastern Standard Time', 'offset' => '-05:00', 'region' => 'North America'],
        'EDT' => ['name' => 'Eastern Daylight Time', 'offset' => '-04:00', 'region' => 'North America'],
        'CST' => ['name' => 'Central Standard Time', 'offset' => '-06:00', 'region' => 'North America'],
        'CDT' => ['name' => 'Central Daylight Time', 'offset' => '-05:00', 'region' => 'North America'],
        'MST' => ['name' => 'Mountain Standard Time', 'offset' => '-07:00', 'region' => 'North America'],
        'MDT' => ['name' => 'Mountain Daylight Time', 'offset' => '-06:00', 'region' => 'North America'],
        'PST' => ['name' => 'Pacific Standard Time', 'offset' => '-08:00', 'region' => 'North America'],
        'PDT' => ['name' => 'Pacific Daylight Time', 'offset' => '-07:00', 'region' => 'North America'],
        
        // European timezones
        'GMT' => ['name' => 'Greenwich Mean Time', 'offset' => '+00:00', 'region' => 'Europe'],
        'BST' => ['name' => 'British Summer Time', 'offset' => '+01:00', 'region' => 'Europe'],
        'CET' => ['name' => 'Central European Time', 'offset' => '+01:00', 'region' => 'Europe'],
        'CEST' => ['name' => 'Central European Summer Time', 'offset' => '+02:00', 'region' => 'Europe'],
        'EET' => ['name' => 'Eastern European Time', 'offset' => '+02:00', 'region' => 'Europe'],
        'EEST' => ['name' => 'Eastern European Summer Time', 'offset' => '+03:00', 'region' => 'Europe'],
        'MSK' => ['name' => 'Moscow Standard Time', 'offset' => '+03:00', 'region' => 'Europe'],
        
        // Asian timezones
        'JST' => ['name' => 'Japan Standard Time', 'offset' => '+09:00', 'region' => 'Asia'],
        'KST' => ['name' => 'Korea Standard Time', 'offset' => '+09:00', 'region' => 'Asia'],
        'CST' => ['name' => 'China Standard Time', 'offset' => '+08:00', 'region' => 'Asia'],
        'IST' => ['name' => 'India Standard Time', 'offset' => '+05:30', 'region' => 'Asia'],
        'SGT' => ['name' => 'Singapore Time', 'offset' => '+08:00', 'region' => 'Asia'],
        'HKT' => ['name' => 'Hong Kong Time', 'offset' => '+08:00', 'region' => 'Asia'],
        'BKK' => ['name' => 'Bangkok Time', 'offset' => '+07:00', 'region' => 'Asia'],
        'WIB' => ['name' => 'Western Indonesia Time', 'offset' => '+07:00', 'region' => 'Asia'],
        
        // African timezones
        'WAT' => ['name' => 'West Africa Time', 'offset' => '+01:00', 'region' => 'Africa'],
        'CAT' => ['name' => 'Central Africa Time', 'offset' => '+02:00', 'region' => 'Africa'],
        'EAT' => ['name' => 'East Africa Time', 'offset' => '+03:00', 'region' => 'Africa'],
        'SAST' => ['name' => 'South Africa Standard Time', 'offset' => '+02:00', 'region' => 'Africa'],
        
        // Middle Eastern timezones
        'AST' => ['name' => 'Arabia Standard Time', 'offset' => '+03:00', 'region' => 'Middle East'],
        'GST' => ['name' => 'Gulf Standard Time', 'offset' => '+04:00', 'region' => 'Middle East'],
        
        // Australian timezones
        'AEST' => ['name' => 'Australian Eastern Standard Time', 'offset' => '+10:00', 'region' => 'Australia'],
        'AEDT' => ['name' => 'Australian Eastern Daylight Time', 'offset' => '+11:00', 'region' => 'Australia'],
        'ACST' => ['name' => 'Australian Central Standard Time', 'offset' => '+09:30', 'region' => 'Australia'],
        'ACDT' => ['name' => 'Australian Central Daylight Time', 'offset' => '+10:30', 'region' => 'Australia'],
        'AWST' => ['name' => 'Australian Western Standard Time', 'offset' => '+08:00', 'region' => 'Australia'],
        
        // South American timezones
        'BRT' => ['name' => 'Brasília Time', 'offset' => '-03:00', 'region' => 'South America'],
        'ART' => ['name' => 'Argentina Time', 'offset' => '-03:00', 'region' => 'South America'],
        'CLT' => ['name' => 'Chile Standard Time', 'offset' => '-04:00', 'region' => 'South America'],
        
        // Additional IANA timezones
        'America/New_York' => ['name' => 'Eastern Time', 'offset' => '-05:00', 'region' => 'North America'],
        'America/Chicago' => ['name' => 'Central Time', 'offset' => '-06:00', 'region' => 'North America'],
        'America/Denver' => ['name' => 'Mountain Time', 'offset' => '-07:00', 'region' => 'North America'],
        'America/Los_Angeles' => ['name' => 'Pacific Time', 'offset' => '-08:00', 'region' => 'North America'],
        'Europe/London' => ['name' => 'London Time', 'offset' => '+00:00', 'region' => 'Europe'],
        'Europe/Paris' => ['name' => 'Paris Time', 'offset' => '+01:00', 'region' => 'Europe'],
        'Europe/Berlin' => ['name' => 'Berlin Time', 'offset' => '+01:00', 'region' => 'Europe'],
        'Asia/Tokyo' => ['name' => 'Tokyo Time', 'offset' => '+09:00', 'region' => 'Asia'],
        'Asia/Shanghai' => ['name' => 'Shanghai Time', 'offset' => '+08:00', 'region' => 'Asia'],
        'Asia/Kolkata' => ['name' => 'Kolkata Time', 'offset' => '+05:30', 'region' => 'Asia'],
        'Asia/Dubai' => ['name' => 'Dubai Time', 'offset' => '+04:00', 'region' => 'Middle East'],
        'Australia/Sydney' => ['name' => 'Sydney Time', 'offset' => '+10:00', 'region' => 'Australia'],
        'Pacific/Auckland' => ['name' => 'Auckland Time', 'offset' => '+12:00', 'region' => 'Pacific'],
    ];

    public function getSupportedTimezones(): array
    {
        return $this->supportedTimezones;
    }

    public function getTimezoneInfo(string $timezone): ?array
    {
        return $this->supportedTimezones[$timezone] ?? null;
    }

    public function convertToTimezone($datetime, string $fromTimezone, string $toTimezone): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime, $fromTimezone);
        }

        return $datetime->setTimezone($toTimezone);
    }

    public function formatForTimezone($datetime, string $timezone, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->setTimezone($timezone)->format($format);
    }

    public function getRelativeTime($datetime, string $timezone = 'UTC'): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->setTimezone($timezone)->diffForHumans();
    }

    public function detectUserTimezone(): string
    {
        // Try to detect from user's IP location
        $ipTimezone = $this->detectFromIP();
        
        if ($ipTimezone && $this->getTimezoneInfo($ipTimezone)) {
            return $ipTimezone;
        }

        // Try to detect from browser timezone
        $browserTimezone = $this->detectFromBrowser();
        
        if ($browserTimezone && $this->getTimezoneInfo($browserTimezone)) {
            return $browserTimezone;
        }

        // Try to detect from locale
        $localeTimezone = $this->detectFromLocale();
        
        if ($localeTimezone && $this->getTimezoneInfo($localeTimezone)) {
            return $localeTimezone;
        }

        // Fallback to UTC
        return 'UTC';
    }

    private function detectFromIP(): ?string
    {
        try {
            $ip = request()->ip();
            
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return null;
            }

            $response = \Illuminate\Support\Facades\Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['countryCode'] ?? null;
                $region = $data['regionName'] ?? null;
                $city = $data['city'] ?? null;
                
                return $this->getTimezoneByLocation($countryCode, $region, $city);
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function detectFromBrowser(): ?string
    {
        $timezone = request()->header('X-Timezone') ?? request()->get('timezone');
        
        if ($timezone && $this->getTimezoneInfo($timezone)) {
            return $timezone;
        }

        return null;
    }

    private function detectFromLocale(): ?string
    {
        $locale = request()->header('Accept-Language');
        
        if ($locale) {
            $primaryLocale = explode(',', $locale)[0];
            $languageCode = explode('-', $primaryLocale)[0];
            
            return $this->getTimezoneByLanguage($languageCode);
        }

        return null;
    }

    private function getTimezoneByLocation(?string $countryCode, ?string $region, ?string $city): ?string
    {
        // Map countries and regions to timezones
        $locationMap = [
            'US' => [
                'default' => 'America/New_York',
                'California' => 'America/Los_Angeles',
                'New York' => 'America/New_York',
                'Chicago' => 'America/Chicago',
                'Denver' => 'America/Denver',
                'Texas' => 'America/Chicago',
                'Florida' => 'America/New_York',
            ],
            'CA' => [
                'default' => 'America/Toronto',
                'Ontario' => 'America/Toronto',
                'Quebec' => 'America/Montreal',
                'British Columbia' => 'America/Vancouver',
            ],
            'GB' => [
                'default' => 'Europe/London',
            ],
            'DE' => [
                'default' => 'Europe/Berlin',
            ],
            'FR' => [
                'default' => 'Europe/Paris',
            ],
            'IT' => [
                'default' => 'Europe/Rome',
            ],
            'ES' => [
                'default' => 'Europe/Madrid',
            ],
            'JP' => [
                'default' => 'Asia/Tokyo',
            ],
            'CN' => [
                'default' => 'Asia/Shanghai',
            ],
            'IN' => [
                'default' => 'Asia/Kolkata',
            ],
            'RU' => [
                'default' => 'Europe/Moscow',
            ],
            'BR' => [
                'default' => 'America/Sao_Paulo',
            ],
            'AU' => [
                'default' => 'Australia/Sydney',
                'Sydney' => 'Australia/Sydney',
                'Melbourne' => 'Australia/Melbourne',
                'Perth' => 'Australia/Perth',
            ],
            'NZ' => [
                'default' => 'Pacific/Auckland',
            ],
            'ZA' => [
                'default' => 'Africa/Johannesburg',
            ],
            'KE' => [
                'default' => 'Africa/Nairobi',
            ],
            'NG' => [
                'default' => 'Africa/Lagos',
            ],
            'EG' => [
                'default' => 'Africa/Cairo',
            ],
            'SA' => [
                'default' => 'Asia/Riyadh',
            ],
            'AE' => [
                'default' => 'Asia/Dubai',
            ],
            'TH' => [
                'default' => 'Asia/Bangkok',
            ],
            'SG' => [
                'default' => 'Asia/Singapore',
            ],
            'MY' => [
                'default' => 'Asia/Kuala_Lumpur',
            ],
            'PH' => [
                'default' => 'Asia/Manila',
            ],
            'VN' => [
                'default' => 'Asia/Ho_Chi_Minh',
            ],
            'ID' => [
                'default' => 'Asia/Jakarta',
            ],
        ];

        if (isset($locationMap[$countryCode])) {
            $countryMap = $locationMap[$countryCode];
            
            // Check for specific region/city first
            if ($region && isset($countryMap[$region])) {
                return $countryMap[$region];
            }
            
            if ($city && isset($countryMap[$city])) {
                return $countryMap[$city];
            }
            
            return $countryMap['default'] ?? null;
        }

        return null;
    }

    private function getTimezoneByLanguage(?string $languageCode): ?string
    {
        $languageMap = [
            'en' => 'UTC', // English is global, default to UTC
            'fr' => 'Europe/Paris',
            'de' => 'Europe/Berlin',
            'it' => 'Europe/Rome',
            'es' => 'Europe/Madrid',
            'pt' => 'Europe/Lisbon',
            'nl' => 'Europe/Amsterdam',
            'ja' => 'Asia/Tokyo',
            'ko' => 'Asia/Seoul',
            'zh' => 'Asia/Shanghai',
            'ar' => 'Asia/Riyadh',
            'he' => 'Asia/Jerusalem',
            'ru' => 'Europe/Moscow',
            'tr' => 'Europe/Istanbul',
            'th' => 'Asia/Bangkok',
            'vi' => 'Asia/Ho_Chi_Minh',
            'id' => 'Asia/Jakarta',
            'ms' => 'Asia/Kuala_Lumpur',
            'tl' => 'Asia/Manila',
            'hi' => 'Asia/Kolkata',
            'pt' => 'America/Sao_Paulo',
            'es' => 'America/Mexico_City',
            'af' => 'Africa/Johannesburg',
            'sw' => 'Africa/Nairobi',
        ];

        return $languageMap[$languageCode] ?? null;
    }

    public function validateTimezone(string $timezone): bool
    {
        return isset($this->supportedTimezones[$timezone]) || in_array($timezone, timezone_identifiers_list());
    }

    public function getTimezoneOffset(string $timezone): string
    {
        $info = $this->getTimezoneInfo($timezone);
        return $info ? $info['offset'] : '+00:00';
    }

    public function getTimezoneName(string $timezone): string
    {
        $info = $this->getTimezoneInfo($timezone);
        return $info ? $info['name'] : $timezone;
    }

    public function getTimezoneRegion(string $timezone): string
    {
        $info = $this->getTimezoneInfo($timezone);
        return $info ? $info['region'] : 'Unknown';
    }

    public function getWorkingHours(string $timezone, ?string $date = null): array
    {
        $date = $date ? Carbon::parse($date, $timezone) : Carbon::now($timezone);
        
        return [
            'start' => $date->copy()->setTime(9, 0, 0),
            'end' => $date->copy()->setTime(17, 0, 0),
            'is_working_hours' => $date->copy()->between($date->copy()->setTime(9, 0, 0), $date->copy()->setTime(17, 0, 0)),
            'is_weekend' => $date->isWeekend(),
        ];
    }

    public function getBusinessDays(string $timezone, int $days = 5): array
    {
        $businessDays = [];
        $date = Carbon::now($timezone);
        
        while (count($businessDays) < $days) {
            if (!$date->isWeekend()) {
                $businessDays[] = $date->copy();
            }
            $date->addDay();
        }
        
        return $businessDays;
    }

    public function getTimezonesByRegion(string $region): array
    {
        return array_filter($this->supportedTimezones, function ($timezone) use ($region) {
            return $timezone['region'] === $region;
        });
    }

    public function getCommonTimezones(): array
    {
        return [
            'UTC' => $this->getTimezoneInfo('UTC'),
            'America/New_York' => $this->getTimezoneInfo('America/New_York'),
            'America/Chicago' => $this->getTimezoneInfo('America/Chicago'),
            'America/Los_Angeles' => $this->getTimezoneInfo('America/Los_Angeles'),
            'Europe/London' => $this->getTimezoneInfo('Europe/London'),
            'Europe/Paris' => $this->getTimezoneInfo('Europe/Paris'),
            'Europe/Berlin' => $this->getTimezoneInfo('Europe/Berlin'),
            'Asia/Tokyo' => $this->getTimezoneInfo('Asia/Tokyo'),
            'Asia/Shanghai' => $this->getTimezoneInfo('Asia/Shanghai'),
            'Asia/Kolkata' => $this->getTimezoneInfo('Asia/Kolkata'),
            'Australia/Sydney' => $this->getTimezoneInfo('Australia/Sydney'),
        ];
    }

    public function formatDateTimeWithTimezone($datetime, string $timezone, string $format = 'M j, Y g:i A'): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->setTimezone($timezone)->format($format);
    }

    public function getTimeDifference($datetime1, $datetime2, string $timezone = 'UTC'): array
    {
        if (is_string($datetime1)) {
            $datetime1 = Carbon::parse($datetime1, $timezone);
        }
        
        if (is_string($datetime2)) {
            $datetime2 = Carbon::parse($datetime2, $timezone);
        }

        $diff = $datetime1->diff($datetime2);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'total_days' => $datetime1->diffInDays($datetime2),
            'total_hours' => $datetime1->diffInHours($datetime2),
            'total_minutes' => $datetime1->diffInMinutes($datetime2),
            'total_seconds' => $datetime1->diffInSeconds($datetime2),
            'human' => $datetime1->diffForHumans($datetime2),
        ];
    }

    public function isDSTActive(string $timezone, ?string $date = null): bool
    {
        $date = $date ? Carbon::parse($date, $timezone) : Carbon::now($timezone);
        return $date->isDST();
    }

    public function getDSTTransition(string $timezone, int $year): array
    {
        $transitions = [];
        
        try {
            // Get DST start and end dates for the given year
            $dstStart = Carbon::parse("March $year", $timezone);
            $dstEnd = Carbon::parse("November $year", $timezone);
            
            // Find the second Sunday in March (DST start for US)
            $dstStart->startOfMonth()->next(Carbon::SUNDAY)->addWeek();
            
            // Find the first Sunday in November (DST end for US)
            $dstEnd->startOfMonth()->next(Carbon::SUNDAY);
            
            $transitions = [
                'spring_forward' => [
                    'date' => $dstStart->format('Y-m-d'),
                    'time' => '02:00:00',
                    'timezone' => $timezone,
                ],
                'fall_back' => [
                    'date' => $dstEnd->format('Y-m-d'),
                    'time' => '02:00:00',
                    'timezone' => $timezone,
                ],
            ];
        } catch (\Exception $e) {
            // DST calculations can be complex, return empty array on error
        }
        
        return $transitions;
    }

    public function getLocalTimeForTimezones(string $datetime, array $timezones): array
    {
        $results = [];
        $baseTime = Carbon::parse($datetime, 'UTC');
        
        foreach ($timezones as $timezone) {
            if ($this->validateTimezone($timezone)) {
                $results[$timezone] = [
                    'timezone' => $timezone,
                    'name' => $this->getTimezoneName($timezone),
                    'offset' => $this->getTimezoneOffset($timezone),
                    'local_time' => $this->formatForTimezone($baseTime, $timezone),
                    'is_dst' => $this->isDSTActive($timezone, $datetime),
                ];
            }
        }
        
        return $results;
    }
}
