import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:excel/excel.dart' as excel;
import 'package:shared_preferences/shared_preferences.dart';

import '../models/scanned_pulsa_entry.dart';
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

  final _nomorController = TextEditingController();
  final _providerController = TextEditingController();
  final _nominalController = TextEditingController();
  final _catatanController = TextEditingController();

  String _selectedMetode = 'tunai';
  String _selectedStatus = 'sukses';
  int? _editingId;

  List<ScannedPulsaEntry> _entries = [];
  bool _isSaving = false;
  bool _scannerActive = true;

  static const _metodeOptions = ['tunai', 'transfer', 'qris', 'debit'];
  static const _statusOptions = ['sukses', 'pending', 'gagal'];

  @override
  void initState() {
    super.initState();
    _loadEntries();
  }

  @override
  void dispose() {
    _nomorController.dispose();
    _providerController.dispose();
    _nominalController.dispose();
    _catatanController.dispose();
    super.dispose();
  }

  // ── Storage ──────────────────────────────────────────
  Future<void> _loadEntries() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString('scanned_pulsa_entries') ?? '[]';
    final jsonData = jsonDecode(raw) as List<dynamic>;
    setState(() {
      _entries = jsonData
          .map((e) => ScannedPulsaEntry.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
    });
  }

  Future<void> _saveEntries() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
      'scanned_pulsa_entries',
      jsonEncode(_entries.map((e) => e.toJson()).toList()),
    );
  }

  // ── Scan ─────────────────────────────────────────────
  void _handleScan(String value) {
    // Format barcode: "08123456789|Telkomsel|10000" atau hanya nomor hp
    final parts = value.split(RegExp(r'[|,;:]'));
    final nomor = parts.isNotEmpty ? parts[0].trim() : value.trim();
    final nominalRaw = parts.length > 1 ? parts[1].replaceAll(RegExp(r'[^0-9]'), '') : '';
    final provider = parts.length > 2 ? parts[2].trim() : '';

    // Isi form otomatis dari hasil scan
    setState(() {
      _nomorController.text = nomor;
      if (provider.isNotEmpty) _providerController.text = provider;
      if (nominalRaw.isNotEmpty) _nominalController.text = nominalRaw;
      _scannerActive = false; // pause scanner setelah scan
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Scan berhasil: $nomor — Periksa & simpan data'),
        backgroundColor: Colors.indigo,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // ── Tambah entry ─────────────────────────────────────
  Future<void> _simpanEntry() async {
    if (!_formKey.currentState!.validate()) return;

    final createdAt = _editingId != null
      ? _entries.where((e) => e.id == _editingId).map((e) => e.createdAt).firstWhere((_) => true, orElse: () => DateTime.now())
      : DateTime.now();

    final entry = ScannedPulsaEntry(
      id: _editingId ?? DateTime.now().millisecondsSinceEpoch,
      noTujuan: _nomorController.text.trim(),
      namaProvider: _providerController.text.trim().isEmpty
        ? 'Unknown'
        : _providerController.text.trim(),
      providerId: 0,
      nominalId: 0,
      nominal: int.tryParse(_nominalController.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0,
      metodePembayaran: _selectedMetode,
      status: _selectedStatus,
      createdAt: createdAt,
    );

    setState(() {
      if (_editingId != null) {
        final index = _entries.indexWhere((e) => e.id == _editingId);
        if (index != -1) _entries[index] = entry;
      } else {
        _entries.insert(0, entry);
      }
    });
    await _saveEntries();

    // Reset form
    _nomorController.clear();
    _providerController.clear();
    _nominalController.clear();
    _catatanController.clear();
    setState(() {
      _selectedMetode = 'tunai';
      _selectedStatus = 'sukses';
      _scannerActive = true;
      _editingId = null;
    });

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Data berhasil disimpan ke laporan'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  // ── Hapus satu ───────────────────────────────────────
  Future<void> _hapusEntry(int id) async {
    setState(() {
      _entries.removeWhere((e) => e.id == id);
      if (_editingId == id) {
        _editingId = null;
        _resetForm();
      }
    });
    await _saveEntries();
  }

  void _editEntry(ScannedPulsaEntry entry) {
    setState(() {
      _editingId = entry.id;
      _nomorController.text = entry.noTujuan;
      _providerController.text = entry.namaProvider;
      _nominalController.text = entry.nominal.toString();
      _selectedMetode = entry.metodePembayaran;
      _selectedStatus = entry.status;
      // note: model no longer contains 'catatan'
      _scannerActive = false;
    });
  }

  void _resetForm() {
    _nomorController.clear();
    _providerController.clear();
    _nominalController.clear();
    _catatanController.clear();
    setState(() {
      _selectedMetode = 'tunai';
      _selectedStatus = 'sukses';
      _editingId = null;
      _scannerActive = true;
    });
  }

  // ── Hapus semua ──────────────────────────────────────
  Future<void> _hapusSemua() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.warning, color: Colors.red),
            SizedBox(width: 8),
            Text('Hapus Semua Data'),
          ],
        ),
        content: const Text('Semua data laporan akan dihapus permanen. Yakin?'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Hapus Semua', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
    if (confirm != true) return;
    setState(() => _entries.clear());
    await _saveEntries();
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Semua data dihapus'), backgroundColor: Colors.red),
      );
    }
  }

  // ── Export Excel ─────────────────────────────────────
  Future<void> _exportExcel() async {
    if (_entries.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data untuk diekspor')),
      );
      return;
    }

    setState(() => _isSaving = true);

    final workbook = excel.Excel.createExcel();
    final sheet = workbook['Laporan Pulsa'];

    // Header
    sheet.appendRow([
      'No', 'Tanggal', 'Jam', 'No. Tujuan',
      'Provider', 'Nominal', 'Metode Bayar', 'Status',
    ]);

    // Data
    for (int i = 0; i < _entries.length; i++) {
      final e = _entries[i];
      final tgl = '${e.createdAt.day.toString().padLeft(2,'0')}/'
          '${e.createdAt.month.toString().padLeft(2,'0')}/'
          '${e.createdAt.year}';
      final jam = '${e.createdAt.hour.toString().padLeft(2,'0')}:'
          '${e.createdAt.minute.toString().padLeft(2,'0')}';
      sheet.appendRow([
        i + 1,
        tgl,
        jam,
        e.noTujuan,
        e.namaProvider,
        e.nominal,
        e.metodePembayaran,
        e.status,
      ]);
    }

    final bytes = workbook.encode();
    if (bytes == null) {
      setState(() => _isSaving = false);
      return;
    }

    final filename =
        'laporan_pulsa_${DateTime.now().day}${DateTime.now().month}${DateTime.now().year}.xlsx';
    try {
      await saveFileBytes(filename, bytes);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Export berhasil: $filename'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal export: $e')),
        );
      }
    } finally {
      setState(() => _isSaving = false);
    }
  }

  // ── Sidebar nav ──────────────────────────────────────
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
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Fitur belum tersedia')),
        );
    }
  }

  // ── Warna status ─────────────────────────────────────
  Color _statusColor(String status) {
    switch (status) {
      case 'sukses':
      case 'berhasil':
        return Colors.green;
      case 'gagal':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }

  // ── UI ────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: CustomSidebar(selectedIndex: 3, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(
        title: const Text('Scan & Laporan Pulsa'),
        actions: [
          if (_entries.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.download),
              tooltip: 'Export Excel',
              onPressed: _isSaving ? null : _exportExcel,
            ),
          if (_entries.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_sweep, color: Colors.red),
              tooltip: 'Hapus Semua',
              onPressed: _hapusSemua,
            ),
        ],
      ),
      body: Column(
        children: [
          // ── Bagian Atas: Scanner + Form ──
          Container(
            color: Colors.grey.shade50,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Scanner toggle
                  Row(
                    children: [
                      const Icon(Icons.qr_code_scanner, color: Color(0xFF6366f1)),
                      const SizedBox(width: 8),
                      const Text('Pindai Pulsa',
                          style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                      const Spacer(),
                      TextButton.icon(
                        onPressed: () => setState(() => _scannerActive = !_scannerActive),
                        icon: Icon(_scannerActive ? Icons.pause : Icons.play_arrow, size: 18),
                        label: Text(_scannerActive ? 'Pause' : 'Aktifkan'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Scanner view
                  if (_scannerActive)
                    ScannerView(onDetect: _handleScan)
                  else
                    Container(
                      height: 80,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Center(
                        child: Text('Scanner dijeda — isi form lalu simpan',
                            style: TextStyle(color: Colors.grey)),
                      ),
                    ),

                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 8),

                  // Form input
                  const Text('Detail Transaksi',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),

                  Form(
                    key: _formKey,
                    child: Column(
                      children: [
                        // Nomor + Provider
                        Row(
                          children: [
                            Expanded(
                              flex: 3,
                              child: TextFormField(
                                controller: _nomorController,
                                keyboardType: TextInputType.phone,
                                decoration: const InputDecoration(
                                  labelText: 'No. Tujuan *',
                                  prefixIcon: Icon(Icons.phone, size: 18),
                                  border: OutlineInputBorder(),
                                  isDense: true,
                                ),
                                validator: (v) => v?.trim().isEmpty == true
                                    ? 'Wajib diisi'
                                    : null,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              flex: 2,
                              child: TextFormField(
                                controller: _providerController,
                                decoration: const InputDecoration(
                                  labelText: 'Provider',
                                  border: OutlineInputBorder(),
                                  isDense: true,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Nominal + Metode
                        Row(
                          children: [
                            Expanded(
                              flex: 2,
                              child: TextFormField(
                                controller: _nominalController,
                                keyboardType: TextInputType.number,
                                decoration: const InputDecoration(
                                  labelText: 'Nominal *',
                                  prefixIcon: Icon(Icons.money, size: 18),
                                  border: OutlineInputBorder(),
                                  isDense: true,
                                ),
                                validator: (v) {
                                  if (v?.trim().isEmpty == true) return 'Wajib diisi';
                                  if (int.tryParse(v!.replaceAll(RegExp(r'[^0-9]'), '')) == null) {
                                    return 'Tidak valid';
                                  }
                                  return null;
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              flex: 2,
                              child: DropdownButtonFormField<String>(
                                value: _selectedMetode,
                                decoration: const InputDecoration(
                                  labelText: 'Metode Bayar',
                                  border: OutlineInputBorder(),
                                  isDense: true,
                                ),
                                items: _metodeOptions
                                    .map((m) => DropdownMenuItem(
                                        value: m,
                                        child: Text(m[0].toUpperCase() + m.substring(1))))
                                    .toList(),
                                onChanged: (v) =>
                                    setState(() => _selectedMetode = v ?? 'tunai'),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              flex: 2,
                              child: DropdownButtonFormField<String>(
                                value: _selectedStatus,
                                decoration: const InputDecoration(
                                  labelText: 'Status',
                                  border: OutlineInputBorder(),
                                  isDense: true,
                                ),
                                items: _statusOptions
                                    .map((s) => DropdownMenuItem(
                                        value: s,
                                        child: Text(s[0].toUpperCase() + s.substring(1))))
                                    .toList(),
                                onChanged: (v) =>
                                    setState(() => _selectedStatus = v ?? 'sukses'),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Catatan
                        TextFormField(
                          controller: _catatanController,
                          decoration: const InputDecoration(
                            labelText: 'Catatan (opsional)',
                            prefixIcon: Icon(Icons.note, size: 18),
                            border: OutlineInputBorder(),
                            isDense: true,
                          ),
                        ),
                        const SizedBox(height: 14),

                        // Tombol simpan
                        Row(
                          children: [
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: _simpanEntry,
                                icon: const Icon(Icons.save, color: Colors.white),
                                label: Text(
                                  _editingId == null ? 'Simpan ke Laporan' : 'Perbarui Entri',
                                  style: const TextStyle(
                                      color: Colors.white, fontWeight: FontWeight.w600),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF6366f1),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(8)),
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                ),
                              ),
                            ),
                            if (_editingId != null) ...[
                              const SizedBox(width: 10),
                              OutlinedButton(
                                onPressed: _resetForm,
                                child: const Text('Batal'),
                              ),
                            ]
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── Bagian Bawah: Tabel Laporan ──
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade300)),
            ),
            child: Row(
              children: [
                const Icon(Icons.table_chart, color: Color(0xFF6366f1), size: 18),
                const SizedBox(width: 6),
                const Text('Laporan Hari Ini',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF6366f1).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '${_entries.length} transaksi',
                    style: const TextStyle(
                        color: Color(0xFF6366f1), fontSize: 11, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ),

          // Tabel
          Expanded(
            child: _entries.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.receipt_long, size: 56, color: Colors.grey.shade300),
                        const SizedBox(height: 12),
                        Text('Belum ada data laporan',
                            style: TextStyle(color: Colors.grey.shade500)),
                        const SizedBox(height: 4),
                        Text('Scan atau input manual untuk mulai mencatat',
                            style: TextStyle(color: Colors.grey.shade400, fontSize: 12)),
                      ],
                    ),
                  )
                : SingleChildScrollView(
                    scrollDirection: Axis.vertical,
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: DataTable(
                        headingRowColor: MaterialStateProperty.all(Colors.indigo.shade50),
                        dataRowMinHeight: 42,
                        dataRowMaxHeight: 42,
                        columnSpacing: 16,
                        columns: const [
                          DataColumn(label: Text('No', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Waktu', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('No. Tujuan', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Provider', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Nominal', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Metode', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Status', style: TextStyle(fontWeight: FontWeight.bold))),
                          DataColumn(label: Text('Aksi', style: TextStyle(fontWeight: FontWeight.bold))),
                        ],
                        rows: List.generate(_entries.length, (i) {
                          final e = _entries[i];
                          final jam =
                              '${e.createdAt.hour.toString().padLeft(2, '0')}:'
                              '${e.createdAt.minute.toString().padLeft(2, '0')}';
                          final nominal = 'Rp ${e.nominal.toString().replaceAllMapped(
                                RegExp(r'\B(?=(\d{3})+(?!\d))'), (m) => '.')}';

                          return DataRow(
                            cells: [
                              DataCell(Text('${i + 1}',
                                  style: const TextStyle(color: Colors.grey))),
                              DataCell(Text(jam)),
                              DataCell(Text(e.noTujuan,
                                  style: const TextStyle(fontWeight: FontWeight.w500))),
                              DataCell(Text(e.namaProvider)),
                              DataCell(Text(nominal,
                                  style: const TextStyle(fontWeight: FontWeight.w600))),
                              DataCell(Text(
                                e.metodePembayaran[0].toUpperCase() +
                                    e.metodePembayaran.substring(1),
                              )),
                              DataCell(
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: _statusColor(e.status).withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    e.status[0].toUpperCase() + e.status.substring(1),
                                    style: TextStyle(
                                      color: _statusColor(e.status),
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ),
                              DataCell(
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    IconButton(
                                      icon: const Icon(Icons.edit, color: Colors.indigo, size: 18),
                                      onPressed: () => _editEntry(e),
                                      tooltip: 'Edit',
                                      padding: EdgeInsets.zero,
                                      constraints: const BoxConstraints(),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.delete, color: Colors.red, size: 18),
                                      onPressed: () => _hapusEntry(e.id),
                                      tooltip: 'Hapus',
                                      padding: EdgeInsets.zero,
                                      constraints: const BoxConstraints(),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          );
                        }),
                      ),
                    ),
                  ),
          ),
        ],
      ),
    );
  }
}
