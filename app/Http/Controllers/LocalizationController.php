<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LocalizationController extends Controller
{
    private CurrencyService $currencyService;
    private TimezoneService $timezoneService;

    public function __construct(CurrencyService $currencyService, TimezoneService $timezoneService)
    {
        $this->currencyService = $currencyService;
        $this->timezoneService = $timezoneService;
    }

    public function currencies(): JsonResponse
    {
        return response()->json([
            'currencies' => $this->currencyService->getSupportedCurrencies(),
        ]);
    }

    public function timezones(): JsonResponse
    {
        return response()->json([
            'timezones' => $this->timezoneService->getSupportedTimezones(),
            'common_timezones' => $this->timezoneService->getCommonTimezones(),
        ]);
    }

    public function convertCurrency(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'from' => ['required', 'string', 'size:3'],
            'to' => ['required', 'string', 'size:3'],
        ]);

        $from = strtoupper($request->from);
        $to = strtoupper($request->to);

        if (!$this->currencyService->validateCurrency($from)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid source currency',
            ], 422);
        }

        if (!$this->currencyService->validateCurrency($to)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid target currency',
            ], 422);
        }

        $convertedAmount = $this->currencyService->convert($request->amount, $from, $to);
        $exchangeRate = $this->currencyService->getExchangeRate($from, $to);

        return response()->json([
            'success' => true,
            'original_amount' => $request->amount,
            'converted_amount' => $convertedAmount,
            'from_currency' => $from,
            'to_currency' => $to,
            'exchange_rate' => $exchangeRate,
            'formatted_original' => $this->currencyService->formatAmount($request->amount, $from),
            'formatted_converted' => $this->currencyService->formatAmount($convertedAmount, $to),
        ]);
    }

    public function exchangeRates(Request $request): JsonResponse
    {
        $request->validate([
            'base' => ['nullable', 'string', 'size:3'],
            'targets' => ['nullable', 'array'],
            'targets.*' => ['string', 'size:3'],
        ]);

        $base = strtoupper($request->base ?? 'USD');
        $targets = $request->targets ?? array_keys($this->currencyService->getSupportedCurrencies());

        if (!$this->currencyService->validateCurrency($base)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid base currency',
            ], 422);
        }

        $rates = [];
        foreach ($targets as $target) {
            $target = strtoupper($target);
            if ($this->currencyService->validateCurrency($target)) {
                $rates[$target] = $this->currencyService->getExchangeRate($base, $target);
            }
        }

        return response()->json([
            'success' => true,
            'base_currency' => $base,
            'rates' => $rates,
            'updated_at' => now()->toISOString(),
        ]);
    }

    public function detectUserSettings(): JsonResponse
    {
        $detectedCurrency = $this->currencyService->detectUserCurrency();
        $detectedTimezone = $this->timezoneService->detectUserTimezone();

        return response()->json([
            'currency' => [
                'detected' => $detectedCurrency,
                'info' => $detectedCurrency ? $this->currencyService->getCurrencyInfo($detectedCurrency) : null,
            ],
            'timezone' => [
                'detected' => $detectedTimezone,
                'info' => $detectedTimezone ? $this->timezoneService->getTimezoneInfo($detectedTimezone) : null,
            ],
        ]);
    }

    public function setUserPreferences(Request $request): JsonResponse
    {
        if (!Auth::user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $request->validate([
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $settings = $user->settings ?? [];

        if ($request->currency) {
            $currency = strtoupper($request->currency);
            if ($this->currencyService->validateCurrency($currency)) {
                $settings['currency'] = $currency;
            }
        }

        if ($request->timezone) {
            $timezone = $request->timezone;
            if ($this->timezoneService->validateTimezone($timezone)) {
                $settings['timezone'] = $timezone;
            }
        }

        $user->update(['settings' => $settings]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'settings' => $settings,
        ]);
    }

    public function getUserPreferences(): JsonResponse
    {
        if (!Auth::user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $user = Auth::user();
        $settings = $user->settings ?? [];

        $currency = $settings['currency'] ?? $this->currencyService->detectUserCurrency();
        $timezone = $settings['timezone'] ?? $this->timezoneService->detectUserTimezone();

        return response()->json([
            'success' => true,
            'preferences' => [
                'currency' => [
                    'code' => $currency,
                    'info' => $this->currencyService->getCurrencyInfo($currency),
                ],
                'timezone' => [
                    'code' => $timezone,
                    'info' => $this->timezoneService->getTimezoneInfo($timezone),
                ],
            ],
        ]);
    }

    public function formatAmount(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'with_symbol' => ['nullable', 'boolean'],
        ]);

        $currency = strtoupper($request->currency);
        $withSymbol = $request->boolean('with_symbol', true);

        if (!$this->currencyService->validateCurrency($currency)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid currency',
            ], 422);
        }

        $formatted = $this->currencyService->formatAmount($request->amount, $currency, $withSymbol);

        return response()->json([
            'success' => true,
            'formatted' => $formatted,
        ]);
    }

    public function convertTime(Request $request): JsonResponse
    {
        $request->validate([
            'datetime' => ['required', 'date'],
            'from_timezone' => ['required', 'string'],
            'to_timezone' => ['required', 'string'],
            'format' => ['nullable', 'string'],
        ]);

        $fromTimezone = $request->from_timezone;
        $toTimezone = $request->to_timezone;
        $format = $request->format ?? 'Y-m-d H:i:s';

        if (!$this->timezoneService->validateTimezone($fromTimezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid source timezone',
            ], 422);
        }

        if (!$this->timezoneService->validateTimezone($toTimezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid target timezone',
            ], 422);
        }

        $convertedDateTime = $this->timezoneService->convertToTimezone(
            $request->datetime,
            $fromTimezone,
            $toTimezone
        );

        return response()->json([
            'success' => true,
            'original' => [
                'datetime' => $request->datetime,
                'timezone' => $fromTimezone,
                'formatted' => $this->timezoneService->formatForTimezone($request->datetime, $fromTimezone, $format),
            ],
            'converted' => [
                'datetime' => $convertedDateTime->toISOString(),
                'timezone' => $toTimezone,
                'formatted' => $convertedDateTime->format($format),
            ],
        ]);
    }

    public function getLocalTimes(Request $request): JsonResponse
    {
        $request->validate([
            'datetime' => ['required', 'date'],
            'timezones' => ['required', 'array'],
            'timezones.*' => ['string'],
        ]);

        $datetime = $request->datetime;
        $timezones = $request->timezones;

        $localTimes = $this->timezoneService->getLocalTimeForTimezones($datetime, $timezones);

        return response()->json([
            'success' => true,
            'datetime' => $datetime,
            'local_times' => $localTimes,
        ]);
    }

    public function getWorkingHours(Request $request): JsonResponse
    {
        $request->validate([
            'timezone' => ['required', 'string'],
            'date' => ['nullable', 'date'],
        ]);

        $timezone = $request->timezone;
        $date = $request->date;

        if (!$this->timezoneService->validateTimezone($timezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid timezone',
            ], 422);
        }

        $workingHours = $this->timezoneService->getWorkingHours($timezone, $date);

        return response()->json([
            'success' => true,
            'working_hours' => [
                'start' => $workingHours['start']->format('H:i'),
                'end' => $workingHours['end']->format('H:i'),
                'is_working_hours' => $workingHours['is_working_hours'],
                'is_weekend' => $workingHours['is_weekend'],
            ],
        ]);
    }

    public function getCurrencyTrends(Request $request): JsonResponse
    {
        $request->validate([
            'currency' => ['required', 'string', 'size:3'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $currency = strtoupper($request->currency);
        $days = $request->integer('days', 30);

        if (!$this->currencyService->validateCurrency($currency)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid currency',
            ], 422);
        }

        $trends = $this->currencyService->getCurrencyTrends($currency, $days);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'period_days' => $days,
            'trends' => $trends,
        ]);
    }

    public function refreshExchangeRates(): JsonResponse
    {
        try {
            $success = $this->currencyService->refreshExchangeRates();
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Exchange rates refreshed successfully' : 'Failed to refresh exchange rates',
                'updated_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing exchange rates: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getDSTInfo(Request $request): JsonResponse
    {
        $request->validate([
            'timezone' => ['required', 'string'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2030'],
        ]);

        $timezone = $request->timezone;
        $year = $request->integer('year', (int)date('Y'));

        if (!$this->timezoneService->validateTimezone($timezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid timezone',
            ], 422);
        }

        $isDSTActive = $this->timezoneService->isDSTActive($timezone);
        $transitions = $this->timezoneService->getDSTTransition($timezone, $year);

        return response()->json([
            'success' => true,
            'timezone' => $timezone,
            'year' => $year,
            'is_dst_active' => $isDSTActive,
            'transitions' => $transitions,
        ]);
    }

    public function getTimeDifference(Request $request): JsonResponse
    {
        $request->validate([
            'datetime1' => ['required', 'date'],
            'datetime2' => ['required', 'date'],
            'timezone' => ['nullable', 'string'],
        ]);

        $timezone = $request->timezone ?? 'UTC';

        if (!$this->timezoneService->validateTimezone($timezone)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid timezone',
            ], 422);
        }

        $difference = $this->timezoneService->getTimeDifference(
            $request->datetime1,
            $request->datetime2,
            $timezone
        );

        return response()->json([
            'success' => true,
            'difference' => $difference,
        ]);
    }
}
