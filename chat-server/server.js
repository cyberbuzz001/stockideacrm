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

io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    socket.on('agent:login', (data) => {
        activeAgents.set(socket.id, {
            userId: data.userId,
            name: data.name,
            lastSeen: Date.now()
        });
        io.emit('agent:update', Array.from(activeAgents.values()));
    });

    socket.on('heartbeat', (data) => {
        if (activeAgents.has(socket.id)) {
            activeAgents.get(socket.id).lastSeen = Date.now();
        }
    });

    socket.on('chat:message', (data) => {
        io.emit('chat:message', data);
    });

    socket.on('disconnect', () => {
        activeAgents.delete(socket.id);
        io.emit('agent:update', Array.from(activeAgents.values()));
        console.log('User disconnected:', socket.id);
    });
});

server.listen(PORT, () => {
    console.log(`Shreesvarn Chat Server running on port ${PORT}`);
});
