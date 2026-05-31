import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../models/topup_bank.dart';

class TopupBankScreen extends StatefulWidget {
  const TopupBankScreen({super.key});

  @override
  State<TopupBankScreen> createState() => _TopupBankScreenState();
}

class _TopupBankScreenState extends State<TopupBankScreen> with SingleTickerProviderStateMixin {
  final ApiService _api = ApiService();
  late TabController _tabController;

  int _saldoDigiflazz = 0;
  List<TopupBank> _history = [];
  bool _isLoading = true;
  String _error = '';

  // Form state
  String _selectedBank = '';
  String _nomorRekening = '';
  String _atasNama = '';
  int? _selectedNominal;
  bool _isProcessing = false;
  List<Map<String, dynamic>> _bankList = [];

  final List<Map<String, dynamic>> _nominalOptions = [
    {'value': 50000, 'label': 'Rp 50.000'},
    {'value': 100000, 'label': 'Rp 100.000'},
    {'value': 250000, 'label': 'Rp 250.000'},
    {'value': 500000, 'label': 'Rp 500.000'},
    {'value': 1000000, 'label': 'Rp 1.000.000'},
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _checkLoginAndLoad();
  }

  Future<void> _checkLoginAndLoad() async {
    final isLoggedIn = await _api.isLoggedIn();
    if (!isLoggedIn) {
      if (mounted) Navigator.pushReplacementNamed(context, '/login');
      return;
    }
    await _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final [saldoResponse, historyResponse, banksResponse] = await Future.wait([
        _api.getSaldoDigiflazz(),
        _api.getBankHistory(limit: 100),
        _api.getSupportedBanks(),
      ]);

      if (!mounted) return;

      if (saldoResponse.statusCode == 200 && saldoResponse.data['success'] == true) {
        _saldoDigiflazz = saldoResponse.data['data']['saldo'] ?? 0;
      }

      if (historyResponse.statusCode == 200 && historyResponse.data['success'] == true) {
        final List raw = historyResponse.data['data'] as List? ?? [];
        _history = raw.map((e) => TopupBank.fromJson(e)).toList();
      }

      if (banksResponse.statusCode == 200 && banksResponse.data['success'] == true) {
        final List raw = banksResponse.data['data'] as List? ?? [];
        _bankList = List<Map<String, dynamic>>.from(raw);
        if (_bankList.isNotEmpty && _selectedBank.isEmpty) {
          _selectedBank = _bankList.first['code'] ?? '';
        }
      }

      setState(() => _isLoading = false);
    } on DioException catch (e) {
      if (mounted) {
        setState(() {
          _error = e.response?.data['message'] ?? 'Koneksi gagal';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Terjadi kesalahan: $e';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _initiateTopup() async {
    if (_selectedNominal == null || _nomorRekening.isEmpty || _atasNama.isEmpty || _selectedBank.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Lengkapi semua data')));
      return;
    }

    if (_nomorRekening.length < 10) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Nomor rekening minimal 10 digit')));
      return;
    }

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
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: const Row(
                children: [
                  Icon(Icons.info, color: Colors.orange),
                  SizedBox(width: 8),
                  Text('Konfirmasi Pembayaran'),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _infoRow('Bank', data['nama_bank']),
                  _infoRow('Rekening', data['nomor_rekening']),
                  _infoRow('Atas Nama', data['atas_nama']),
                  _infoRow('Nominal', 'Rp ${_formatCurrency(data['nominal'])}'),
                  _infoRow('Ref ID', data['ref_id']),
                  _infoRow('Status', data['status'].toString().toUpperCase()),
                  const SizedBox(height: 12),
                  const Text(
                    'Simulasi: Klik "Konfirmasi" untuk mensimulasikan pembayaran berhasil',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Batal'),
                ),
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _confirmPayment(data['ref_id']);
                  },
                  child: const Text('Konfirmasi Pembayaran'),
                ),
              ],
            ),
          );
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(response.data['message'] ?? 'Gagal')));
      }
    } on DioException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.response?.data['message'] ?? 'Koneksi gagal')));
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _confirmPayment(String refId) async {
    setState(() => _isProcessing = true);

    try {
      final response = await _api.topupBankConfirm(refId);

      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'];

        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (context) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: const Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.green),
                  SizedBox(width: 8),
                  Text('Pembayaran Berhasil'),
                ],
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _infoRow('Nominal', 'Rp ${_formatCurrency(data['nominal'])}'),
                  _infoRow('Status', data['status'].toString().toUpperCase()),
                  const SizedBox(height: 12),
                  _infoRow('Saldo Baru', 'Rp ${_formatCurrency(data['saldo_baru'])}', isHighlight: true),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _loadData();
                    _resetForm();
                  },
                  child: const Text('Tutup'),
                ),
              ],
            ),
          );
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(response.data['message'] ?? 'Gagal')));
      }
    } on DioException catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.response?.data['message'] ?? 'Koneksi gagal')));
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  void _resetForm() {
    setState(() {
      _selectedNominal = null;
      _nomorRekening = '';
      _atasNama = '';
      _selectedBank = _bankList.isNotEmpty ? _bankList.first['code'] ?? '' : '';
    });
  }

  Widget _infoRow(String label, String value, {bool isHighlight = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: TextStyle(
                color: Colors.grey.shade600,
                fontSize: 13,
                fontWeight: isHighlight ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ),
          const Text(': '),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 13,
                color: isHighlight ? Colors.green.shade700 : Colors.black,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(int value) {
    return value.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Top-up via Bank Transfer'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Top-up', icon: Icon(Icons.add_circle)),
            Tab(text: 'History', icon: Icon(Icons.history)),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadData,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildTopupTab(),
                    _buildHistoryTab(),
                  ],
                ),
    );
  }

  Widget _buildTopupTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Card(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Saldo Digiflazz', style: Theme.of(context).textTheme.bodySmall),
                  const SizedBox(height: 8),
                  Text(
                    'Rp ${_formatCurrency(_saldoDigiflazz)}',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          color: Colors.green.shade700,
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          Text('Pilih Bank', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: _selectedBank.isNotEmpty ? _selectedBank : null,
            items: _bankList.map((bank) {
              return DropdownMenuItem<String>(
                value: bank['code'],
                child: Text('${bank['name']} (${bank['code']})'),
              );
            }).toList(),
            onChanged: (value) => setState(() => _selectedBank = value ?? ''),
            decoration: InputDecoration(
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              prefixIcon: const Icon(Icons.account_balance),
            ),
          ),
          const SizedBox(height: 24),
          Text('Nomor Rekening', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          TextField(
            onChanged: (val) => setState(() => _nomorRekening = val),
            keyboardType: TextInputType.number,
            decoration: InputDecoration(
              hintText: '1234567890',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              prefixIcon: const Icon(Icons.numbers),
            ),
          ),
          const SizedBox(height: 24),
          Text('Atas Nama', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          TextField(
            onChanged: (val) => setState(() => _atasNama = val),
            decoration: InputDecoration(
              hintText: 'Nama Pemilik Rekening',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              prefixIcon: const Icon(Icons.person),
            ),
          ),
          const SizedBox(height: 24),
          Text('Pilih Nominal', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _nominalOptions.map((option) {
              final isSelected = _selectedNominal == option['value'];
              return ChoiceChip(
                selected: isSelected,
                onSelected: (selected) {
                  setState(() => _selectedNominal = selected ? option['value'] : null);
                },
                label: Text(option['label']),
                selectedColor: Colors.green.shade100,
              );
            }).toList(),
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _isProcessing ? null : _initiateTopup,
              child: _isProcessing
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Lanjutkan Top-up'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryTab() {
    if (_history.isEmpty) {
      return const Center(child: Text('Belum ada history'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _history.length,
      itemBuilder: (context, index) {
        final item = _history[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(item.namaBank, style: Theme.of(context).textTheme.titleSmall),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: item.status == 'berhasil'
                            ? Colors.green.shade100
                            : item.status == 'gagal'
                                ? Colors.red.shade100
                                : Colors.orange.shade100,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        item.statusLabel,
                        style: TextStyle(
                          fontSize: 12,
                          color: item.status == 'berhasil'
                              ? Colors.green.shade700
                              : item.status == 'gagal'
                                  ? Colors.red.shade700
                                  : Colors.orange.shade700,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item.nominalFormatted, style: Theme.of(context).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 4),
                        Text(
                          '${item.nomorRekening} • ${item.atasNama}',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                        ),
                      ],
                    ),
                    Text(
                      item.createdAt.toString().split('.')[0],
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
