import 'package:flutter/material.dart';
import 'package:excel/excel.dart' as excel;
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import '../utils/save_file.dart';
import '../widgets/scanner_view.dart';
import '../widgets/custom_sidebar.dart';

class ScanPulsaScreen extends StatefulWidget {
  const ScanPulsaScreen({super.key});

  @override
  State<ScanPulsaScreen> createState() => _ScanPulsaScreenState();
}

class _ScanPulsaScreenState extends State<ScanPulsaScreen> {
  final ApiService _api = ApiService();
  final _formKey = GlobalKey<FormState>();
  final _scrollController = ScrollController();
  final _nomorController = TextEditingController();

  int? _selectedProviderId;
  int? _selectedNominalId;
  String _selectedMetode = 'tunai';
  String _selectedStatus = 'sukses';
  int? _editingId;

  List<Map<String, dynamic>> _transactions = [];
  List<Map<String, dynamic>> _providers = [];
  List<Map<String, dynamic>> _nominals = [];

  bool _isLoading = false;
  bool _isSaving = false;
  bool _scannerActive = true;
  bool _formExpanded = true;

  static const _metodeOptions = ['tunai', 'saldo', 'transfer', 'grip'];
  static const _statusOptions = ['sukses', 'proses', 'gagal'];

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  @override
  void dispose() {
    _nomorController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    setState(() => _isLoading = true);
    try {
      await _loadProviders();
      await _loadTransactions();
    } catch (e) {
      debugPrint('Error loading data: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _loadProviders() async {
    try {
      final res = await _api.getLaporanProviders();
      if (res.statusCode == 200 && res.data['success'] == true) {
        final list = res.data['data'] as List? ?? [];
        setState(() => _providers = List<Map<String, dynamic>>.from(list));
        debugPrint('Providers loaded: ${_providers.length}');
      } else {
        throw Exception('Gagal load providers');
      }
    } catch (e) {
      debugPrint('Error loading providers: $e');
      _showSnack('Gagal memuat data provider', color: Colors.red);
    }
  }

  Future<void> _loadTransactions() async {
    try {
      final res = await _api.getLaporanPulsa();
      if (res.statusCode == 200 && res.data['success'] == true) {
        final list = res.data['data'] as List? ?? [];
        setState(() => _transactions = List<Map<String, dynamic>>.from(list));
        debugPrint('Transactions loaded: ${_transactions.length}');
      } else {
        throw Exception('Gagal load transaksi');
      }
    } catch (e) {
      debugPrint('Error loading transactions: $e');
      _showSnack('Gagal memuat data transaksi', color: Colors.red);
    }
  }

  Future<void> _loadNominalForProvider(int providerId) async {
    try {
      final res = await _api.getNominalsForProvider(providerId);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final list = res.data['data'] as List? ?? [];
        setState(() {
          _nominals = List<Map<String, dynamic>>.from(list);
          _selectedNominalId = null;
        });
        debugPrint('Nominals loaded: ${_nominals.length}');
      } else {
        setState(() => _nominals = []);
        debugPrint('Error loading nominals: ${res.data['message']}');
      }
    } catch (e) {
      setState(() => _nominals = []);
      debugPrint('Error loading nominals: $e');
    }
  }

  void _handleScan(String value) {
    final parts = value.split(RegExp(r'[|,;:]'));
    final nomor = parts.isNotEmpty ? parts[0].trim() : value.trim();
    setState(() {
      _nomorController.text = nomor;
      _scannerActive = false;
    });
    _showSnack('✓ Scan berhasil: $nomor — Pilih provider & nominal', color: Colors.indigo);
  }

  Future<void> _simpanEntry() async {
    // Validasi form terlebih dahulu
    if (!_formKey.currentState!.validate()) {
      _showSnack('Form tidak valid, periksa input', color: Colors.red);
      return;
    }

    final noTujuan = _nomorController.text.trim();
    if (noTujuan.isEmpty) {
      _showSnack('Nomor tujuan wajib diisi', color: Colors.red);
      return;
    }

    // Pastikan provider dan nominal sudah dipilih
    if (_selectedProviderId == null) {
      _showSnack('Pilih provider terlebih dahulu', color: Colors.red);
      return;
    }
    if (_selectedNominalId == null) {
      _showSnack('Pilih nominal terlebih dahulu', color: Colors.red);
      return;
    }

    // Debug: lihat nilai sebelum dikirim
    print('🔍 DEBUG SIMPAN:');
    print('   providerId = $_selectedProviderId (${_selectedProviderId.runtimeType})');
    print('   nominalId = $_selectedNominalId (${_selectedNominalId.runtimeType})');
    print('   metode = $_selectedMetode');
    print('   noTujuan = $noTujuan');

    setState(() => _isSaving = true);
    try {
      Response res;
      if (_editingId != null) {
        res = await _api.updateLaporanPulsa(
          id: _editingId!,
          noTujuan: noTujuan,
          providerId: _selectedProviderId!,
          nominalId: _selectedNominalId!,
          metodePembayaran: _selectedMetode,
          status: _selectedStatus,
        );
      } else {
        res = await _api.createLaporanPulsa(
          noTujuan: noTujuan,
          providerId: _selectedProviderId!,
          nominalId: _selectedNominalId!,
          metodePembayaran: _selectedMetode,
        );
      }

      print('✅ RESPONSE: ${res.statusCode} - ${res.data}');

      if (res.statusCode == 201 || res.statusCode == 200) {
        await _loadTransactions();
        _resetForm();
        _showSnack(
          _editingId != null ? 'Data berhasil diupdate' : 'Transaksi berhasil disimpan',
          color: Colors.green,
        );
      } else {
        _showSnack(res.data['message'] ?? 'Gagal menyimpan', color: Colors.red);
      }
    } on DioException catch (e) {
      print('❌ DIO ERROR: ${e.message}');
      print('📄 RESPONSE DATA: ${e.response?.data}');
      _showSnack(e.response?.data['message'] ?? 'Koneksi gagal: ${e.message}', color: Colors.red);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _editEntry(Map<String, dynamic> transaction) async {
    final providerId = transaction['provider_id'] is int
        ? transaction['provider_id']
        : int.tryParse(transaction['provider_id']?.toString() ?? '') ?? 0;
    final nominalId = transaction['nominal_id'] is int
        ? transaction['nominal_id']
        : int.tryParse(transaction['nominal_id']?.toString() ?? '') ?? 0;
    final id = transaction['id'] is int
        ? transaction['id']
        : int.tryParse(transaction['id']?.toString() ?? '') ?? 0;

    await _loadNominalForProvider(providerId);
    setState(() {
      _editingId = id;
      _nomorController.text = transaction['no_tujuan']?.toString() ?? '';
      _selectedProviderId = providerId;
      _selectedNominalId = nominalId;
      _selectedMetode = transaction['metode_pembayaran'] ?? 'tunai';
      _selectedStatus = transaction['status'] ?? 'sukses';
      _scannerActive = false;
      _formExpanded = true;
    });
    _showSnack('Mode edit aktif — ubah data lalu tekan Update', color: Colors.indigo);
  }

  Future<void> _hapusEntry(int id) async {
    final confirm = await _showConfirmDialog('Hapus Data', 'Yakin ingin menghapus transaksi ini?');
    if (confirm != true) return;
    try {
      final res = await _api.deleteLaporanPulsa(id);
      if (res.statusCode == 200) {
        await _loadTransactions();
        _showSnack('Data berhasil dihapus', color: Colors.green);
      }
    } catch (e) {
      _showSnack('Gagal menghapus: $e', color: Colors.red);
    }
  }

  Future<void> _hapusSemua() async {
    final confirm = await _showConfirmDialog('Hapus Semua Data', 'Semua transaksi akan dihapus permanen. Yakin?');
    if (confirm != true) return;
    setState(() => _isSaving = true);
    try {
      for (final trx in _transactions) {
        final id = trx['id'] is int ? trx['id'] : int.tryParse(trx['id']?.toString() ?? '') ?? 0;
        await _api.deleteLaporanPulsa(id);
      }
      await _loadTransactions();
      _showSnack('Semua data berhasil dihapus', color: Colors.red);
    } catch (e) {
      _showSnack('Gagal hapus semua: $e', color: Colors.red);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _resetForm() {
    _nomorController.clear();
    setState(() {
      _selectedProviderId = null;
      _selectedNominalId = null;
      _selectedMetode = 'tunai';
      _selectedStatus = 'sukses';
      _editingId = null;
      _nominals = [];
      _scannerActive = true;
    });
  }

  Future<void> _exportExcel() async {
    if (_transactions.isEmpty) {
      _showSnack('Tidak ada data untuk diekspor');
      return;
    }
    setState(() => _isSaving = true);

    try {
      final workbook = excel.Excel.createExcel();
      final sheet = workbook['Laporan Pulsa'];
      final headers = [
        'No', 'No Transaksi', 'Tanggal', 'Provider', 'Kasir',
        'Nominal', 'No Tujuan', 'Harga Jual', 'Keuntungan', 'Metode', 'Status'
      ];
      for (int i = 0; i < headers.length; i++) {
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: i, rowIndex: 0)).value = headers[i];
      }

      for (int i = 0; i < _transactions.length; i++) {
        final t = _transactions[i];
        final row = i + 1;
        final tgl = t['created_at'] != null ? DateTime.parse(t['created_at']).toLocal() : DateTime.now();
        final tglStr = '${tgl.day.toString().padLeft(2, '0')}/${tgl.month.toString().padLeft(2, '0')}/${tgl.year} ${tgl.hour.toString().padLeft(2, '0')}:${tgl.minute.toString().padLeft(2, '0')}';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 0, rowIndex: row)).value = row;
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 1, rowIndex: row)).value = t['no_transaksi'] ?? '-';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 2, rowIndex: row)).value = tglStr;
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 3, rowIndex: row)).value = t['nama_provider'] ?? '-';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 4, rowIndex: row)).value = t['nama_user'] ?? 'Sistem';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 5, rowIndex: row)).value = t['nominal_paket'] ?? t['nominal'] ?? 0;
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 6, rowIndex: row)).value = t['no_tujuan'] ?? '';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 7, rowIndex: row)).value = t['harga_jual'] ?? 0;
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 8, rowIndex: row)).value = t['keuntungan'] ?? 0;
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 9, rowIndex: row)).value = t['metode_pembayaran'] ?? '';
        sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 10, rowIndex: row)).value = t['status'] ?? '';
      }

      final bytes = workbook.encode();
      if (bytes == null) {
        _showSnack('Gagal membuat file Excel', color: Colors.red);
        return;
      }
      final filename = 'laporan_pulsa_${DateTime.now().millisecondsSinceEpoch}.xlsx';
      await saveFileBytes(filename, bytes);
      _showSnack('Export berhasil: $filename', color: Colors.green);
    } catch (e) {
      _showSnack('Gagal export: $e', color: Colors.red);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _showSnack(String msg, {Color? color}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: color));
  }

  Future<bool?> _showConfirmDialog(String title, String content) {
    return showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(children: [const Icon(Icons.warning, color: Colors.red), const SizedBox(width: 8), Text(title)]),
        content: Text(content),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Ya, Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(dynamic value) {
    final v = value is num ? value : (value?.toString().isNotEmpty == true ? num.tryParse(value.toString()) ?? 0 : 0);
    return 'Rp ${v.toStringAsFixed(0).replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}';
  }

  Color _statusColor(String status) {
    switch (status.toLowerCase()) {
      case 'sukses':
      case 'berhasil':
        return Colors.green;
      case 'gagal':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }

  Future<void> _onSidebarItemSelected(int index) async {
    final roleId = await _api.getUserRole();
    if (!mounted) return;
    switch (index) {
      case 0:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/dashboard');
        else Navigator.pushReplacementNamed(context, '/kasir');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/pulsa-provider');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/topup-saldo');
        break;
      case 3:
        break;
      case 4:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/user');
        else Navigator.pushReplacementNamed(context, '/history');
        break;
      case 5:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/history');
        break;
      default:
        _showSnack('Fitur belum tersedia');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: CustomSidebar(selectedIndex: 3, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(
        title: Text(_editingId != null ? 'Edit Laporan Pulsa' : 'Scan & Laporan Pulsa'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadTransactions, tooltip: 'Refresh'),
          if (_transactions.isNotEmpty)
            IconButton(icon: const Icon(Icons.file_download), onPressed: _isSaving ? null : _exportExcel, tooltip: 'Export Excel'),
          if (_transactions.isNotEmpty)
            IconButton(icon: const Icon(Icons.delete_sweep, color: Colors.red), onPressed: _hapusSemua, tooltip: 'Hapus Semua'),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Form Panel
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  color: _editingId != null ? Colors.orange.shade50 : Colors.indigo.shade50,
                  child: Column(
                    children: [
                      InkWell(
                        onTap: () => setState(() => _formExpanded = !_formExpanded),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          child: Row(
                            children: [
                              Icon(_editingId != null ? Icons.edit : Icons.add_circle,
                                  color: _editingId != null ? Colors.orange : const Color(0xFF6366f1), size: 20),
                              const SizedBox(width: 8),
                              Text(_editingId != null ? 'Edit Transaksi' : 'Tambah Transaksi Baru',
                                  style: TextStyle(fontWeight: FontWeight.bold,
                                      color: _editingId != null ? Colors.orange.shade800 : const Color(0xFF6366f1))),
                              const Spacer(),
                              Icon(_formExpanded ? Icons.expand_less : Icons.expand_more, color: Colors.grey),
                            ],
                          ),
                        ),
                      ),
                      if (_formExpanded)
                        Padding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                          child: Form(
                            key: _formKey,
                            child: Column(
                              children: [
                                if (_scannerActive && _editingId == null) ...[
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: SizedBox(height: 180, child: ScannerView(onDetect: _handleScan)),
                                  ),
                                  const SizedBox(height: 10),
                                ] else if (_editingId == null) ...[
                                  Container(
                                    height: 56,
                                    decoration: BoxDecoration(
                                      color: Colors.grey.shade100,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: Colors.grey.shade300),
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const Icon(Icons.pause_circle, color: Colors.grey),
                                        const SizedBox(width: 8),
                                        const Text('Scanner dijeda', style: TextStyle(color: Colors.grey)),
                                        const SizedBox(width: 16),
                                        TextButton(
                                          onPressed: () => setState(() => _scannerActive = true),
                                          child: const Text('Aktifkan'),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                ],
                                // Baris 1: No Tujuan & Provider
                                Row(
                                  children: [
                                    Expanded(
                                      flex: 4,
                                      child: TextFormField(
                                        controller: _nomorController,
                                        keyboardType: TextInputType.phone,
                                        decoration: InputDecoration(
                                          labelText: 'No. Tujuan *',
                                          prefixIcon: const Icon(Icons.phone, size: 18),
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          isDense: true,
                                        ),
                                        validator: (v) => v?.trim().isEmpty == true ? 'Wajib diisi' : null,
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      flex: 4,
                                      child: DropdownButtonFormField<int>(
                                        value: _selectedProviderId,
                                        isExpanded: true,
                                        hint: const Text('Pilih Provider'),
                                        decoration: InputDecoration(
                                          labelText: 'Provider *',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          isDense: true,
                                        ),
                                        items: _providers.isEmpty
                                            ? [const DropdownMenuItem<int>(value: null, child: Text('Tidak ada provider'))]
                                            : _providers.map((p) {
                                                final id = p['id'] is int ? p['id'] as int : int.tryParse(p['id']?.toString() ?? '') ?? 0;
                                                return DropdownMenuItem<int>(
                                                  value: id,
                                                  child: Text(p['nama_provider'] ?? 'Unknown'),
                                                );
                                              }).toList(),
                                        onChanged: (v) {
                                          if (v != null) {
                                            setState(() => _selectedProviderId = v);
                                            _loadNominalForProvider(v);
                                          }
                                        },
                                        validator: (v) => v == null ? 'Pilih provider' : null,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                // Baris 2: Nominal & Metode & Status (jika edit)
                                Row(
                                  children: [
                                    Expanded(
                                      flex: 4,
                                      child: DropdownButtonFormField<int>(
                                        value: _selectedNominalId,
                                        isExpanded: true,
                                        hint: const Text('Pilih Nominal'),
                                        decoration: InputDecoration(
                                          labelText: 'Nominal *',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          isDense: true,
                                        ),
                                        items: _nominals.isEmpty
                                            ? [const DropdownMenuItem<int>(value: null, child: Text('Pilih provider dulu'))]
                                            : _nominals.map((n) {
                                                final id = n['id'] is int ? n['id'] as int : int.tryParse(n['id']?.toString() ?? '') ?? 0;
                                                final nom = n['nominal']?.toString() ?? '0';
                                                final harga = n['harga_jual']?.toString() ?? '0';
                                                return DropdownMenuItem<int>(
                                                  value: id,
                                                  child: Text('$nom - Rp $harga'),
                                                );
                                              }).toList(),
                                        onChanged: (v) => setState(() => _selectedNominalId = v),
                                        validator: (v) => v == null ? 'Pilih nominal' : null,
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      flex: 3,
                                      child: DropdownButtonFormField<String>(
                                        value: _selectedMetode,
                                        decoration: InputDecoration(
                                          labelText: 'Metode',
                                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                          isDense: true,
                                        ),
                                        items: _metodeOptions.map((m) => DropdownMenuItem(
                                            value: m, child: Text(m[0].toUpperCase() + m.substring(1)))).toList(),
                                        onChanged: (v) => setState(() => _selectedMetode = v ?? 'tunai'),
                                      ),
                                    ),
                                    if (_editingId != null) ...[
                                      const SizedBox(width: 8),
                                      Expanded(
                                        flex: 3,
                                        child: DropdownButtonFormField<String>(
                                          value: _selectedStatus,
                                          decoration: InputDecoration(
                                            labelText: 'Status',
                                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                            isDense: true,
                                          ),
                                          items: _statusOptions.map((s) => DropdownMenuItem(
                                              value: s, child: Text(s[0].toUpperCase() + s.substring(1)))).toList(),
                                          onChanged: (v) => setState(() => _selectedStatus = v ?? 'sukses'),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  children: [
                                    Expanded(
                                      child: ElevatedButton.icon(
                                        onPressed: _isSaving ? null : _simpanEntry,
                                        icon: _isSaving
                                            ? const SizedBox(width: 16, height: 16,
                                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                            : Icon(_editingId != null ? Icons.save : Icons.add),
                                        label: Text(_editingId != null ? 'Update' : 'Simpan ke Laporan'),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: _editingId != null ? Colors.orange : const Color(0xFF6366f1),
                                          foregroundColor: Colors.white,
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        ),
                                      ),
                                    ),
                                    if (_editingId != null) ...[
                                      const SizedBox(width: 8),
                                      OutlinedButton.icon(
                                        onPressed: _resetForm,
                                        icon: const Icon(Icons.cancel),
                                        label: const Text('Batal'),
                                      ),
                                    ],
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                // Header Tabel
                Container(
                  padding: const EdgeInsets.fromLTRB(16, 10, 16, 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border(top: BorderSide(color: Colors.grey.shade300), bottom: BorderSide(color: Colors.grey.shade200)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.table_chart, color: Color(0xFF6366f1), size: 18),
                      const SizedBox(width: 6),
                      const Text('Laporan Penjualan Pulsa', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFF6366f1).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text('${_transactions.length} transaksi',
                            style: const TextStyle(color: Color(0xFF6366f1), fontSize: 11, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ),
                // Tabel Data
                Expanded(
                  child: _transactions.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.receipt_long, size: 60, color: Colors.grey.shade300),
                              const SizedBox(height: 12),
                              Text('Belum ada data laporan', style: TextStyle(color: Colors.grey.shade500, fontSize: 15)),
                              const SizedBox(height: 4),
                              Text('Scan atau input manual untuk mencatat',
                                  style: TextStyle(color: Colors.grey.shade400, fontSize: 12)),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _loadTransactions,
                          child: SingleChildScrollView(
                            scrollDirection: Axis.vertical,
                            controller: _scrollController,
                            child: SingleChildScrollView(
                              scrollDirection: Axis.horizontal,
                              child: DataTable(
                                headingRowColor: WidgetStateProperty.all(Colors.indigo.shade50),
                                dataRowMinHeight: 44,
                                dataRowMaxHeight: 44,
                                columnSpacing: 12,
                                horizontalMargin: 12,
                                columns: const [
                                  DataColumn(label: Text('No', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('No Transaksi', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Tanggal', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Provider', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Kasir', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Nominal', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('No Tujuan', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Harga Jual', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Keuntungan', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Metode', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Status', style: TextStyle(fontWeight: FontWeight.bold))),
                                  DataColumn(label: Text('Aksi', style: TextStyle(fontWeight: FontWeight.bold))),
                                ],
                                rows: List.generate(_transactions.length, (i) {
                                  final t = _transactions[i];
                                  final tgl = t['created_at'] != null
                                      ? DateTime.parse(t['created_at']).toLocal()
                                      : DateTime.now();
                                  final tglStr = '${tgl.day.toString().padLeft(2, '0')}/${tgl.month.toString().padLeft(2, '0')} ${tgl.hour.toString().padLeft(2, '0')}:${tgl.minute.toString().padLeft(2, '0')}';
                                  final nominalValue = t['nominal_paket'] ?? t['nominal'] ?? 0;
                                  final id = t['id'] is int ? t['id'] : int.tryParse(t['id']?.toString() ?? '') ?? 0;

                                  return DataRow(
                                    color: WidgetStateProperty.resolveWith((states) {
                                      if (_editingId == id) return Colors.orange.shade50;
                                      return i.isEven ? Colors.white : Colors.grey.shade50;
                                    }),
                                    cells: [
                                      DataCell(Text('${i + 1}', style: const TextStyle(color: Colors.grey))),
                                      DataCell(Text(t['no_transaksi'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w500))),
                                      DataCell(Text(tglStr, style: const TextStyle(fontSize: 11))),
                                      DataCell(Text(t['nama_provider'] ?? '-')),
                                      DataCell(Text(t['nama_user'] ?? 'Sistem')),
                                      DataCell(Text('Rp ${nominalValue.toString().replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}',
                                          style: const TextStyle(fontWeight: FontWeight.w600))),
                                      DataCell(Text(t['no_tujuan'] ?? '')),
                                      DataCell(Text(_formatCurrency(t['harga_jual']),
                                          style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF6366f1)))),
                                      DataCell(Text(_formatCurrency(t['keuntungan']),
                                          style: const TextStyle(color: Colors.green, fontWeight: FontWeight.w500))),
                                      DataCell(Text(t['metode_pembayaran']?.toString().toUpperCase() ?? '-')),
                                      DataCell(Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: _statusColor(t['status'] ?? 'proses').withOpacity(0.12),
                                          borderRadius: BorderRadius.circular(20),
                                        ),
                                        child: Text(
                                          (t['status'] ?? 'proses').toUpperCase(),
                                          style: TextStyle(
                                              color: _statusColor(t['status'] ?? 'proses'),
                                              fontSize: 11,
                                              fontWeight: FontWeight.w600),
                                        ),
                                      )),
                                      DataCell(Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          IconButton(
                                            icon: const Icon(Icons.edit, color: Color(0xFF6366f1), size: 18),
                                            onPressed: () => _editEntry(t),
                                            tooltip: 'Edit',
                                            padding: EdgeInsets.zero,
                                            constraints: const BoxConstraints(),
                                          ),
                                          const SizedBox(width: 8),
                                          IconButton(
                                            icon: const Icon(Icons.delete, color: Colors.red, size: 18),
                                            onPressed: () => _hapusEntry(id),
                                            tooltip: 'Hapus',
                                            padding: EdgeInsets.zero,
                                            constraints: const BoxConstraints(),
                                          ),
                                        ],
                                      )),
                                    ],
                                  );
                                }),
                              ),
                            ),
                          ),
                        ),
                ),
              ],
            ),
    );
  }
}