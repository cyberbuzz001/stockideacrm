const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const PORT = process.env.PORT || 3000;

// Smart Heartbeat & Agent Status
const activeAgents = new Map();

// Liveness probe for PM2 / uptime monitoring.
app.get('/health', (req, res) => {
    res.json({ status: 'ok', connections: io.engine.clientsCount, activeAgents: activeAgents.size });
});

// Wrap a socket handler so a malformed payload or unexpected error logs and
// disconnects the offending client instead of crashing the whole process
// (an uncaught throw inside a socket.io handler takes down every connection).
function safeHandler(socket, label, fn) {
    return (data) => {
        try {
            fn(data);
        } catch (err) {
            console.error(`[${label}] handler error for socket ${socket.id}:`, err.message);
        }
    };
}

io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    socket.on('agent:login', safeHandler(socket, 'agent:login', (data) => {
        if (!data || typeof data.userId === 'undefined' || typeof data.name !== 'string') {
            console.warn(`[agent:login] rejected malformed payload from ${socket.id}`);
            return;
        }
        activeAgents.set(socket.id, {
            userId: data.userId,
            name: data.name,
            lastSeen: Date.now()
        });
        io.emit('agent:update', Array.from(activeAgents.values()));
    }));

    socket.on('heartbeat', safeHandler(socket, 'heartbeat', () => {
        if (activeAgents.has(socket.id)) {
            activeAgents.get(socket.id).lastSeen = Date.now();
        }
    }));

    socket.on('chat:message', safeHandler(socket, 'chat:message', (data) => {
        if (!data || typeof data !== 'object') {
            console.warn(`[chat:message] rejected malformed payload from ${socket.id}`);
            return;
        }
        io.emit('chat:message', data);
    }));

    socket.on('disconnect', () => {
        activeAgents.delete(socket.id);
        io.emit('agent:update', Array.from(activeAgents.values()));
        console.log('User disconnected:', socket.id);
    });
});

server.listen(PORT, () => {
    console.log(`Shreesvarn Chat Server running on port ${PORT}`);
});
