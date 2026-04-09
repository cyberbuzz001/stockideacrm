<div x-data="{ 
        wins: [],
        addWin(data) {
            const win = {
                id: Date.now(),
                message: data.message,
                agent: data.agentName,
                type: data.type
            };
            this.wins.unshift(win);
            if(this.wins.length > 5) this.wins.pop();
            // Tactile Celebration for Mobile
            if(data.type === 'win') window.CRMHaptics?.celebrate();
        }
    }"
    x-init="
        window.Echo.channel('velocity-public')
            .listen('.VelocityUpdate', (e) => {
                addWin(e);
            });
    "
    class="relative h-10 flex items-center overflow-hidden bg-white/40 backdrop-blur-xl border-y border-white/50 w-full">
    
    <div class="flex-none bg-indigo-600 text-white px-4 h-full flex items-center z-10 shadow-lg">
        <span class="text-[10px] font-black uppercase tracking-widest whitespace-nowrap flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
            </span>
            Live Velocity
        </span>
    </div>

    <div class="flex-auto relative h-full overflow-hidden flex items-center px-4">
        <div class="flex items-center gap-8 animate-marquee whitespace-nowrap">
            <template x-if="wins.length === 0">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Awaiting live market wins...</span>
            </template>
            <template x-for="win in wins" :key="win.id">
                <div class="flex items-center gap-3 bg-white/60 py-1 px-3 rounded-full border border-white/80 shadow-sm animate-fade-in">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-black text-indigo-600" x-text="win.agent.substring(0,2).toUpperCase()"></div>
                    <p class="text-[11px] font-bold text-slate-700">
                        <span class="text-indigo-600" x-text="'@' + win.agent"></span>
                        <span x-text="win.message"></span>
                    </p>
                </div>
            </template>
            <!-- Duplicate for infinite scroll if many wins -->
        </div>
    </div>
</div>

<style>
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-marquee {
    display: flex;
    white-space: nowrap;
    animation: marquee 30s linear infinite;
}
.animate-marquee:hover {
    animation-play-state: paused;
}
</style>
