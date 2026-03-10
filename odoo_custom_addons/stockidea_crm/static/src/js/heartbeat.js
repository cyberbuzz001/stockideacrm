/** @odoo-module **/

import { registry } from "@web/core/registry";
import { rpc } from "@web/core/network/rpc";
import { browser } from "@web/core/browser/browser";

const heartbeatService = {
    start(env) {
        let lastActivity = Date.now();
        let isActive = true;

        // Track user activity
        ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
            browser.addEventListener(evt, () => {
                lastActivity = Date.now();
                isActive = true;
            });
        });

        // Loop every 60 seconds
        setInterval(async () => {
            const now = Date.now();
            // If no activity for 60s, mark as idle
            if (now - lastActivity > 60000) {
                isActive = false;
            }

            // Send Heartbeat to Server
            // We ignore movement during lunch (1:00 PM - 1:30 PM) logic is handled on server or can be here.
            // Sending status: 'active' or 'idle'
            try {
                await rpc("/stockidea/heartbeat", {
                    status: isActive ? 'active' : 'idle',
                    timestamp: now
                });
            } catch (e) {
                console.error("Heartbeat failed", e);
            }
        }, 60000); // 60 seconds
    }
};

registry.category("services").add("stockidea_heartbeat", heartbeatService);
