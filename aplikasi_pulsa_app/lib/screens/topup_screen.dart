import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/digiflazz_product.dart';

class TopupScreen extends StatefulWidget {
  final DigiflazzProduct product;
  final double hargaJual;
  const TopupScreen({super.key, required this.product, required this.hargaJual});

  @override
  State<TopupScreen> createState() => _TopupScreenState();
}

class _TopupScreenState extends State<TopupScreen> {
  final TextEditingController _phoneController = TextEditingController();
  final ApiService _api = ApiService();
  bool _isLoading = false;
  String _error = '';
  String _selectedPayment = 'tunai';
  final List<String> _paymentMethods = ['tunai', 'saldo', 'transfer'];

  Future<void> _doTopup() async {
    final phone = _phoneController.text.trim();
    if (phone.isEmpty) {
      setState(() => _error = 'Masukkan nomor tujuan');
      return;
    }
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final response = await _api.topupPulsa({
        'buyer_sku_code': widget.product.buyerSkuCode,
        'customer_no': phone,
        'testing': true,
        'harga_jual': widget.hargaJual,
        'metode_pembayaran': _selectedPayment,
      });
      if (response.statusCode == 200) {
        final data = response.data['data'];
        final status = data['status'] ?? 'pending';
        final message = data['message'] ?? 'Transaksi diproses';
        if (mounted) {
          showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: Text(status == 'Sukses' ? '✅ Berhasil' : '⚠️ $status'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Produk: ${widget.product.productName}'),
                  const SizedBox(height: 8),
                  Text('Nomor: $phone'),
                  const SizedBox(height: 8),
                  Text('Harga: ${_formatRupiah(widget.hargaJual)}'),
                  const SizedBox(height: 8),
                  Text('Status: $status'),
                  if (message.isNotEmpty) Text('Pesan: $message'),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                    Navigator.pop(context);
                  },
                  child: const Text('Tutup'),
                ),
              ],
            ),
          );
        }
      } else {
        setState(() => _error = response.data['message'] ?? 'Gagal');
      }
    } on DioException catch (e) {
      setState(() => _error = e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  String _formatRupiah(double price) {
    return 'Rp ${price.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Konfirmasi Topup')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Text(widget.product.productName, style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(_formatRupiah(widget.hargaJual), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.green)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'Nomor HP Tujuan',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _selectedPayment,
              items: _paymentMethods.map((method) {
                return DropdownMenuItem(value: method, child: Text(method.toUpperCase()));
              }).toList(),
              onChanged: (value) => setState(() => _selectedPayment = value!),
              decoration: const InputDecoration(labelText: 'Metode Pembayaran', border: OutlineInputBorder()),
            ),
            if (_error.isNotEmpty) ...[
              const SizedBox(height: 16),
              Text(_error, style: const TextStyle(color: Colors.red)),
            ],
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _doTopup,
                style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                child: _isLoading ? const CircularProgressIndicator() : const Text('BAYAR'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}