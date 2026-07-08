#!/bin/bash

# =====================================================
# API Testing Script untuk Topup E-Wallet & Bank
# =====================================================

# Configuration
BASE_URL="http://localhost:8080"
ADMIN_USERNAME="admin"
ADMIN_PASSWORD="123456"

echo "=========================================="
echo "Testing Topup E-Wallet & Bank API"
echo "=========================================="
echo ""

# Step 1: Login dan dapatkan JWT token
echo "[1/6] Logging in..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"username\": \"$ADMIN_USERNAME\",
    \"password\": \"$ADMIN_PASSWORD\"
  }")

JWT_TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$JWT_TOKEN" ]; then
  echo "❌ Login failed!"
  echo "Response: $LOGIN_RESPONSE"
  exit 1
fi

echo "✅ Login successful!"
echo "JWT Token: ${JWT_TOKEN:0:50}..."
echo ""

# Step 2: Get supported ewallets
echo "[2/6] Getting supported e-wallets..."
EWALLETS=$(curl -s -X GET "$BASE_URL/api/topup-ewallet/supported-methods" \
  -H "Authorization: Bearer $JWT_TOKEN")

echo "✅ Supported E-Wallets:"
echo "$EWALLETS" | jq '.data[] | "\(.code) - \(.name)"' 2>/dev/null || echo "$EWALLETS"
echo ""

# Step 3: Initiate E-Wallet Topup
echo "[3/6] Initiating E-Wallet topup..."
EWALLET_INIT=$(curl -s -X POST "$BASE_URL/api/topup-ewallet/" \
  -H "Authorization: Bearer $JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "metode_ewallet": "dana",
    "nomor_telepon": "08123456789",
    "nominal": 50000
  }')

EWALLET_REF_ID=$(echo $EWALLET_INIT | grep -o '"ref_id":"[^"]*' | cut -d'"' -f4)

if [ -z "$EWALLET_REF_ID" ]; then
  echo "❌ E-Wallet topup initiation failed!"
  echo "Response: $EWALLET_INIT"
else
  echo "✅ E-Wallet topup initiated successfully!"
  echo "Reference ID: $EWALLET_REF_ID"
  echo ""

  # Step 4: Confirm E-Wallet Payment
  echo "[4/6] Confirming E-Wallet payment..."
  EWALLET_CONFIRM=$(curl -s -X POST "$BASE_URL/api/topup-ewallet/confirm/$EWALLET_REF_ID" \
    -H "Authorization: Bearer $JWT_TOKEN")

  EWALLET_STATUS=$(echo $EWALLET_CONFIRM | grep -o '"status":"[^"]*' | cut -d'"' -f4)

  if [ "$EWALLET_STATUS" = "berhasil" ]; then
    echo "✅ E-Wallet payment confirmed!"
    echo "Response: $EWALLET_CONFIRM" | jq '.' 2>/dev/null || echo "$EWALLET_CONFIRM"
  else
    echo "❌ E-Wallet confirmation failed!"
    echo "Response: $EWALLET_CONFIRM"
  fi
fi

echo ""

# Step 5: Get supported banks
echo "[5/6] Getting supported banks..."
BANKS=$(curl -s -X GET "$BASE_URL/api/topup-bank/supported-banks" \
  -H "Authorization: Bearer $JWT_TOKEN")

echo "✅ Supported Banks:"
echo "$BANKS" | jq '.data[] | "\(.code) - \(.name)"' 2>/dev/null || echo "$BANKS"
echo ""

# Step 6: Initiate Bank Topup
echo "[6/6] Initiating Bank topup..."
BANK_INIT=$(curl -s -X POST "$BASE_URL/api/topup-bank/" \
  -H "Authorization: Bearer $JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_bank": "BCA",
    "nomor_rekening": "1234567890",
    "atas_nama": "Admin Test",
    "nominal": 100000
  }')

BANK_REF_ID=$(echo $BANK_INIT | grep -o '"ref_id":"[^"]*' | cut -d'"' -f4)

if [ -z "$BANK_REF_ID" ]; then
  echo "❌ Bank topup initiation failed!"
  echo "Response: $BANK_INIT"
else
  echo "✅ Bank topup initiated successfully!"
  echo "Reference ID: $BANK_REF_ID"
  echo ""
  echo "Confirming Bank payment (this would normally wait for actual payment confirmation)..."
  BANK_CONFIRM=$(curl -s -X POST "$BASE_URL/api/topup-bank/confirm/$BANK_REF_ID" \
    -H "Authorization: Bearer $JWT_TOKEN")

  BANK_STATUS=$(echo $BANK_CONFIRM | grep -o '"status":"[^"]*' | cut -d'"' -f4)

  if [ "$BANK_STATUS" = "berhasil" ]; then
    echo "✅ Bank payment confirmed!"
    echo "Response: $BANK_CONFIRM" | jq '.' 2>/dev/null || echo "$BANK_CONFIRM"
  else
    echo "❌ Bank confirmation failed!"
    echo "Response: $BANK_CONFIRM"
  fi
fi

echo ""
echo "=========================================="
echo "Testing complete!"
echo "=========================================="
