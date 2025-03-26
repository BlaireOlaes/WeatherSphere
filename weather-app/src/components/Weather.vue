<script setup>
import { ref, computed } from "vue";
import axios from "axios";

const city = ref("Tacloban");
const weather = ref(null);

const fetchWeather = async () => {
    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/weather?city=${city.value}`);
        weather.value = response.data;
    } catch (error) {
        console.error("Error fetching weather:", error);
    }
};

const isRaining = () => {
    if (!weather.value) return false;
    return weather.value.weather[0].main === 'Rain';
};

const getRainStatus = computed(() => {
    if (!weather.value) return '';
    
    if (isRaining()) {
        return `📢 It's currently raining in ${weather.value.name}`;
    }
    
    if (weather.value.rain && weather.value.rain['1h']) {
        return `🌧 Rain expected with ${weather.value.rain['1h']}mm precipitation in the next hour`;
    }
    
    return '☀ No rain expected at the moment';
});

fetchWeather();
</script>

<template>
  <div class="p-6 max-w-md mx-auto bg-blue-500 text-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold">Weather Forecast</h1>
    <input v-model="city" class="w-full p-2 mt-2 rounded text-black" placeholder="Enter City" />
    <button @click="fetchWeather" class="mt-2 bg-white text-blue-500 px-4 py-2 rounded">
      Get Forecast
    </button>

    <div v-if="weather" class="mt-4">
      <h2 class="text-xl font-semibold">{{ weather.name }}</h2>
      <div class="mt-2 p-2 bg-blue-600 rounded">
        <p class="font-medium">{{ new Date().toLocaleString() }}</p>
        <p>🌡 {{ weather.main.temp }}°C</p>
        <p>💨 {{ weather.wind.speed }} m/s</p>
        <p>☁ {{ weather.weather[0].description }}</p>
        <div class="mt-2 p-2 bg-blue-700 rounded">
          <p class="font-semibold">{{ getRainStatus }}</p>
        </div>
        <p v-if="weather.rain" class="mt-2">
          Rain volume (last 1h): {{ weather.rain['1h'] }}mm
        </p>
      </div>
    </div>
  </div>
</template>