#!/bin/bash

# Terminate all background processes on exit (Ctrl+C)
trap "kill 0" EXIT

BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$BASE_DIR"

echo "========================================="
echo "   Starting FlexLM Dummy System Agent    "
echo "========================================="

# Check Python3
if ! command -v python3 &> /dev/null; then
    echo "Error: python3 is not installed or not in PATH."
    exit 1
fi

# Set up virtual environment (optional but recommended)
if [ ! -d "venv" ]; then
    echo "Creating virtual environment..."
    python3 -m venv venv
fi

# Activate virtualenv and install requirements
source venv/bin/activate
echo "Installing dependencies from requirements.txt..."
pip install -r requirements.txt

# Start Generator and Agent
echo "Starting Dummy Log Generator in background..."
python -u generator.py &

# Allow generator to initialize and write first log lines
sleep 1

echo "Starting Sync Agent..."
python -u agent.py

# Wait for all background tasks
wait
