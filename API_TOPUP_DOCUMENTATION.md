# API Documentation - Topup E-Wallet & Bank Transfer

## Ringkasan
Sistem telah ditambahkan dengan fitur topup untuk E-Wallet (GCash, DANA, OVO, LinkAja, GoPay) dan Bank Transfer (BCA, BNI, Mandiri, BRI, CIMB, dll).

---

## 🎯 MASALAH YANG SUDAH DIPERBAIKI

### 1. Status Transaksi Pulsa Masih "Pending"
✅ **FIXED**: Status transaksi sekarang konsisten menggunakan lowercase ('sukses', 'proses', 'gagal') sesuai dengan database enum.
- File yang diubah: `app/Controllers/Digiflazz.php`
- Perubahan: 'Sukses' → 'sukses' + normalisasi status dari API response

---

## 📱 E-WALLET ENDPOINTS

### 1. Initiate E-Wallet Topup
**Endpoint:** `POST /api/topup-ewallet/`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json
```

**Request Body:**
```json
{
  "metode_ewallet": "gcash|dana|ovo|linkaja|gopay",
  "nomor_telepon": "09123456789",
  "nominal": 50000
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Transaksi topup e-wallet berhasil dibuat",
  "data": {
    "id": 1,
    "ref_id": "EWLT-1779950000-123",
    "metode": "gcash",
    "nomor_telepon": "09123456789",
    "nominal": 50000,
    "status": "pending",
    "created_at": "2026-05-28 16:30:00"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "metode_ewallet": "metode_ewallet hanya boleh berisi: gcash, dana, ovo, linkaja, gopay"
  }
}
```

---

### 2. Confirm E-Wallet Payment (Simulasi)
**Endpoint:** `POST /api/topup-ewallet/confirm/{ref_id}`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Example:**
```
POST /api/topup-ewallet/confirm/EWLT-1779950000-123
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Top-up e-wallet berhasil dikonfirmasi",
  "data": {
    "ref_id": "EWLT-1779950000-123",
    "nominal": 50000,
    "status": "berhasil",
    "saldo_baru": 300000
  }
}
```

---

### 3. Get E-Wallet History
**Endpoint:** `GET /api/topup-ewallet/history`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Query Parameters:**
- `limit`: (optional, default: 50) Jumlah records
- `offset`: (optional, default: 0) Offset untuk pagination

**Example:**
```
GET /api/topup-ewallet/history?limit=20&offset=0
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": "e203afdb-a8b3-11f0-93f0-68f728f47497",
      "ref_id": "EWLT-1779950000-123",
      "metode_ewallet": "gcash",
      "nomor_telepon": "09123456789",
      "nominal": 50000,
      "status": "berhasil",
      "keterangan": "Top-up gcash ke 09123456789",
      "created_at": "2026-05-28 16:30:00",
      "updated_at": "2026-05-28 16:35:00"
    }
  ],
  "total": 1
}
```

---

### 4. Get Supported E-Wallets
**Endpoint:** `GET /api/topup-ewallet/supported-methods`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "code": "gcash",
      "name": "GCash",
      "country": "PH"
    },
    {
      "code": "dana",
      "name": "DANA",
      "country": "ID"
    },
    {
      "code": "ovo",
      "name": "OVO",
      "country": "ID"
    },
    {
      "code": "linkaja",
      "name": "LinkAja",
      "country": "ID"
    },
    {
      "code": "gopay",
      "name": "GoPay",
      "country": "ID"
    }
  ]
}
```

---

## 🏦 BANK TRANSFER ENDPOINTS

### 1. Initiate Bank Topup
**Endpoint:** `POST /api/topup-bank/`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nama_bank": "BCA",
  "nomor_rekening": "1234567890",
  "atas_nama": "John Doe",
  "nominal": 100000
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Transaksi topup bank berhasil dibuat (waiting for payment confirmation)",
  "data": {
    "id": 1,
    "ref_id": "BANK-1779950500-456",
    "nama_bank": "BCA",
    "nomor_rekening": "1234567890",
    "atas_nama": "John Doe",
    "nominal": 100000,
    "status": "pending",
    "created_at": "2026-05-28 16:40:00"
  }
}
```

---

### 2. Confirm Bank Payment (Simulasi)
**Endpoint:** `POST /api/topup-bank/confirm/{ref_id}`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Example:**
```
POST /api/topup-bank/confirm/BANK-1779950500-456
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Top-up bank transfer berhasil dikonfirmasi",
  "data": {
    "ref_id": "BANK-1779950500-456",
    "nominal": 100000,
    "status": "berhasil",
    "saldo_baru": 400000
  }
}
```

---

### 3. Get Bank History
**Endpoint:** `GET /api/topup-bank/history`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Query Parameters:**
- `limit`: (optional, default: 50) Jumlah records
- `offset`: (optional, default: 0) Offset untuk pagination

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": "e203afdb-a8b3-11f0-93f0-68f728f47497",
      "ref_id": "BANK-1779950500-456",
      "nama_bank": "BCA",
      "nomor_rekening": "1234567890",
      "atas_nama": "John Doe",
      "nominal": 100000,
      "status": "berhasil",
      "keterangan": "Top-up via BCA ke 1234567890 atas nama John Doe",
      "created_at": "2026-05-28 16:40:00",
      "updated_at": "2026-05-28 16:45:00"
    }
  ],
  "total": 1
}
```

---

### 4. Get Supported Banks
**Endpoint:** `GET /api/topup-bank/supported-banks`

**Request Headers:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "code": "BCA",
      "name": "Bank Central Asia",
      "type": "transfer"
    },
    {
      "code": "BNI",
      "name": "Bank Negara Indonesia",
      "type": "transfer"
    },
    {
      "code": "Mandiri",
      "name": "Bank Mandiri",
      "type": "transfer"
    },
    {
      "code": "BRI",
      "name": "Bank Rakyat Indonesia",
      "type": "transfer"
    },
    {
      "code": "CIMB",
      "name": "CIMB Niaga",
      "type": "transfer"
    },
    {
      "code": "Permata",
      "name": "Bank Permata",
      "type": "transfer"
    },
    {
      "code": "Danamon",
      "name": "Bank Danamon",
      "type": "transfer"
    },
    {
      "code": "Maybank",
      "name": "Maybank Indonesia",
      "type": "transfer"
    }
  ]
}
```

---

## 📊 Database Tables Created

### `tbl_topup_ewallet`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (CHAR 36) - User yang melakukan topup
- ref_id (VARCHAR 100, UNIQUE) - Reference ID unik
- metode_ewallet (ENUM: gcash, dana, ovo, linkaja, gopay)
- nomor_telepon (VARCHAR 20)
- nominal (BIGINT)
- status (ENUM: pending, proses, berhasil, gagal)
- keterangan (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

### `tbl_topup_bank`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (CHAR 36) - User yang melakukan topup
- ref_id (VARCHAR 100, UNIQUE) - Reference ID unik
- nama_bank (VARCHAR 100)
- nomor_rekening (VARCHAR 20)
- atas_nama (VARCHAR 100)
- nominal (BIGINT)
- status (ENUM: pending, proses, berhasil, gagal)
- keterangan (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

---

## 🧪 Testing dengan cURL

### Test E-Wallet Topup
```bash
curl -X POST http://localhost:8080/api/topup-ewallet/ \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "metode_ewallet": "dana",
    "nomor_telepon": "08123456789",
    "nominal": 50000
  }'
```

### Test Confirm E-Wallet
```bash
curl -X POST http://localhost:8080/api/topup-ewallet/confirm/EWLT-1779950000-123 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Test Bank Topup
```bash
curl -X POST http://localhost:8080/api/topup-bank/ \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_bank": "BCA",
    "nomor_rekening": "1234567890",
    "atas_nama": "John Doe",
    "nominal": 100000
  }'
```

### Test Get Supported Banks
```bash
curl -X GET http://localhost:8080/api/topup-bank/supported-banks \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

---

## 🔄 Flow Topup Simulasi

### E-Wallet Topup Flow:
1. **POST /api/topup-ewallet/** → Create transaction dengan status `pending`
2. **POST /api/topup-ewallet/confirm/{ref_id}** → Simulasi payment confirmation, update status ke `berhasil`, tambah saldo user
3. **GET /api/topup-ewallet/history** → Lihat semua transaksi e-wallet

### Bank Topup Flow:
1. **POST /api/topup-bank/** → Create transaction dengan status `pending`
2. **POST /api/topup-bank/confirm/{ref_id}** → Simulasi payment confirmation, update status ke `berhasil`, tambah saldo user
3. **GET /api/topup-bank/history** → Lihat semua transaksi bank

---

## 🛠️ Integration Notes untuk Production

### Untuk E-Wallet (Real Integration):
1. Integrate dengan payment gateway e-wallet (GCash API, DANA API, etc)
2. Implement webhook handler untuk menerima payment confirmation
3. Update status berdasarkan webhook dari payment provider

### Untuk Bank Transfer (Real Integration):
1. Integrate dengan bank partner atau payment aggregator (Xendit, Doku, etc)
2. Generate unique virtual account untuk setiap topup request
3. Setup automated verification untuk transfer yang masuk
4. Implement webhook untuk konfirmasi pembayaran

---

## 📝 Catatan Penting

- ✅ Status transaksi pulsa sudah diperbaiki (konsisten lowercase)
- ✅ Saldo saat topup pulsa dengan metode saldo akan berkurang otomatis
- ✅ History topup disimpan di `tbl_topup_saldo_history`
- ✅ Topup E-Wallet dan Bank disimpan di tabel terpisah
- ✅ Semua endpoint dilindungi dengan JWT authentication
- 🔜 Webhook integration untuk payment gateway (development selanjutnya)
