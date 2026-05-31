import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../widgets/custom_sidebar.dart';

class TopupSaldoScreen extends StatefulWidget {
  const TopupSaldoScreen({super.key});

  @override
  State<TopupSaldoScreen> createState() => _TopupSaldoScreenState();
}

class _TopupSaldoScreenState extends State<TopupSaldoScreen> {
  final ApiService _api = ApiService();
  int _saldoDigiflazz = 0;
  bool _isLoading = true;
  String _error = '';

  int? _selectedNominal;
  String _topupMode = 'ewallet';

  String _selectedEwallet = 'dana';
  String _nomorTelepon = '';

  String _selectedBank = 'BCA';
  String _nomorRekening = '';
  String _atasNama = '';

  bool _isProcessing = false;

  final List<Map<String, dynamic>> _nominalOptions = [
    {'value': 50000, 'label': 'Rp 50.000'},
    {'value': 100000, 'label': 'Rp 100.000'},
    {'value': 250000, 'label': 'Rp 250.000'},
    {'value': 500000, 'label': 'Rp 500.000'},
    {'value': 1000000, 'label': 'Rp 1.000.000'},
  ];

  final List<Map<String, dynamic>> _ewalletMethods = [
    {'value': 'dana', 'label': 'DANA', 'icon': Icons.wallet_giftcard, 'color': Colors.blue},
    {'value': 'ovo', 'label': 'OVO', 'icon': Icons.account_balance_wallet, 'color': Colors.purple},
    {'value': 'gopay', 'label': 'GoPay', 'icon': Icons.account_balance_wallet, 'color': Colors.green},
    {'value': 'linkaja', 'label': 'LinkAja', 'icon': Icons.add_card, 'color': Colors.orange},
  ];

  final List<String> _bankOptions = [
    'BCA', 'BNI', 'Mandiri', 'BRI', 'CIMB', 'Permata', 'Danamon', 'Maybank',
  ];

  @override
  void initState() {
    super.initState();
    _loadSaldo();
  }

  Future<void> _loadSaldo() async {
    setState(() => _isLoading = true);
    try {
      final response = await _api.getSaldoDigiflazz();
      if (response.statusCode == 200 && response.data['success'] == true) {
        setState(() {
          _saldoDigiflazz = response.data['data']['saldo'] ?? 0;
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = response.data['message'] ?? 'Gagal load saldo';
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      setState(() {
        _error = e.response?.data['message'] ?? 'Koneksi gagal';
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Terjadi kesalahan: $e';
        _isLoading = false;
      });
    }
  }

  bool _validateEwallet() {
    if (_selectedNominal == null) {
      _showError('Pilih nominal terlebih dahulu');
      return false;
    }
    if (_nomorTelepon.isEmpty) {
      _showError('Masukkan nomor telepon');
      return false;
    }
    if (_nomorTelepon.length < 10 || _nomorTelepon.length > 15) {
      _showError('Nomor telepon harus 10-15 digit');
      return false;
    }
    return true;
  }

  bool _validateBank() {
    if (_selectedNominal == null) {
      _showError('Pilih nominal terlebih dahulu');
      return false;
    }
    if (_nomorRekening.isEmpty) {
      _showError('Masukkan nomor rekening');
      return false;
    }
    if (_nomorRekening.length < 10) {
      _showError('Nomor rekening minimal 10 digit');
      return false;
    }
    if (_atasNama.isEmpty) {
      _showError('Masukkan nama pemilik rekening');
      return false;
    }
    return true;
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: Colors.red));
  }

  Future<void> _doTopupEwallet() async {
    if (!_validateEwallet()) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.topupEwalletInitiate(_selectedEwallet, _nomorTelepon, _selectedNominal!);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => AlertDialog(
              title: const Text('Konfirmasi Top-up E-Wallet'),
              content: Column(mainAxisSize: MainAxisSize.min, children: [
                _infoRow('Metode', _selectedEwallet.toUpperCase()),
                _infoRow('Nomor', _nomorTelepon),
                _infoRow('Nominal', 'Rp ${_formatCurrency(_selectedNominal!)}'),
                _infoRow('Ref ID', data['ref_id'] ?? ''),
                _infoRow('Status', 'PENDING'),
                const SizedBox(height: 12),
                const Text('Klik Konfirmasi untuk simulasi pembayaran berhasil'),
              ]),
              actions: [
                TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _confirmEwallet(data['ref_id'] ?? '');
                  },
                  child: const Text('Konfirmasi'),
                ),
              ],
            ),
          );
        }
      } else {
        _showError(response.data['message'] ?? 'Gagal');
      }
    } on DioException catch (e) {
      _showError(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _confirmEwallet(String refId) async {
    if (refId.isEmpty) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.topupEwalletConfirm(refId);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('Top-up Berhasil'),
              content: Column(mainAxisSize: MainAxisSize.min, children: [
                _infoRow('Ref ID', refId),
                _infoRow('Status', data['status']?.toString().toUpperCase() ?? 'BERHASIL'),
                if (data['saldo_baru'] != null) _infoRow('Saldo Baru', 'Rp ${_formatCurrency(data['saldo_baru'])}'),
              ]),
              actions: [
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _loadSaldo();
                    _resetForm();
                  },
                  child: const Text('Tutup'),
                ),
              ],
            ),
          );
        }
      } else {
        _showError(response.data['message'] ?? 'Gagal konfirmasi');
      }
    } on DioException catch (e) {
      _showError(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _doTopupBank() async {
    if (!_validateBank()) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.topupBankInitiate(_selectedBank, _nomorRekening, _atasNama, _selectedNominal!);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => AlertDialog(
              title: const Text('Konfirmasi Top-up Bank'),
              content: Column(mainAxisSize: MainAxisSize.min, children: [
                _infoRow('Bank', _selectedBank),
                _infoRow('Rekening', _nomorRekening),
                _infoRow('Atas Nama', _atasNama),
                _infoRow('Nominal', 'Rp ${_formatCurrency(_selectedNominal!)}'),
                _infoRow('Ref ID', data['ref_id'] ?? ''),
                _infoRow('Status', 'PENDING'),
                const SizedBox(height: 12),
                const Text('Klik Konfirmasi untuk simulasi pembayaran berhasil'),
              ]),
              actions: [
                TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _confirmBank(data['ref_id'] ?? '');
                  },
                  child: const Text('Konfirmasi'),
                ),
              ],
            ),
          );
        }
      } else {
        _showError(response.data['message'] ?? 'Gagal');
      }
    } on DioException catch (e) {
      _showError(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _confirmBank(String refId) async {
    if (refId.isEmpty) return;
    setState(() => _isProcessing = true);
    try {
      final response = await _api.topupBankConfirm(refId);
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];
        if (mounted) {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('Top-up Berhasil'),
              content: Column(mainAxisSize: MainAxisSize.min, children: [
                _infoRow('Ref ID', refId),
                _infoRow('Status', data['status']?.toString().toUpperCase() ?? 'BERHASIL'),
                if (data['saldo_baru'] != null) _infoRow('Saldo Baru', 'Rp ${_formatCurrency(data['saldo_baru'])}'),
              ]),
              actions: [
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _loadSaldo();
                    _resetForm();
                  },
                  child: const Text('Tutup'),
                ),
              ],
            ),
          );
        }
      } else {
        _showError(response.data['message'] ?? 'Gagal konfirmasi');
      }
    } on DioException catch (e) {
      _showError(e.response?.data['message'] ?? 'Koneksi gagal');
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  void _resetForm() {
    setState(() {
      _selectedNominal = null;
      _nomorTelepon = '';
      _nomorRekening = '';
      _atasNama = '';
    });
  }

  Widget _infoRow(String label, String value) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 2),
    child: Row(children: [SizedBox(width: 100, child: Text(label)), const Text(': '), Expanded(child: Text(value))]),
  );

  String _formatCurrency(int value) => value.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.');

  void _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    switch (index) {
      case 0:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/dashboard');
        else Navigator.pushReplacementNamed(context, '/kasir');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/pulsa-provider');
        break;
      case 2:
        // sudah di topup saldo
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/scan-pulsa');
        break;
      case 4:
        if (roleId == 2) {
          Navigator.pushReplacementNamed(context, '/user');
        } else {
          Navigator.pushReplacementNamed(context, '/history');
        }
        break;
      case 5:
        if (roleId == 2) {
          Navigator.pushReplacementNamed(context, '/history');
        }
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Fitur belum tersedia')),
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: CustomSidebar(selectedIndex: 2, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(title: const Text('Top-up Saldo')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error.isNotEmpty
              ? Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                  Text(_error),
                  const SizedBox(height: 16),
                  ElevatedButton(onPressed: _loadSaldo, child: const Text('Coba Lagi')),
                ]))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(children: [
                            const Text('Saldo Saat Ini'),
                            const SizedBox(height: 8),
                            Text('Rp ${_formatCurrency(_saldoDigiflazz)}', style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                          ]),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(children: [
                        _buildModeChip('E-Wallet', 'ewallet'),
                        _buildModeChip('Bank', 'bank'),
                      ]),
                      const SizedBox(height: 24),
                      if (_topupMode == 'ewallet') ...[
                        const Text('Pilih E-Wallet'),
                        const SizedBox(height: 8),
                        Wrap(spacing: 8, children: _ewalletMethods.map((m) => _buildEwalletChip(m)).toList()),
                        const SizedBox(height: 16),
                        TextField(
                          onChanged: (v) => _nomorTelepon = v,
                          keyboardType: TextInputType.phone,
                          decoration: const InputDecoration(labelText: 'Nomor Telepon', prefixIcon: Icon(Icons.phone)),
                        ),
                      ] else ...[
                        DropdownButtonFormField<String>(
                          value: _selectedBank,
                          items: _bankOptions.map((b) => DropdownMenuItem(value: b, child: Text(b))).toList(),
                          onChanged: (v) => setState(() => _selectedBank = v!),
                          decoration: const InputDecoration(labelText: 'Pilih Bank'),
                        ),
                        const SizedBox(height: 16),
                        TextField(
                          onChanged: (v) => _nomorRekening = v,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(labelText: 'Nomor Rekening'),
                        ),
                        const SizedBox(height: 16),
                        TextField(
                          onChanged: (v) => _atasNama = v,
                          decoration: const InputDecoration(labelText: 'Atas Nama'),
                        ),
                      ],
                      const SizedBox(height: 24),
                      const Text('Pilih Nominal'),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        children: _nominalOptions.map((opt) => ChoiceChip(
                          label: Text(opt['label']),
                          selected: _selectedNominal == opt['value'],
                          onSelected: (s) => setState(() => _selectedNominal = s ? opt['value'] : null),
                        )).toList(),
                      ),
                      const SizedBox(height: 32),
                      ElevatedButton(
                        onPressed: _isProcessing ? null : (_topupMode == 'ewallet' ? _doTopupEwallet : _doTopupBank),
                        child: Text(_topupMode == 'ewallet' ? 'Top-up E-Wallet' : 'Top-up Bank'),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildModeChip(String label, String mode) => Expanded(
    child: OutlinedButton(
      onPressed: () => setState(() => _topupMode = mode),
      style: OutlinedButton.styleFrom(
        backgroundColor: _topupMode == mode ? Colors.indigo.shade50 : null,
        side: BorderSide(color: _topupMode == mode ? Colors.indigo : Colors.grey.shade300),
      ),
      child: Text(label),
    ),
  );

  Widget _buildEwalletChip(Map<String, dynamic> method) => GestureDetector(
    onTap: () => setState(() => _selectedEwallet = method['value']),
    child: Chip(
      label: Text(method['label']),
      avatar: Icon(method['icon'], color: method['color']),
      backgroundColor: _selectedEwallet == method['value'] ? method['color'].withOpacity(0.1) : null,
    ),
  );
}