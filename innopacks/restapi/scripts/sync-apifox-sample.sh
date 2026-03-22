#!/bin/bash

# InnoShop API Docs - Sync to Apifox (Sample)
# Copy this file to sync-apifox.sh and fill in your credentials.
# This script generates OpenAPI specs for both Front and Panel APIs, then uploads to Apifox

set -e

# ============================================================
# Configuration - Replace with your own credentials
# ============================================================
APIFOX_TOKEN="your-apifox-token"
APIFOX_FRONT_PROJECT_ID="your-front-project-id"
APIFOX_PANEL_PROJECT_ID="your-panel-project-id"
FRONT_OPENAPI_FILE="storage/app/scribe/openapi.yaml"
PANEL_OPENAPI_FILE="storage/app/scribe_panel/openapi.yaml"

# ============================================================
# Set project root directory (navigate from scripts/ to project root)
# ============================================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

cd "$PROJECT_ROOT"

echo "Project root: $PROJECT_ROOT"

# ============================================================
# Generate docs
# ============================================================

# Generate Front API docs (uses default 'scribe' config)
echo "Generating Front API docs..."
php artisan scribe:generate

if [ ! -f "$FRONT_OPENAPI_FILE" ]; then
    echo "Error: $FRONT_OPENAPI_FILE not found"
    exit 1
fi
echo "Front API OpenAPI spec generated: $FRONT_OPENAPI_FILE"

# Generate Panel API docs (uses 'scribe_panel' config)
echo "Generating Panel API docs..."
php artisan scribe:generate --config scribe_panel

if [ ! -f "$PANEL_OPENAPI_FILE" ]; then
    echo "Error: $PANEL_OPENAPI_FILE not found"
    exit 1
fi
echo "Panel API OpenAPI spec generated: $PANEL_OPENAPI_FILE"

# ============================================================
# Upload to Apifox
# ============================================================

# Check if Apifox CLI is installed
if ! command -v apifox-cli &> /dev/null; then
    echo "Error: apifox-cli is not installed"
    echo "Install it with: npm install -g apifox-cli"
    exit 1
fi

# Upload Front API spec
echo "Syncing Front API to Apifox (Project: $APIFOX_FRONT_PROJECT_ID)..."
apifox-cli upload \
    --file="$FRONT_OPENAPI_FILE" \
    --project-id="$APIFOX_FRONT_PROJECT_ID" \
    --token="$APIFOX_TOKEN"
echo "Front API docs synced to Apifox successfully!"

# Upload Panel API spec
echo "Syncing Panel API to Apifox (Project: $APIFOX_PANEL_PROJECT_ID)..."
apifox-cli upload \
    --file="$PANEL_OPENAPI_FILE" \
    --project-id="$APIFOX_PANEL_PROJECT_ID" \
    --token="$APIFOX_TOKEN"
echo "Panel API docs synced to Apifox successfully!"

echo "Done!"
