<div
    x-data="{
        publicIp: 'Mendeteksi...',
        locationReady: false,
        locationGranted: false,
        init() {
            this.fetchIp();
            this.fetchLocation();
        },
        async fetchIp() {
            try {
                const res = await fetch('https://api.ipify.org?format=json');
                const data = await res.json();
                this.publicIp = data.ip;
            } catch {
                this.publicIp = 'Tidak dapat mendeteksi';
            }
        },
        fetchLocation() {
            if (!navigator.geolocation) {
                this.locationReady = true;
                return;
            }
            navigator.geolocation.getCurrentPosition(
                () => {
                    this.locationGranted = true;
                    this.locationReady = true;
                },
                () => {
                    this.locationReady = true;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }"
    class="mb-4 space-y-1 text-sm text-gray-600 dark:text-gray-400"
>
    <div>
        IP: <span class="font-mono" x-text="publicIp"></span>
    </div>
    <div>
        Location:
        <template x-if="locationReady && locationGranted">
            <span class="text-green-600 dark:text-green-400">Location acquired</span>
        </template>
        <template x-if="locationReady && !locationGranted">
            <span class="text-red-500">Location unavailable</span>
        </template>
        <template x-if="!locationReady">
            <span class="text-amber-500">Mendeteksi...cation...</span>
        </template>
    </div>
</div>
