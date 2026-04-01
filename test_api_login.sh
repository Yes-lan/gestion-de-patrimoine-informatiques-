#!/bin/bash

echo "═══════════════════════════════════════════════════════════"
echo "          🔍 API LOGIN TEST"
echo "═══════════════════════════════════════════════════════════"
echo ""

API_URL="http://localhost/api/mobile/login"
EMAIL="dr.dupont@hospital.fr"
PASSWORD="medecin123"

echo "Testing login with:"
echo "  Email: $EMAIL"
echo "  Password: $PASSWORD"
echo "  URL: $API_URL"
echo ""

RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

echo "Response:"
echo "$RESPONSE" | jq . 2>/dev/null || echo "$RESPONSE"
echo ""

if echo "$RESPONSE" | grep -q "token"; then
  echo "✅ Login successful!"
else
  echo "❌ Login failed!"
fi
