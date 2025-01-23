#!/bin/bash

# Change directory to the script's location and then to the "websocket" directory
cd "$(dirname "$0")/websocket" || exit

# Print message
echo "Starting WebSocket server..."

# Run the npm script
npm run chat

# Pause equivalent: Wait for the user to press Enter before exiting
read -p "Press Enter to exit..."
