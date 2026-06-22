<div x-data="{
    cameraReady: false,
    locationReady: false,
    latitude: 0,
    longitude: 0,
    gpsAccuracy: 0,
    gpsSpeed: 0,
    watchId: null,
    init() {
        this.startCamera();
        this.startGeolocation();
    },
    destroy() {
        if (this.watchId) navigator.geolocation.clearWatch(this.watchId);
        if (this.$refs.modalVideo?.srcObject) this.$refs.modalVideo.srcObject.getTracks().forEach(t => t.stop());
    },
    async startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } });
            this.$refs.modalVideo.srcObject = stream;
            await this.$refs.modalVideo.play();
            this.cameraReady = true;
        } catch (err) { console.error('Camera error:', err); }
    },
    startGeolocation() {
        if (!navigator.geolocation) return;
        this.watchId = navigator.geolocation.watchPosition(
            (p) => {
                this.latitude = p.coords.latitude;
                this.longitude = p.coords.longitude;
                this.gpsAccuracy = p.coords.accuracy || 0;
                this.gpsSpeed = p.coords.speed || 0;
                this.locationReady = true;
                this.$wire.storeGpsData(this.latitude, this.longitude, this.gpsAccuracy, this.gpsSpeed);
            },
            (err) => console.error('Geolocation error:', err),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    },
}" class="space-y-3">

    {{-- Camera --}}
    <div class="relative w-full h-72 bg-gray-900 rounded-lg overflow-hidden">
        <video x-ref="modalVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
        <div x-show="!cameraReady" class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white">
                <x-heroicon-s-camera class="w-4 h-4 mx-auto mb-1 animate-pulse" />
                <p class="text-xs">Initializing camera...</p>
            </div>
        </div>
        <div x-show="cameraReady" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-36 h-36 border-2 border-white/50 rounded-full"></div>
        </div>
    </div>

    {{-- Map --}}
    <div class="w-full h-72 rounded-lg overflow-hidden">
        <template x-if="locationReady">
            <iframe
                x-bind:src="'https://www.openstreetmap.org/export/embed.html?bbox=' + (longitude - 0.005).toFixed(4) + '%2C' + (latitude - 0.005).toFixed(4) + '%2C' + (longitude + 0.005).toFixed(4) + '%2C' + (latitude + 0.005).toFixed(4) + '&layer=mapnik&marker=' + latitude + '%2C' + longitude"
                class="w-full h-full border-0 rounded-lg"
                loading="lazy"
            ></iframe>
        </template>
        <template x-if="!locationReady">
            <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-400">⏳ Locating...</p>
            </div>
        </template>
    </div>

    {{-- Status Line --}}
    <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
        <span x-show="!cameraReady">⏳ Camera loading...</span>
        <span x-show="!locationReady">⏳ Locating...</span>
        <span x-show="locationReady" class="text-success-600 dark:text-success-400">
            Location: <span x-text="latitude.toFixed(4)"></span>, <span x-text="longitude.toFixed(4)"></span>
        </span>
        <span class="text-success-600 dark:text-success-400">IP: {{ request()->ip() }}</span>
    </div>
</div>
