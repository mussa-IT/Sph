<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    private array $exchangeRates = [];
    private array $supportedCurrencies = [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'code' => 'USD'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'code' => 'EUR'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'code' => 'GBP'],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'code' => 'JPY'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'code' => 'CAD'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'code' => 'AUD'],
        'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'code' => 'CHF'],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'code' => 'CNY'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'code' => 'INR'],
        'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$', 'code' => 'BRL'],
        'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$', 'code' => 'MXN'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'code' => 'ZAR'],
        'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'code' => 'KES'],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'code' => 'NGN'],
        'EGP' => ['name' => 'Egyptian Pound', 'symbol' => '£', 'code' => 'EGP'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SR', 'code' => 'SAR'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'code' => 'AED'],
        'THB' => ['name' => 'Thai Baht', 'symbol' => '฿', 'code' => 'THB'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'code' => 'SGD'],
        'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'code' => 'HKD'],
        'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'code' => 'NZD'],
        'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr', 'code' => 'SEK'],
        'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr', 'code' => 'NOK'],
        'DKK' => ['name' => 'Danish Krone', 'symbol' => 'kr', 'code' => 'DKK'],
        'PLN' => ['name' => 'Polish Zloty', 'symbol' => 'zł', 'code' => 'PLN'],
        'RUB' => ['name' => 'Russian Ruble', 'symbol' => '₽', 'code' => 'RUB'],
        'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺', 'code' => 'TRY'],
        'ILS' => ['name' => 'Israeli Shekel', 'symbol' => '₪', 'code' => 'ILS'],
        'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩', 'code' => 'KRW'],
        'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'code' => 'IDR'],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'code' => 'MYR'],
        'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱', 'code' => 'PHP'],
        'VND' => ['name' => 'Vietnamese Dong', 'symbol' => '₫', 'code' => 'VND'],
    ];

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    public function getCurrencyInfo(string $code): ?array
    {
        return $this->supportedCurrencies[strtoupper($code)] ?? null;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = $this->getExchangeRate($from, $to);
        return $amount * $rate;
    }

    public function getExchangeRate(string $from, string $to): float
    {
        $cacheKey = "exchange_rate_{$from}_{$to}";
        
        return Cache::remember($cacheKey, 3600, function () use ($from, $to) {
            if ($from === $to) {
                return 1.0;
            }

            // Try to get from cached rates
            $rates = $this->getExchangeRates();
            
            if (isset($rates[$from][$to])) {
                return $rates[$from][$to];
            }

            if (isset($rates['USD'][$from]) && isset($rates['USD'][$to])) {
                return $rates['USD'][$to] / $rates['USD'][$from];
            }

            // Fallback to API call
            return $this->fetchExchangeRate($from, $to);
        });
    }

    public function getExchangeRates(): array
    {
        return Cache::remember('exchange_rates', 3600, function () {
            return $this->fetchExchangeRates();
        });
    }

    private function fetchExchangeRates(): array
    {
        try {
            // Try multiple sources for reliability
            $sources = [
                'fixer' => $this->fetchFromFixer(),
                'exchangerate' => $this->fetchFromExchangeRate(),
                'coingecko' => $this->fetchFromCoinGecko(),
            ];

            foreach ($sources as $source => $rates) {
                if ($rates && !empty($rates)) {
                    return $rates;
                }
            }

            // Fallback to hardcoded rates
            return $this->getFallbackRates();
        } catch (\Exception $e) {
            return $this->getFallbackRates();
        }
    }

    private function fetchFromFixer(): ?array
    {
        try {
            $apiKey = config('services.fixer.api_key');
            if (!$apiKey) {
                return null;
            }

            $response = Http::get("https://api.fixer.io/latest", [
                'access_key' => $apiKey,
                'base' => 'USD',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rates = [];
                
                foreach ($data['rates'] as $currency => $rate) {
                    $rates[$currency] = $rate;
                }

                return ['USD' => $rates];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function fetchFromExchangeRate(): ?array
    {
        try {
            $response = Http::get("https://open.er-api.com/v6/latest/USD");

            if ($response->successful()) {
                $data = $response->json();
                return ['USD' => $data['rates']];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function fetchFromCoinGecko(): ?array
    {
        try {
            $response = Http::get("https://api.coingecko.com/api/v3/exchange_rates");

            if ($response->successful()) {
                $data = $response->json();
                $rates = [];
                
                foreach ($data['rates'] as $currency => $rate) {
                    $rates[$currency] = $rate['value'];
                }

                return ['USD' => $rates];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function getFallbackRates(): array
    {
        return [
            'USD' => [
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.0,
                'CAD' => 1.25,
                'AUD' => 1.35,
                'CHF' => 0.92,
                'CNY' => 6.45,
                'INR' => 74.0,
                'BRL' => 5.2,
                'MXN' => 20.0,
                'ZAR' => 15.0,
                'KES' => 115.0,
                'NGN' => 410.0,
                'EGP' => 15.7,
                'SAR' => 3.75,
                'AED' => 3.67,
                'THB' => 33.0,
                'SGD' => 1.35,
                'HKD' => 7.8,
                'NZD' => 1.45,
                'SEK' => 8.6,
                'NOK' => 8.4,
                'DKK' => 6.3,
                'PLN' => 3.8,
                'RUB' => 74.0,
                'TRY' => 8.5,
                'ILS' => 3.3,
                'KRW' => 1180.0,
                'IDR' => 14000.0,
                'MYR' => 4.2,
                'PHP' => 50.0,
                'VND' => 23000.0,
            ]
        ];
    }

    public function formatAmount(float $amount, string $currency, bool $withSymbol = true): string
    {
        $info = $this->getCurrencyInfo($currency);
        if (!$info) {
            return number_format($amount, 2);
        }

        $symbol = $withSymbol ? $info['symbol'] : '';
        $formattedAmount = $this->formatNumberForCurrency($amount, $currency);

        switch ($currency) {
            case 'USD':
            case 'CAD':
            case 'AUD':
            case 'NZD':
            case 'HKD':
            case 'SGD':
                return $symbol . $formattedAmount;
            
            case 'EUR':
            case 'GBP':
            case 'CHF':
                return $formattedAmount . $symbol;
            
            case 'JPY':
            case 'KRW':
            case 'VND':
                return $symbol . number_format($amount, 0);
            
            case 'INR':
            case 'BRL':
            case 'MXN':
            case 'ZAR':
            case 'KES':
            case 'NGN':
            case 'EGP':
            case 'SAR':
            case 'AED':
            case 'THB':
            case 'MYR':
            case 'PHP':
                return $symbol . $formattedAmount;
            
            default:
                return $symbol . $formattedAmount;
        }
    }

    private function formatNumberForCurrency(float $amount, string $currency): string
    {
        $decimalPlaces = $this->getDecimalPlaces($currency);
        
        return number_format($amount, $decimalPlaces);
    }

    private function getDecimalPlaces(string $currency): int
    {
        $zeroDecimalCurrencies = [
            'JPY', 'KRW', 'VND', 'CLP', 'PYG', 'UGX', 'RWF', 'BIF', 'GNF', 'KMF', 'MGF', 
            'MRO', 'MUR', 'MZN', 'RSD', 'SLL', 'SZL', 'TJS', 'TMM', 'TND', 'TOP', 'UZS', 'VUV', 'XAF',
            'XOF', 'XPF', 'YER'
        ];

        return in_array(strtoupper($currency), $zeroDecimalCurrencies) ? 0 : 2;
    }

    public function detectUserCurrency(): string
    {
        // Try to detect from user's location
        $userCurrency = $this->detectFromIP();
        
        if ($userCurrency && $this->getCurrencyInfo($userCurrency)) {
            return $userCurrency;
        }

        // Try to detect from browser locale
        $browserCurrency = $this->detectFromLocale();
        
        if ($browserCurrency && $this->getCurrencyInfo($browserCurrency)) {
            return $browserCurrency;
        }

        // Fallback to USD
        return 'USD';
    }

    private function detectFromIP(): ?string
    {
        try {
            $ip = request()->ip();
            
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return null;
            }

            $response = Http::get("http://ip-api.com/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['countryCode'] ?? null;
                
                return $this->getCurrencyByCountry($countryCode);
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function detectFromLocale(): ?string
    {
        $locale = request()->header('Accept-Language');
        
        if ($locale) {
            // Extract primary language
            $primaryLocale = explode(',', $locale)[0];
            $languageCode = explode('-', $primaryLocale)[0];
            
            return $this->getCurrencyByLanguage($languageCode);
        }

        return null;
    }

    private function getCurrencyByCountry(?string $countryCode): ?string
    {
        $countryToCurrency = [
            'US' => 'USD',
            'CA' => 'CAD',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'NL' => 'EUR',
            'BE' => 'EUR',
            'AT' => 'EUR',
            'IE' => 'EUR',
            'PT' => 'EUR',
            'FI' => 'EUR',
            'GR' => 'EUR',
            'JP' => 'JPY',
            'AU' => 'AUD',
            'NZ' => 'NZD',
            'CH' => 'CHF',
            'SE' => 'SEK',
            'NO' => 'NOK',
            'DK' => 'DKK',
            'PL' => 'PLN',
            'RU' => 'RUB',
            'TR' => 'TRY',
            'IL' => 'ILS',
            'KR' => 'KRW',
            'CN' => 'CNY',
            'IN' => 'INR',
            'BR' => 'BRL',
            'MX' => 'MXN',
            'ZA' => 'ZAR',
            'KE' => 'KES',
            'NG' => 'NGN',
            'EG' => 'EGP',
            'SA' => 'SAR',
            'AE' => 'AED',
            'TH' => 'THB',
            'SG' => 'SGD',
            'HK' => 'HKD',
            'ID' => 'IDR',
            'MY' => 'MYR',
            'PH' => 'PHP',
            'VN' => 'VND',
        ];

        return $countryToCurrency[$countryCode] ?? null;
    }

    private function getCurrencyByLanguage(?string $languageCode): ?string
    {
        $languageToCurrency = [
            'en' => 'USD',
            'fr' => 'EUR',
            'de' => 'EUR',
            'it' => 'EUR',
            'es' => 'EUR',
            'pt' => 'EUR',
            'nl' => 'EUR',
            'ja' => 'JPY',
            'ko' => 'KRW',
            'zh' => 'CNY',
            'ar' => 'SAR',
            'he' => 'ILS',
            'ru' => 'RUB',
            'tr' => 'TRY',
            'th' => 'THB',
            'vi' => 'VND',
            'id' => 'IDR',
            'ms' => 'MYR',
            'tl' => 'PHP',
            'hi' => 'INR',
            'pt' => 'BRL',
            'es' => 'MXN',
            'af' => 'ZAR',
            'sw' => 'KES',
        ];

        return $languageToCurrency[$languageCode] ?? null;
    }

    public function validateCurrency(string $currency): bool
    {
        return isset($this->supportedCurrencies[strtoupper($currency)]);
    }

    public function getCurrencySymbol(string $currency): string
    {
        $info = $this->getCurrencyInfo($currency);
        return $info ? $info['symbol'] : '';
    }

    public function getCurrencyName(string $currency): string
    {
        $info = $this->getCurrencyInfo($currency);
        return $info ? $info['name'] : '';
    }

    public function refreshExchangeRates(): bool
    {
        try {
            Cache::forget('exchange_rates');
            $rates = $this->fetchExchangeRates();
            
            if (!empty($rates)) {
                Cache::put('exchange_rates', $rates, 3600);
                return true;
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return false;
    }

    public function getHistoricalRates(string $from, string $to, string $date): ?float
    {
        $cacheKey = "historical_rate_{$from}_{$to}_{$date}";
        
        return Cache::remember($cacheKey, 86400 * 7, function () use ($from, $to, $date) {
            try {
                $response = Http::get("https://api.fixer.io/{$date}", [
                    'access_key' => config('services.fixer.api_key'),
                    'base' => $from,
                    'symbols' => $to,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['rates'][$to] ?? null;
                }
            } catch (\Exception $e) {
                // Log error if needed
            }

            return null;
        });
    }

    public function getCurrencyTrends(string $currency, int $days = 30): array
    {
        $cacheKey = "currency_trends_{$currency}_{$days}";
        
        return Cache::remember($cacheKey, 3600, function () use ($currency, $days) {
            $trends = [];
            $endDate = now();
            $startDate = $endDate->copy()->subDays($days);

            for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                $rate = $this->getHistoricalRates('USD', $currency, $date->format('Y-m-d'));
                
                if ($rate !== null) {
                    $trends[] = [
                        'date' => $date->format('Y-m-d'),
                        'rate' => $rate,
                    ];
                }
            }

            return $trends;
        });
    }
}
