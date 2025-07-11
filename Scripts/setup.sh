#!/bin/bash

# database import
set -e

DB_NAME="db"

echo "🔍 Checking if database '$DB_NAME' is empty..."

# Count number of tables
TABLE_COUNT=$(ddev mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB_NAME}';")

if [ "$TABLE_COUNT" -eq 0 ]; then
  echo "📥 Database is empty — importing default data..."
  ddev import-db --src=packages/oai-pmh/install/setup_database.sql.gz
  echo "✅ Import complete."
else
  echo "✅ Database already has $TABLE_COUNT tables — skipping import."
fi


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
