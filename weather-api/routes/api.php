<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/weather', function (Request $request) {
    $city = $request->query('city', 'Tacloban, Leyte, PH'); // Default city
    $apiKey = env('WEATHER_API_KEY');

    $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
        'q' => $city,
        'appid' => $apiKey,
        'units' => 'metric'
    ]);

    return $response->json();
});
