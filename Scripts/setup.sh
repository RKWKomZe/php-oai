#!/bin/bash

# setup.sh – Create symbolic link to expose public assets

# Variables
TARGET_DIR="Resources/Public"
LINK_NAME="web/Public"

# Check if symlink already exists
if [ -L "$LINK_NAME" ]; then
    echo "✅ Symlink '$LINK_NAME' already exists."
elif [ -e "$LINK_NAME" ]; then
    echo "⚠️  '$LINK_NAME' exists and is not a symlink. Please remove it manually."
else
    ln -s ../$TARGET_DIR $LINK_NAME
    echo "🔗 Symlink created: $LINK_NAME → ../$TARGET_DIR"
fi
