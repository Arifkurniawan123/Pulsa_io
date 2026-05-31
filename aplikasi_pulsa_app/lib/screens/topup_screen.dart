import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/nominal_pulsa.dart';

class TopupScreen extends StatefulWidget {
  final NominalPulsa nominal;
  const TopupScreen({super.key, required this.nominal});

  @override
  State<TopupScreen> createState() => _TopupScreenState();
}

class _TopupScreenState extends State<TopupScreen> {
  final TextEditingController _phoneController = TextEditingController();
  final ApiService _api = ApiService();
  bool _isLoading = false;
  String _error = '';
  int _saldoDigiflazz = 0;
  bool _saldoCukup = true;

  @override
  void initState() {
    super.initState();
    _loadSaldo();
  }

  Future<void> _loadSaldo() async {
    try {
      final response = await _api.getSaldoDigiflazz();
      if (response.statusCode == 200 && response.data['success'] == true) {
        setState(() {
          _saldoDigiflazz = response.data['data']['saldo'] ?? 0;
          _checkSaldo();
        });
      }
    } catch (e) {}
  }

  void _checkSaldo() {
    setState(() {
      _saldoCukup = _saldoDigiflazz >= widget.nominal.hargaJual.toInt();
    });
  }

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  bool _isValidPhoneForProvider(String phone, int providerId) {
    final Map<int, List<String>> providerPrefixes = {
      56: ['0812','0813','0814','0815','0816','0817','0818','0819','0851','0852','0853'],
      59: ['0817','0818','0819','0859','0877','0878'],
      55: ['0814','0815','0816','0855','0856','0857','0858'],
      58: ['0895','0896','0897','0898','0899'],
      53: ['0831','0832','0833','0838'],
      57: ['0881','0882','0883','0884','0885','0886','0887','0888','0889'],
      54: ['0851','0852','0853','0854','0855','0856','0857','0858','0859','0812','0813','0814','0815','0816','0817','0818','0819'],
    };
    final prefixes = providerPrefixes[providerId] ?? [];
    if (prefixes.isEmpty) return true;
    return prefixes.any((prefix) => phone.startsWith(prefix));
  }

  Future<void> _doTopup() async {
    final phone = _phoneController.text.trim();
    if (phone.isEmpty) {
      setState(() => _error = 'Masukkan nomor tujuan');
      return;
    }
    if (phone.length < 10 || phone.length > 15) {
      setState(() => _error = 'Nomor tujuan tidak valid (10-15 digit)');
      return;
    }
    if (!_isValidPhoneForProvider(phone, widget.nominal.providerId)) {
      setState(() => _error = 'Nomor tidak valid untuk provider ini');
      return;
    }

    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final response = await _api.topupPulsa({
        'nominal_id': widget.nominal.id,
        'customer_no': phone,
        'harga_jual': widget.nominal.hargaJual,
        'metode_pembayaran': 'saldo',
        'testing': true,
      }).timeout(const Duration(seconds: 30));

      await _loadSaldo();

      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        final status = data?['status'] ?? 'sukses';
        final isSuccess = status == 'sukses';

        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (_) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  Icon(isSuccess ? Icons.check_circle : Icons.info,
                      color: isSuccess ? Colors.green : Colors.orange),
                  const SizedBox(width: 8),
                  Text(isSuccess ? 'Transaksi Berhasil' : 'Transaksi Diproses'),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _infoRow('Nominal', widget.nominal.nominalLabel),
                  _infoRow('Harga', widget.nominal.hargaJualLabel),
                  _infoRow('Nomor', phone),
                  _infoRow('Pembayaran', 'SALDO'),
                  _infoRow('Status', status.toUpperCase()),
                  if (data?['message'] != null && data['message'].isNotEmpty)
                    _infoRow('Pesan', data['message']),
                  const SizedBox(height: 12),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Sisa Saldo', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                      Text(
                        'Rp ${_saldoDigiflazz.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.green),
                      ),
                    ],
                  ),
                ],
              ),
              actions: [
                ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
                  onPressed: () {
                    Navigator.pop(context);
                    Navigator.pop(context);
                  },
                  child: const Text('Selesai', style: TextStyle(color: Colors.white)),
                ),
              ],
            ),
          );
        }
      } else {
        setState(() => _error = response.data['message'] ?? 'Transaksi gagal');
      }
    } on DioException catch (e) {
      String errMsg = e.response?.data['message'] ?? 'Koneksi gagal';
      if (e.type == DioExceptionType.connectionTimeout) {
        errMsg = 'Koneksi timeout, coba lagi.';
      }
      setState(() => _error = errMsg);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 90, child: Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 13))),
          const Text(': '),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13))),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Konfirmasi Topup')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(colors: [Colors.indigo.shade400, Colors.indigo.shade700]),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                children: [
                  const Icon(Icons.phone_android, color: Colors.white, size: 40),
                  const SizedBox(height: 8),
                  Text(widget.nominal.nominalLabel,
                      style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text(widget.nominal.hargaJualLabel,
                      style: const TextStyle(color: Colors.white70, fontSize: 16)),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const Text('Nomor HP Tujuan', style: TextStyle(fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                hintText: 'Contoh: 08123456789',
                prefixIcon: Icon(Icons.phone),
                border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(10))),
              ),
            ),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _saldoCukup ? Colors.green.shade50 : Colors.red.shade50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: _saldoCukup ? Colors.green.shade300 : Colors.red.shade300),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(_saldoCukup ? Icons.check_circle : Icons.warning,
                          color: _saldoCukup ? Colors.green : Colors.red, size: 18),
                      const SizedBox(width: 8),
                      Text('Saldo Digiflazz',
                          style: TextStyle(fontWeight: FontWeight.w600,
                              color: _saldoCukup ? Colors.green.shade700 : Colors.red.shade700)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Saldo Tersedia', style: TextStyle(fontSize: 12, color: Colors.grey)),
                          Text(
                            'Rp ${_saldoDigiflazz.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14,
                                color: _saldoCukup ? Colors.green.shade700 : Colors.red.shade700),
                          ),
                        ],
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Biaya Topup', style: TextStyle(fontSize: 12, color: Colors.grey)),
                          Text(
                            'Rp ${widget.nominal.hargaJual.toInt().toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                        ],
                      ),
                    ],
                  ),
                  if (!_saldoCukup) ...[
                    const SizedBox(height: 8),
                    Text('Saldo tidak cukup. Silahkan top-up saldo terlebih dahulu.',
                        style: TextStyle(fontSize: 12, color: Colors.red.shade700, fontWeight: FontWeight.w500)),
                  ],
                ],
              ),
            ),
            if (_error.isNotEmpty) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.red.shade200)),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: Colors.red, size: 20),
                    const SizedBox(width: 8),
                    Expanded(child: Text(_error, style: const TextStyle(color: Colors.red, fontSize: 13))),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading || !_saldoCukup ? null : _doTopup,
                style: ElevatedButton.styleFrom(
                  backgroundColor: (!_saldoCukup) ? Colors.grey : Colors.green,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _isLoading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : Text(
                        !_saldoCukup ? 'SALDO TIDAK CUKUP' : 'BAYAR SEKARANG',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}