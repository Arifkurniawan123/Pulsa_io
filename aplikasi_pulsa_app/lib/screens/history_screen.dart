import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:dio/dio.dart';
import 'package:excel/excel.dart' as excel;
import 'package:share_plus/share_plus.dart';
import 'package:path_provider/path_provider.dart';
import '../services/api_service.dart';
import '../widgets/custom_sidebar.dart';
import '../models/topup_saldo_history.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> with SingleTickerProviderStateMixin {
  final ApiService _api = ApiService();
  late TabController _tabController;
  bool _isLoading = true;
  bool _isExporting = false;
  String _error = '';
  int _userRole = 0;

  List<TopupSaldoHistory> _saldoHistory = [];
  List<TopupSaldoHistory> _pulsaHistory = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadUserRole();
    _loadHistory();
  }

  Future<void> _loadUserRole() async {
    final roleId = await _api.getUserRole();
    if (!mounted) return;
    setState(() {
      _userRole = roleId ?? 0;
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadHistory() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final [saldoRes, pulsaRes] = await Future.wait([
        _api.getTopupHistory(tipe: 'topup_saldo', limit: 100),
        _api.getTopupHistory(tipe: 'topup_pulsa', limit: 100),
      ]);

      if (saldoRes.statusCode == 200 && saldoRes.data['success'] == true) {
        final List raw = saldoRes.data['data'] as List? ?? [];
        _saldoHistory = raw.map((e) => TopupSaldoHistory.fromJson(e)).toList();
      }
      if (pulsaRes.statusCode == 200 && pulsaRes.data['success'] == true) {
        final List raw = pulsaRes.data['data'] as List? ?? [];
        _pulsaHistory = raw.map((e) => TopupSaldoHistory.fromJson(e)).toList();
      }

      setState(() => _isLoading = false);
    } on DioException catch (e) {
      setState(() {
        _error = e.response?.data['message'] ?? 'Gagal memuat history';
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Terjadi kesalahan: $e';
        _isLoading = false;
      });
    }
  }

  // ==================== EXPORT WITH SHARE ====================
  Future<void> _exportToExcel() async {
    if (_saldoHistory.isEmpty && _pulsaHistory.isEmpty) {
      _showSnack('Tidak ada data untuk diekspor');
      return;
    }

    setState(() => _isExporting = true);
    try {
      final workbook = excel.Excel.createExcel();
      _fillSaldoSheet(workbook['Topup Saldo']);
      _fillPulsaSheet(workbook['Topup Pulsa']);
      final bytes = workbook.encode();
      if (bytes == null) {
        _showSnack('Gagal membuat file Excel', color: Colors.red);
        return;
      }

      final directory = await getTemporaryDirectory();
      final filename = 'history_transaksi_${DateTime.now().millisecondsSinceEpoch}.xlsx';
      final file = File('${directory.path}/$filename');
      await file.writeAsBytes(bytes);

      await Share.shareXFiles(
        [XFile(file.path)],
        text: 'Riwayat Transaksi - Pulsa IO',
        subject: 'Riwayat Transaksi',
      );

      _showSnack('File siap dibagikan', color: Colors.green);
    } catch (e) {
      _showSnack('Gagal export: $e', color: Colors.red);
    } finally {
      if (mounted) setState(() => _isExporting = false);
    }
  }

  void _fillSaldoSheet(excel.Sheet sheet) {
    final headers = ['No', 'Tanggal', 'Nominal', 'Metode', 'Referensi ID', 'Status', 'Tipe'];
    for (int i = 0; i < headers.length; i++) {
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: i, rowIndex: 0)).value = headers[i];
    }

    if (_saldoHistory.isEmpty) {
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 0, rowIndex: 1)).value = 'Tidak ada data';
      return;
    }

    for (int i = 0; i < _saldoHistory.length; i++) {
      final item = _saldoHistory[i];
      final row = i + 1;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 0, rowIndex: row)).value = i + 1;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 1, rowIndex: row)).value = _formatDateTime(item.createdAt);
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 2, rowIndex: row)).value = item.nominal;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 3, rowIndex: row)).value = item.metodePembayaran;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 4, rowIndex: row)).value = item.referensiId;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 5, rowIndex: row)).value = item.status;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 6, rowIndex: row)).value = item.tipeTransaksiLabel;
    }
  }

  void _fillPulsaSheet(excel.Sheet sheet) {
    final headers = ['No', 'Tanggal', 'Nominal', 'Metode', 'Referensi ID', 'Status', 'Tipe'];
    for (int i = 0; i < headers.length; i++) {
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: i, rowIndex: 0)).value = headers[i];
    }

    if (_pulsaHistory.isEmpty) {
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 0, rowIndex: 1)).value = 'Tidak ada data';
      return;
    }

    for (int i = 0; i < _pulsaHistory.length; i++) {
      final item = _pulsaHistory[i];
      final row = i + 1;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 0, rowIndex: row)).value = i + 1;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 1, rowIndex: row)).value = _formatDateTime(item.createdAt);
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 2, rowIndex: row)).value = item.nominal;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 3, rowIndex: row)).value = item.metodePembayaran;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 4, rowIndex: row)).value = item.referensiId;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 5, rowIndex: row)).value = item.status;
      sheet.cell(excel.CellIndex.indexByColumnRow(columnIndex: 6, rowIndex: row)).value = item.tipeTransaksiLabel;
    }
  }

  String _formatDateTime(DateTime dt) {
    return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year} '
        '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
  }

  void _showSnack(String msg, {Color? color}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg), backgroundColor: color));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const CustomSidebar(currentRoute: '/history'),
      appBar: AppBar(
        title: const Text('Riwayat Transaksi'),
        actions: [
          IconButton(
            icon: _isExporting
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.share),
            tooltip: 'Export & Share Excel',
            onPressed: _isExporting ? null : _exportToExcel,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Topup Saldo', icon: Icon(Icons.account_balance_wallet)),
            Tab(text: 'Topup Pulsa', icon: Icon(Icons.phone_android)),
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
                      ElevatedButton(onPressed: _loadHistory, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildHistoryList(_saldoHistory, 'Topup Saldo'),
                    _buildHistoryList(_pulsaHistory, 'Topup Pulsa'),
                  ],
                ),
    );
  }

  Widget _buildHistoryList(List<TopupSaldoHistory> list, String title) {
    if (list.isEmpty) return Center(child: Text('Belum ada data $title'));
    return RefreshIndicator(
      onRefresh: _loadHistory,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: list.length,
        itemBuilder: (context, index) {
          final item = list[index];
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
                      Text(item.tipeTransaksiLabel, style: Theme.of(context).textTheme.titleSmall),
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
                        child: Text(item.statusLabel, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
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
                          Text(item.nominalFormatted, style: const TextStyle(fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(
                            '${item.metodePembayaran.toUpperCase()} • ${item.referensiId}',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                          ),
                        ],
                      ),
                      Text(
                        _formatDateTime(item.createdAt),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}