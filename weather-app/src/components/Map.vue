<template>
  <div class="relative w-full h-full bg-gray-800">
    <!-- City Badge - Top overlay -->
    <div class="absolute top-4 left-4 z-50">
      <div class="city-badge">
        <span class="location-icon">📍</span>
        <span class="city-name">{{ cityName }}</span>
      </div>
    </div>
    
    <!-- Map Container -->
    <div id="map" class="w-full h-full"></div>
  </div>
</template>

<script>
import * as L from 'leaflet'
import 'leaflet/dist/leaflet.css'

export default {
  name: 'MapView',
  props: {
    coordinates: {
      type: Array,
      required: true
    },
    cityName: {
      type: String,
      default: 'Selected City'
    }
  },
  data() {
    return {
      map: null,
      marker: null,
      cityHighlight: null,
      pulseCircles: [],
      animationId: null
    }
  },
  watch: {
    coordinates(newCoords) {
      if (this.map && this.marker) {
        this.map.setView(newCoords, 13)
        this.marker.setLatLng(newCoords)
        
        this.cleanupHighlight()
        this.addCityHighlight(newCoords)
      }
    }
  },
  methods: {
    cleanupHighlight() {
      if (this.cityHighlight) {
        this.map.removeLayer(this.cityHighlight)
        this.cityHighlight = null
      }
      
      this.pulseCircles.forEach(circle => {
        this.map.removeLayer(circle)
      })
      this.pulseCircles = []
      
      if (this.animationId) {
        cancelAnimationFrame(this.animationId)
        this.animationId = null
      }
    },
    
    addCityHighlight(coords) {
      this.cityHighlight = L.circle(coords, {
        radius: 4000,
        fillColor: '#0092B8',
        fillOpacity: 0.2,
        color: '#0092B8',
        weight: 3,
        opacity: 0.8,
        dashArray: '10, 10'
      }).addTo(this.map)
      
      this.addPulseEffect(coords)
    },
    
    addPulseEffect(coords) {
      for (let i = 1; i <= 3; i++) {
        const circle = L.circle(coords, {
          radius: 2000 * i,
          fillColor: '#0092B8',
          fillOpacity: 0.1 / i,
          color: '#0092B8',
          weight: 2,
          opacity: 0.5 / i
        }).addTo(this.map)
        
        this.pulseCircles.push(circle)
      }
      
      let scale = 1
      const animate = () => {
        scale = scale > 1.5 ? 1 : scale + 0.01
        this.pulseCircles.forEach((circle, index) => {
          const newRadius = (2000 * (index + 1)) * scale
          circle.setRadius(newRadius)
        })
        this.animationId = requestAnimationFrame(animate)
      }
      animate()
    },
    
    resizeMap() {
      if (this.map) {
        setTimeout(() => {
          this.map.invalidateSize()
        }, 100)
      }
    }
  },
  mounted() {
    // Initialize map
    this.map = L.map('map').setView(this.coordinates, 13)

    // Dark theme tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; <a href="https://carto.com/">CartoDB</a>',
      subdomains: 'abcd',
      maxZoom: 15
    }).addTo(this.map)

    // Add city highlight
    this.addCityHighlight(this.coordinates)

    // Custom marker icon
    const customIcon = L.divIcon({
      className: 'custom-marker',
      html: `<div style="
        background-color: #0092B8;
        width: 30px;
        height: 30px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,146,184,0.6);
        animation: bounce 2s infinite;
      "></div>`,
      iconSize: [30, 30],
      iconAnchor: [15, 30]
    });

    // Add marker
    this.marker = L.marker(this.coordinates, { icon: customIcon }).addTo(this.map)
    
    // Handle window resize
    window.addEventListener('resize', this.resizeMap)
  },
  
  beforeUnmount() {
    this.cleanupHighlight()
    window.removeEventListener('resize', this.resizeMap)
    if (this.map) {
      this.map.remove()
    }
  }
}
</script>

<style scoped>
/* City badge styles */
.city-badge {
  background: linear-gradient(135deg, #0092B8, #00B4D8);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(0, 146, 184, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  animation: pulse-glow 2s ease-in-out infinite;
}

.location-icon {
  margin-right: 6px;
  font-size: 16px;
}

.city-name {
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 4px 12px rgba(0, 146, 184, 0.4);
  }
  50% {
    box-shadow: 0 4px 20px rgba(0, 146, 184, 0.8), 0 0 30px rgba(0, 146, 184, 0.4);
  }
}

/* Map controls styling */
:global(.leaflet-control-zoom) {
  background-color: #1e1e1e;
  border: 1px solid #333;
}

:global(.leaflet-control-zoom-in),
:global(.leaflet-control-zoom-out) {
  color: #fff;
  background-color: #2a2a2a;
}

:global(.leaflet-control-zoom-in:hover),
:global(.leaflet-control-zoom-out:hover) {
  background-color: #0092B8;
}

/* Custom marker styles */
.custom-marker {
  background: transparent !important;
  border: none !important;
}

/* Marker bounce animation */
@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: rotate(-45deg) translateY(0);
  }
  40% {
    transform: rotate(-45deg) translateY(-10px);
  }
  60% {
    transform: rotate(-45deg) translateY(-5px);
  }
}
</style>