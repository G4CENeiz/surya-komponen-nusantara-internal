<div
    x-data="{ time: 'loading...' }"
    x-init="
        const tick = () => {
            time = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true,
            });
        };
        tick();
        setInterval(tick, 1000);
    "
    class="text-center"
>
    <div class="text-4xl font-bold tracking-wider font-mono" x-text="time"></div>
    <div class="text-sm opacity-60 mt-2">{{ now()->format('l, F j, Y') }}</div>
</div>
