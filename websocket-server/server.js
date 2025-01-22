const WebSocket = require('ws');

const wss = new WebSocket.Server({ host: '0.0.0.0', port: 8085 });

let groups = {};
let messages = {};

function broadcastToGroup(groupId, message) {
    if (!groups[groupId]) return;
    groups[groupId].forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(message));
        }
    });
}

wss.on('connection', (ws, req) => {
    const urlParams = new URLSearchParams(req.url.split('?')[1]);
    const token = urlParams.get('token');

    if (token !== 'your-secure-token') {
        ws.close();
        console.log(`[${new Date().toISOString()}] Connection refused: Invalid token`);
        return;
    }

    console.log(`[${new Date().toISOString()}] New client connected`);

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            if (data.type === 'join') {
                const groupId = data.groupId;

                groups[groupId] = groups[groupId] || new Set();
                groups[groupId].add(ws);

                console.log(`[${new Date().toISOString()}] Client joined group ${groupId}`);

                const groupMessages = messages[groupId] || [];
                ws.send(JSON.stringify({
                    type: 'history',
                    messages: groupMessages.slice(-5),
                }));
            } else if (data.type === 'message') {
                const groupId = data.groupId;

                const chatMessage = {
                    sender: data.sender,
                    avatar: data.avatar,
                    message: data.message,
                    timestamp: new Date(),
                };

                messages[groupId] = messages[groupId] || [];
                messages[groupId].push(chatMessage);

                console.log(`[${new Date().toISOString()}] Message from ${chatMessage.sender} in group ${groupId}: "${chatMessage.message}"`);

                broadcastToGroup(groupId, { type: 'message', ...chatMessage });
            }
        } catch (err) {
            console.error(`[${new Date().toISOString()}] Error processing message:`, err);
        }
    });

    ws.on('close', () => {
        console.log(`[${new Date().toISOString()}] Client disconnected`);
        Object.keys(groups).forEach((groupId) => {
            groups[groupId].delete(ws);
            if (groups[groupId].size === 0) {
                delete groups[groupId];
            }
        });
    });
});

console.log(`[${new Date().toISOString()}] WebSocket server running on ws://0.0.0.0:8085`);
