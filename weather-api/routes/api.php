<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/weather', function (Request $request) {
    $city = $request->query('city', 'Tacloban City,Leyte,Visayas');
    $apiKey = env('WEATHER_API_KEY');

    try {
        $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // Add a simplified rain probability based on clouds and humidity
            $data['pop'] = min(($data['clouds']['all'] * $data['main']['humidity']) / 10000, 1);
            return $data;
        }

        return response()->json([
            'error' => 'City not found or API error'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Weather service unavailable'
        ], 500);
    }
});

Route::get('/forecast', function (Request $request) {
    $city = $request->query('city', 'Tacloban City,Leyte,Visayas');
    $apiKey = env('WEATHER_API_KEY');

    try {
        $response = Http::get("https://api.openweathermap.org/data/2.5/forecast", [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json([
            'error' => 'City not found or API error'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Weather service unavailable'
        ], 500);
    }
});