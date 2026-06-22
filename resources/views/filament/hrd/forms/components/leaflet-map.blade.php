@php
    $latPath = $field->getLatStatePath();
    $lngPath = $field->getLngStatePath();
    $radiusPath = $field->getRadiusStatePath();
    $defaultLat = $field->getDefaultLat();
    $defaultLng = $field->getDefaultLng();
    $defaultZoom = $field->getDefaultZoom();
    $mapId = 'leaflet-map-' . md5($field->getLivewire()->getName() . $field->getStatePath());
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div
    wire:ignore
    x-data="{
        map: null,
        marker: null,
        circle: null,
        latPath: @js($latPath),
        lngPath: @js($lngPath),
        radiusPath: @js($radiusPath),
        defaultLat: @js($defaultLat),
        defaultLng: @js($defaultLng),
        defaultZoom: @js($defaultZoom),

        initMap() {
            this.$nextTick(() => {
                this.createMap();
                // Re-init after Livewire saves and re-renders
                this.$wire.on('message', () => {
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        } else {
                            this.createMap();
                        }
                    }, 300);
                });
            });
        },

        createMap() {
            const el = document.getElementById(@js($mapId));
            if (!el || el._leaflet_id) return;

            const lat = parseFloat(this.$wire.$get(this.latPath)) || this.defaultLat;
            const lng = parseFloat(this.$wire.$get(this.lngPath)) || this.defaultLng;
            const radius = parseInt(this.$wire.$get(this.radiusPath)) || 100;

            this.map = L.map(el).setView([lat, lng], this.defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);

            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
            this.circle = L.circle([lat, lng], {
                radius: radius,
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.15,
                weight: 2,
            }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.setPosition(pos.lat, pos.lng);
            });

            this.map.on('click', (e) => {
                this.setPosition(e.latlng.lat, e.latlng.lng);
            });

            setTimeout(() => this.map.invalidateSize(), 200);
        },

        setPosition(lat, lng) {
            this.marker.setLatLng([lat, lng]);
            this.circle.setLatLng([lat, lng]);
            this.$wire.$set(this.latPath, parseFloat(lat.toFixed(8)));
            this.$wire.$set(this.lngPath, parseFloat(lng.toFixed(8)));
        },

        updateRadius(value) {
            const r = parseInt(value);
            this.$wire.$set(this.radiusPath, r);
            if (this.circle) {
                this.circle.setRadius(r);
            }
        }
    }"
    x-init="initMap()"
>
    <div id="{{ $mapId }}" style="height: 350px; border-radius: 0.5rem; z-index: 0;"></div>

    <div class="mt-3 flex items-center gap-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
            Geofence Radius
        </label>
        <input
            type="range"
            min="50"
            max="500"
            step="10"
            :value="$wire.$get('{{ $radiusPath }}') || 100"
            x-on:input="updateRadius($event.target.value)"
            class="hidden"
        />
        <span class="text-sm font-mono text-gray-600 dark:text-gray-400 min-w-[4rem] text-right" x-text="($wire.$get('{{ $radiusPath }}') || 100) + ' m'">
            100 m
        </span>
    </div>

    <div class="mt-2 flex gap-4 text-xs text-gray-500 dark:text-gray-400">
        <span>📍 Lat: <span x-text="$wire.$get('{{ $latPath }}') || '{{ $defaultLat }}'"></span></span>
        <span>📍 Lng: <span x-text="$wire.$get('{{ $lngPath }}') || '{{ $defaultLng }}'"></span></span>
    </div>
</div>
