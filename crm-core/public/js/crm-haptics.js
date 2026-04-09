/**
 * CRM Haptics Engine
 * Provides subtle tactile feedback for PWA interactions
 */
const CRMHaptics = {
    // Short assertive tap for standard actions
    tap() {
        if ('vibrate' in navigator) {
            navigator.vibrate(15);
        }
    },
    
    // Medium success pulse
    success() {
        if ('vibrate' in navigator) {
            navigator.vibrate([20, 50, 20]);
        }
    },

    // Double pulse for major wins (Gamification)
    celebrate() {
        if ('vibrate' in navigator) {
            navigator.vibrate([10, 30, 10, 30, 100]);
        }
    },

    // Warning/Error vibrate
    error() {
        if ('vibrate' in navigator) {
            navigator.vibrate([100, 50, 100]);
        }
    }
};

// Global Exposure
window.CRMHaptics = CRMHaptics;

// Auto-bind to some elements
document.addEventListener('click', (e) => {
    if (e.target.closest('button') || e.target.closest('a')) {
        CRMHaptics.tap();
    }
});
