#!/bin/bash

# Install Node.js dependencies
echo "Installing Node.js dependencies..."
npm install

# Install Tailwind CSS plugins
echo "Installing Tailwind CSS plugins..."
npm install -D @tailwindcss/forms @tailwindcss/typography @tailwindcss/aspect-ratio

# Create necessary directories
echo "Creating necessary directories..."
mkdir -p public/css
mkdir -p src/css

# Build CSS
echo "Building CSS..."
npm run build
echo ""
echo "Setup completed successfully!" 

LUNO_KEY=your_api_key
LUNO_SECRET=your_api_secret 