<template>
  <div class="h-screen flex flex-col">
    <!-- Top Half: Weather Component (50%) -->
    <div class="h-1/2 w-full">
      <Weather @location-updated="updateLocation" />
    </div>
    
    <!-- Bottom Half: Map Component (50%) -->
    <div class="h-1/2 w-full">
      <Map :coordinates="coordinates" :city-name="cityName" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Weather from './components/Weather.vue'
import Map from './components/Map.vue'

const coordinates = ref([10.3157, 123.8854]) // Default: Cebu
const cityName = ref('Cebu City')

const updateLocation = (newCoords, weatherData) => {
  coordinates.value = newCoords
  if (weatherData && weatherData.name) {
    cityName.value = weatherData.name
  }
}
</script>