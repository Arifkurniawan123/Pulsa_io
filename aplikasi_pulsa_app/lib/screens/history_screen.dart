import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
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
        Navigator.pushReplacementNamed(context, '/topup-saldo');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/scan-pulsa');
        break;
      case 4:
        if (roleId == 2) Navigator.pushReplacementNamed(context, '/user');
        else Navigator.pushReplacementNamed(context, '/history');
        break;
      case 5:
        if (roleId == 2) {
          // sudah di history (admin)
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
    final isAdmin = _userRole == 2;
    final selectedIndex = isAdmin ? 5 : 4; // riwayat di index 5 (admin) atau 4 (kasir)

    return Scaffold(
      drawer: CustomSidebar(selectedIndex: selectedIndex, onItemSelected: _onSidebarItemSelected),
      appBar: AppBar(
        title: const Text('Riwayat Transaksi'),
        actions: [
          IconButton(
            icon: const Icon(Icons.qr_code_scanner),
            tooltip: 'Scan Pulsa',
            onPressed: () => Navigator.pushNamed(context, '/scan-pulsa'),
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
      ),
    );
  }
}