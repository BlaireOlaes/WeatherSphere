<script setup>
import { ref, computed } from "vue";
import axios from "axios";

const city = ref("cebu");
const weather = ref(null);
const forecast = ref(null);
const isLoading = ref(false);
const emit = defineEmits(['location-updated'])

const fetchWeather = async () => {
  isLoading.value = true;
  try {
    // Fetch current weather
    const weatherResponse = await axios.get(`http://127.0.0.1:8000/api/weather?city=${city.value}`);
    weather.value = weatherResponse.data;

    // Fetch 7-day forecast
    const forecastResponse = await axios.get(`http://127.0.0.1:8000/api/forecast?city=${city.value}`);
    forecast.value = forecastResponse.data;

    // Emit new coordinates to parent
    if (weather.value && weather.value.coord) {
      const { lat, lon } = weather.value.coord
      emit('location-updated', [lat, lon], weather.value)
    }
  } catch (error) {
    console.error("Error fetching weather:", error);
  } finally {
    isLoading.value = false;
  }
};

const getRainProbability = () => {
  if (!weather.value || !weather.value.pop) return 0;
  return Math.round(weather.value.pop * 100);
};

const getRainStatus = computed(() => {
  if (!weather.value) return '';

  if (isRaining()) {
    return `📢 It's currently raining in ${weather.value.name}`;
  }

  if (weather.value.rain && weather.value.rain['1h']) {
    return `🌧 Rain expected with ${weather.value.rain['1h']}mm precipitation (${getRainProbability()}% chance)`;
  }

  return `🌤 ${getRainProbability()}% chance of rain`;
});

const isRaining = () => {
  if (!weather.value) return false;
  return weather.value.weather[0].main === 'Rain';
};

// Get daily forecast for the week
const getDailyForecast = computed(() => {
  if (!forecast.value || !forecast.value.list) return [];
  
  const daily = {};
  forecast.value.list.forEach(item => {
    const date = new Date(item.dt * 1000);
    const dayKey = date.toDateString();
    
    if (!daily[dayKey]) {
      daily[dayKey] = {
        date: date,
        temp_max: item.main.temp_max,
        temp_min: item.main.temp_min,
        description: item.weather[0].description,
        icon: item.weather[0].main,
        humidity: item.main.humidity,
        pop: item.pop
      };
    } else {
      // Update max/min temperatures
      daily[dayKey].temp_max = Math.max(daily[dayKey].temp_max, item.main.temp_max);
      daily[dayKey].temp_min = Math.min(daily[dayKey].temp_min, item.main.temp_min);
    }
  });
  
  // Return only 5 days (what the API actually provides)
  return Object.values(daily).slice(0, 5);
});

const getWeatherIcon = (condition) => {
  const icons = {
    'Clear': '☀️',
    'Clouds': '☁️',
    'Rain': '🌧️',
    'Drizzle': '🌦️',
    'Thunderstorm': '⛈️',
    'Snow': '❄️',
    'Mist': '🌫️',
    'Fog': '🌫️'
  };
  return icons[condition] || '🌤️';
};

const getDayName = (date) => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  return days[date.getDay()];
};

fetchWeather();
</script>

<template>
  <div class="h-auto bg-[#0a0a0a] text-white shadow-md overflow-y-auto border-b border-gray-700">
    <div class="p-3">
      <!-- Header Section -->
      <h1 class="text-2xl font-bold text-cyan-400 mb-1 font-sans tracking-wide">WeatherSphere</h1>

      <input
        v-model="city"
        class="w-full p-2 mb-2 rounded bg-[#1a1a1a] text-white border border-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
        placeholder="Enter City"
        @keyup.enter="fetchWeather"
      />

      <button
        @click="fetchWeather"
        :disabled="isLoading"
        class="w-full mb-3 bg-cyan-600 text-white px-4 py-2 rounded hover:bg-cyan-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:opacity-50"
      >
        {{ isLoading ? 'Loading...' : 'GET FORECAST' }}
      </button>

      <!-- Side by Side Layout: Current Weather + Forecast -->
      <div v-if="weather" class="grid grid-cols-2 gap-2">
        <!-- Left Side: Current Weather -->
        <div class="bg-[#1f1f1f] rounded shadow-lg p-2">
          <h2 class="text-lg font-semibold text-cyan-300 mb-2">{{ weather.name }}</h2>

          <div class="space-y-1">
            <p class="text-s pt-1 text-gray-400">{{ new Date().toLocaleString() }}</p>
            <p class="text-s pt-1">🌡 {{ Math.round(weather.main.temp) }}°C</p>
            <p class="text-s pt-1">💨 {{ weather.wind.speed }} m/s</p>
            <p class="text-s pt-1">💧 {{ weather.main.humidity }}%</p>
            <p class="text-s pt-1">☁ {{ weather.weather[0].description }}</p>

            <div class="mt-2 p-3 bg-[#292929] rounded text-cyan-300">
              <p class="font-bold text-m">{{ getRainStatus }}</p>
            </div>

            <p v-if="weather.rain" class="text-xs text-gray-400">
              Rain: {{ weather.rain['1h'] }}mm
            </p>
          </div>
        </div>

       <div class="bg-[#1f1f1f] rounded shadow-lg p-2 ">
          <h3 class="text-lg font-semibold text-cyan-300 mb-2">5 Day Forecast</h3>
          
          <div v-if="forecast && getDailyForecast.length > 0" class="space-y-1 max-h-48 overflow-y-auto">
            <div 
              v-for="(day, index) in getDailyForecast" 
              :key="index"
              class="bg-[#292929] rounded p-2 hover:bg-[#333333] transition-colors"
            >
              <div class="flex items-center justify-between p-2">
                <!-- Day and Icon -->
                <div class="flex items-center space-x-2">
                  <span class="text-lg">{{ getWeatherIcon(day.icon) }}</span>
                  <div>
                    <p class="font-medium text-xs">
                      {{ index === 0 ? 'Today' : getDayName(day.date) }}
                    </p>
                    <p class="text-xs text-gray-400 capitalize">{{ day.description }}</p>
                  </div>
                </div>
                
                <!-- Temperature -->
                <div class="text-right">
                  <div class="flex items-center space-x-1">
                    <span class="font-bold text-xs">{{ Math.round(day.temp_max) }}°</span>
                    <span class="text-gray-400 text-xs">{{ Math.round(day.temp_min) }}°</span>
                  </div>
                  <p class="text-xs text-gray-400">🌧 {{ Math.round(day.pop * 100) }}%</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading State for Forecast -->
          <div v-else-if="isLoading" class="text-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-500 mx-auto mb-1"></div>
            <p class="text-xs text-gray-400">Loading forecast...</p>
          </div>
        </div>
      </div>

      <!-- Loading State for Initial Load -->
      <div v-else-if="isLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-500 mx-auto mb-2"></div>
        <p class="text-sm text-gray-400">Loading weather data...</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #1a1a1a;
}

::-webkit-scrollbar-thumb {
  background: #0092B8;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #00B4D8;
}
</style>